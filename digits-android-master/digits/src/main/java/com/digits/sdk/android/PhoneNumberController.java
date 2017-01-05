/*
 * Copyright (C) 2015 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

package com.digits.sdk.android;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.os.ResultReceiver;
import android.support.annotation.NonNull;
import android.widget.EditText;

import com.twitter.sdk.android.core.SessionManager;

import java.util.Locale;

import io.fabric.sdk.android.services.common.CommonUtils;

class PhoneNumberController extends DigitsControllerImpl {
    private final TosView tosView;
    final CountryListSpinner countryCodeSpinner;
    boolean voiceEnabled;
    boolean resendState;
    boolean emailCollection;

    PhoneNumberController(ResultReceiver resultReceiver,
                          StateButton stateButton, EditText phoneEditText,
                          CountryListSpinner countryCodeSpinner, TosView tosView,
                          DigitsEventCollector digitsEventCollector, boolean emailCollection,
                          DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
        this(resultReceiver, stateButton, phoneEditText, countryCodeSpinner,
                Digits.getInstance().getDigitsClient(), new PhoneNumberErrorCodes(stateButton
                        .getContext().getResources()),
                Digits.getInstance().getActivityClassManager(), Digits.getSessionManager(),
                tosView, digitsEventCollector, emailCollection, digitsEventDetailsBuilder);
    }

    /**
     * Only for test
     */
    PhoneNumberController(ResultReceiver resultReceiver, StateButton stateButton,
                          EditText phoneEditText, CountryListSpinner countryCodeSpinner,
                          DigitsClient client, ErrorCodes errors,
                          ActivityClassManager activityClassManager,
                          SessionManager<DigitsSession> sessionManager, TosView tosView,
                          DigitsEventCollector digitsEventCollector, boolean emailCollection,
                          DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
        super(resultReceiver, stateButton, phoneEditText, client, errors, activityClassManager,
                sessionManager, digitsEventCollector, digitsEventDetailsBuilder);
        this.countryCodeSpinner = countryCodeSpinner;
        this.tosView = tosView;
        voiceEnabled = false;
        resendState = false;
        this.emailCollection = emailCollection;
    }

    public void setPhoneNumber(PhoneNumber phoneNumber) {
        if (PhoneNumber.isValid(phoneNumber)) {
            editText.setText(phoneNumber.getPhoneNumber());
            editText.setSelection(phoneNumber.getPhoneNumber().length());
        }
    }

    public void setCountryCode(PhoneNumber phoneNumber) {
        if (PhoneNumber.isCountryValid(phoneNumber)) {
            countryCodeSpinner.setSelectedForCountry(new Locale("",
                    phoneNumber.getCountryIso()), phoneNumber.getCountryCode());
        }
    }

    LoginOrSignupComposer createCompositeCallback(final Context context, final String phoneNumber) {
        final DigitsEventDetailsBuilder dm = eventDetailsBuilder
                .withCountry(((CountryInfo) countryCodeSpinner.getTag()).locale.getCountry())
                .withCurrentTime(System.currentTimeMillis());

        return new LoginOrSignupComposer(context, digitsClient, sessionManager, phoneNumber,
                getVerificationType(), this.emailCollection, resultReceiver,
                activityClassManager, dm) {

            @Override
            public void success(final Intent intent) {
                final DigitsEventDetailsBuilder digitsEventDetailsBuilder = eventDetailsBuilder
                    .withCountry(((CountryInfo) countryCodeSpinner.getTag())
                            .locale.getCountry())
                    .withCurrentTime(System.currentTimeMillis());

                sendButton.showFinish();

                editText.postDelayed(new Runnable() {
                    @Override
                    public void run() {
                        digitsEventCollector.submitPhoneSuccess(digitsEventDetailsBuilder.build());
                        startActivityForResult((Activity) context, intent);
                    }
                }, POST_DELAY_MS);
            }

            @Override
            public void failure(DigitsException digitsException) {
                if (digitsException instanceof OperatorUnsupportedException) {
                    voiceEnabled = digitsException.getConfig().isVoiceEnabled;
                    resend();
                    handleError(context, digitsException);
                } else {
                    handleError(context, digitsException);
                }
            }
        };
    }

    @Override
    public void executeRequest(final Context context) {
        scribeRequest();
        if (validateInput(editText.getText())) {
            sendButton.showProgress();
            CommonUtils.hideKeyboard(context, editText);
            final int code = ((CountryInfo) countryCodeSpinner.getTag()).countryCode;
            final String number = editText.getText().toString();
            final String phoneNumber = getNumber(code, number);
            createCompositeCallback(context, phoneNumber).start();
        }
    }

    private void scribeRequest() {
        final DigitsEventDetails digitsEventDetails = this.eventDetailsBuilder
                .withCountry(((CountryInfo) countryCodeSpinner.getTag()).locale.getCountry())
                .withCurrentTime(System.currentTimeMillis())
                .build();

        if (isRetry()) {
            digitsEventCollector.retryClickOnPhoneScreen(digitsEventDetails);
        } else {
            digitsEventCollector.submitClickOnPhoneScreen(digitsEventDetails);
        }
    }

    private boolean isRetry() {
        return errorCount > 0;
    }

    @NonNull
    private Verification getVerificationType() {
        return resendState && voiceEnabled ? Verification.voicecall : Verification.sms;
    }

    @Override
    Uri getTosUri() {
        return DigitsConstants.DIGITS_TOS;
    }

    private String getNumber(long countryCode, String numberTextView) {
        return "+" + String.valueOf(countryCode) + numberTextView;
    }

    public void onLoadComplete(PhoneNumber phoneNumber) {
        setPhoneNumber(phoneNumber);
        setCountryCode(phoneNumber);
    }


    public void resend() {
        resendState = true;
        if (voiceEnabled) {
            sendButton.setStatesText(R.string.dgts__call_me, R.string.dgts__calling,
                    R.string.dgts__calling);
            tosView.setText(R.string.dgts__terms_text_call_me);
        }
    }

    @Override
    public void scribeControllerFailure() {
        digitsEventCollector.submitPhoneFailure();
    }

    @Override
    void scribeControllerException(DigitsException exception) {
        digitsEventCollector.submitPhoneException(exception);
    }

    //We override the base startFallback and avoid finishing affinity.
    //The phoneNumber activity remains on the backstack and can be resumed when the user choses
    //to try again in the Failure screen.
    @Override
    public void startFallback(Context context, ResultReceiver receiver, DigitsException reason) {
        final Intent intent = new Intent(context, activityClassManager.getFailureActivity());
        intent.putExtra(DigitsClient.EXTRA_RESULT_RECEIVER, receiver);
        intent.putExtra(DigitsClient.EXTRA_FALLBACK_REASON, reason);
        intent.putExtra(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder
                .withCountry(((CountryInfo) countryCodeSpinner.getTag()).locale.getCountry()));
        context.startActivity(intent);
    }

    @Override
    public void onTextChanged(CharSequence s, int start, int before, int count) {
        super.onTextChanged(s, start, before, count);
        if (Verification.voicecall.equals(getVerificationType())) {
            resendState = false;
            sendButton.setStatesText(R.string.dgts__continue,
                    R.string.dgts__sending,
                    R.string.dgts__done);
            sendButton.showStart();
            tosView.setText(R.string.dgts__terms_text);
        }
    }

    protected void sendFailure(String message) {
        final Bundle bundle = new Bundle();
        bundle.putString(LoginResultReceiver.KEY_ERROR, message);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder
                .withCountry(((CountryInfo) countryCodeSpinner.getTag()).locale.getCountry()));
        resultReceiver.send(LoginResultReceiver.RESULT_ERROR, bundle);
    }
}
