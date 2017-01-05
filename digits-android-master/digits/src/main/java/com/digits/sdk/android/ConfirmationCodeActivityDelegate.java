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
import android.content.IntentFilter;
import android.content.res.Resources;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.os.ResultReceiver;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import io.fabric.sdk.android.services.common.CommonUtils;

class ConfirmationCodeActivityDelegate extends DigitsActivityDelegateImpl {
    SpacedEditText editText;
    LinkTextView editPhoneNumberLink;
    StateButton stateButton;
    InvertedStateButton resendButton, callMeButton;
    TextView termsText;
    TextView timerText;
    DigitsController controller;
    SmsBroadcastReceiver receiver;
    Activity activity;
    DigitsEventCollector digitsEventCollector;
    AuthConfig config;
    TosFormatHelper tosFormatHelper;
    BucketedTextChangeListener bucketedTextChangeListener;

    public ConfirmationCodeActivityDelegate(DigitsEventCollector digitsEventCollector) {
        this.digitsEventCollector = digitsEventCollector;
    }

    @Override
    public int getLayoutId() {
        return R.layout.dgts__activity_confirmation;
    }

    @Override
    public boolean isValid(Bundle bundle) {
        final boolean isValidBundle = BundleManager.assertContains(bundle,
                DigitsClient.EXTRA_RESULT_RECEIVER, DigitsClient.EXTRA_PHONE);

        if (isValidBundle) {
            final DigitsEventDetailsBuilder digitsEventDetailsBuilder =
                    bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);

            return (digitsEventDetailsBuilder.authStartTime != null)
                    && (digitsEventDetailsBuilder.language != null)
                    && (digitsEventDetailsBuilder.country != null);
        }
        return false;
    }

    @Override
    public void init(Activity activity, Bundle bundle) {
        this.activity = activity;
        eventDetailsBuilder = bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);
        editText = (SpacedEditText) activity.findViewById(R.id.dgts__confirmationEditText);
        stateButton = (StateButton) activity.findViewById(R.id.dgts__createAccount);
        resendButton =  (InvertedStateButton) activity
                .findViewById(R.id.dgts__resendConfirmationButton);
        callMeButton =  (InvertedStateButton) activity.findViewById(R.id.dgts__callMeButton);
        editPhoneNumberLink = (LinkTextView) activity.findViewById(R.id.dgts__editPhoneNumber);
        termsText = (TextView) activity.findViewById(R.id.dgts__termsTextCreateAccount);
        timerText = (TextView) activity.findViewById(R.id.dgts__countdownTimer);
        config = bundle.getParcelable(DigitsClient.EXTRA_AUTH_CONFIG);

        controller = initController(bundle);
        tosFormatHelper = new TosFormatHelper(activity);
        bucketedTextChangeListener = new BucketedTextChangeListener(this.editText,
                DigitsConstants.MIN_CONFIRMATION_CODE_LENGTH, DigitsConstants.hyphen,
                createBucketOnEditCallback(stateButton));

        setUpEditText(activity, controller, editText);
        setUpSendButton(activity, controller, stateButton);
        setupResendButton(activity, controller, digitsEventCollector, resendButton);
        setupCallMeButton(activity, controller, digitsEventCollector, callMeButton, config);
        setupCountDownTimer(controller, timerText, config);
        setUpEditPhoneNumberLink(activity, editPhoneNumberLink,
                bundle.getString(DigitsClient.EXTRA_PHONE));
        setUpTermsText(activity, controller, termsText);
        setUpSmsIntercept(activity, editText);

        CommonUtils.openKeyboard(activity, editText);
    }

    DigitsController initController(Bundle bundle) {
        return new ConfirmationCodeController(
                bundle.<ResultReceiver>getParcelable(DigitsClient.EXTRA_RESULT_RECEIVER),
                stateButton, resendButton, callMeButton, editText,
                bundle.getString(DigitsClient.EXTRA_PHONE), digitsEventCollector,
                bundle.getBoolean(DigitsClient.EXTRA_EMAIL), timerText, eventDetailsBuilder);
    }

    @Override
    public void setUpSendButton(Activity activity, DigitsController controller,
                                StateButton stateButton) {
        stateButton.setStatesText(R.string.dgts__create_account, R.string.dgts__sending,
                R.string.dgts__done);
        stateButton.showStart();
        stateButton.setEnabled(false);
        super.setUpSendButton(activity, controller, stateButton);
    }

    @Override
    public void setUpTermsText(Activity activity, DigitsController controller, TextView termsText) {
        termsText.setText(tosFormatHelper.getFormattedTerms(R.string.dgts__terms_text_create));
        super.setUpTermsText(activity, controller, termsText);
    }

    @Override
    public void setUpEditText(final Activity activity, final DigitsController controller,
                              EditText editText) {
        super.setUpEditText(activity, controller, editText);

        final Drawable dr = activity.getResources()
                .getDrawable(Resources.getSystem()
                        .getIdentifier("indicator_input_error", "drawable", "android"));
        editText.setText(activity.getResources()
                .getString(R.string.dgts__confirmationEditTextPlaceholder));
        editText.setCompoundDrawablePadding(dr.getIntrinsicWidth() * -1);
        editText.addTextChangedListener(bucketedTextChangeListener);
    }

    @Override
    public void onResume() {
        digitsEventCollector.signupScreenImpression(eventDetailsBuilder
                .withCurrentTime(System.currentTimeMillis()).build());
        controller.onResume();
    }

    @Override
    public void onDestroy() {
        if (receiver != null) {
            activity.unregisterReceiver(receiver);
        }
        controller.cancelTimer();
    }

    void setupResendButton(final Activity activity, final DigitsController controller,
                           final DigitsEventCollector digitsEventCollector,
                           final InvertedStateButton resendButton){
        resendButton.setEnabled(false);
        resendButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                digitsEventCollector.resendClickOnSignupScreen();
                controller.clearError();
                controller.resendCode(activity, resendButton, Verification.sms);
            }
        });
    }

    void setupCallMeButton(final Activity activity, final DigitsController controller,
                           final DigitsEventCollector digitsEventCollector,
                           final InvertedStateButton callMeButton,
                           final AuthConfig config){
        callMeButton.setVisibility(config.isVoiceEnabled ? View.VISIBLE : View.GONE);
        callMeButton.setEnabled(false);

        callMeButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                digitsEventCollector.callMeClickOnSignupScreen();
                controller.clearError();
                controller.resendCode(activity, callMeButton, Verification.voicecall);
            }
        });
    }

    protected void setUpSmsIntercept(Activity activity, EditText editText) {
        if (CommonUtils.checkPermission(activity, "android.permission.RECEIVE_SMS")) {
            final IntentFilter filter = new IntentFilter("android.provider.Telephony.SMS_RECEIVED");
            receiver = new SmsBroadcastReceiver(editText);
            activity.registerReceiver(receiver, filter);
        }
    }
}
