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

import android.content.Context;
import android.net.Uri;
import android.os.ResultReceiver;
import android.widget.TextView;

import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.SessionManager;

import io.fabric.sdk.android.services.common.CommonUtils;

class ConfirmationCodeController extends DigitsControllerImpl {
    private final String phoneNumber;
    private final Boolean isEmailCollection;
    private final InvertedStateButton resendButton, callMeButton;
    private final TextView timerText;
    private final SpacedEditText confirmationEditText;

    ConfirmationCodeController(ResultReceiver resultReceiver, StateButton stateButton,
                               InvertedStateButton resendButton, InvertedStateButton callMeButton,
                               SpacedEditText confirmationEditText, String phoneNumber,
                               DigitsEventCollector digitsEventCollector, boolean isEmailCollection,
                               TextView timerText,
                               DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
        this(resultReceiver, stateButton, resendButton, callMeButton, confirmationEditText,
                phoneNumber, Digits.getSessionManager(), Digits.getInstance().getDigitsClient(),
                new ConfirmationErrorCodes(stateButton.getContext().getResources()),
                Digits.getInstance().getActivityClassManager(), digitsEventCollector,
                isEmailCollection, timerText, digitsEventDetailsBuilder);
    }

    /**
     * Only for test
     */
    ConfirmationCodeController(ResultReceiver resultReceiver, StateButton stateButton,
                               InvertedStateButton resendButton, InvertedStateButton callMeButton,
                               SpacedEditText confirmationEditText, String phoneNumber,
                               SessionManager<DigitsSession> sessionManager, DigitsClient client,
                               ErrorCodes errors, ActivityClassManager activityClassManager,
                               DigitsEventCollector digitsEventCollector, boolean isEmailCollection,
                               TextView timerText,
                               DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
        super(resultReceiver, stateButton, confirmationEditText, client, errors,
                activityClassManager, sessionManager, digitsEventCollector,
                digitsEventDetailsBuilder);
        this.phoneNumber = phoneNumber;
        this.isEmailCollection = isEmailCollection;
        this.resendButton = resendButton;
        this.callMeButton = callMeButton;
        this.countDownTimer = createCountDownTimer(
                DigitsConstants.RESEND_TIMER_DURATION_MILLIS, timerText, resendButton,
                callMeButton);
        this.timerText = timerText;
        this.confirmationEditText = confirmationEditText;
    }

    @Override
    public void executeRequest(final Context context) {
        digitsEventCollector.submitClickOnSignupScreen(eventDetailsBuilder
                .withCurrentTime(System.currentTimeMillis()).build());
        if (validateInput(confirmationEditText.getUnspacedText())) {
            sendButton.showProgress();
            CommonUtils.hideKeyboard(context, confirmationEditText);
            final String code = confirmationEditText.getUnspacedText().toString();
            digitsClient.createAccount(code, phoneNumber,
                    new DigitsCallback<DigitsUser>(context, this, sessionManager) {
                        @Override
                        public void success(Result<DigitsUser> result) {
                            digitsEventCollector.signupSuccess(eventDetailsBuilder
                                    .withCurrentTime(System.currentTimeMillis()).build());
                            final DigitsSession session =
                                    DigitsSession.create(result, phoneNumber);
                            sessionManager.setActiveSession(session);
                            if (isEmailCollection) {
                                startEmailRequest(context, phoneNumber, eventDetailsBuilder);
                            } else {
                                loginSuccess(context, session, phoneNumber,
                                        eventDetailsBuilder);
                            }
                        }

                    });
        } else {
            handleError(context,
              new DigitsException(
                errors.getMessage(DigitsApiErrorConstants.CLIENT_SIDE_VALIDATION_FAILED)));
        }
    }

    public void resendCode(final Context context, final InvertedStateButton activeButton,
                           final Verification verificationType) {
        activeButton.showProgress();
        digitsClient.registerDevice(phoneNumber, verificationType,
            new DigitsCallback<DeviceRegistrationResponse>(context, this, sessionManager) {
                @Override
                public void success(Result<DeviceRegistrationResponse> result) {
                    activeButton.showFinish();
                    activeButton.postDelayed(new Runnable() {
                        @Override
                        public void run() {
                            activeButton.showStart();
                            timerText.setText(String.valueOf(
                                            DigitsConstants.RESEND_TIMER_DURATION_MILLIS / 1000),
                                    TextView.BufferType.NORMAL);
                            resendButton.setEnabled(false);
                            callMeButton.setEnabled(false);
                            startTimer();
                        }
                    }, POST_DELAY_MS);
                }
            });
    }

    @Override
    public void scribeControllerFailure() {
        digitsEventCollector.signupFailure();
    }

    @Override
    public void scribeControllerException(DigitsException exception) {
        digitsEventCollector.signupException(exception);
    }

    @Override
    public void handleError(final Context context, DigitsException digitsException) {
        callMeButton.showError();
        resendButton.showError();
        super.handleError(context, digitsException);
    }

    @Override
    Uri getTosUri() {
        return DigitsConstants.TWITTER_TOS;
    }

    @Override
    public boolean validateInput(CharSequence text) {
        return super.validateInput(text) &&
                text.length() >= DigitsConstants.MIN_CONFIRMATION_CODE_LENGTH;
    }

}
