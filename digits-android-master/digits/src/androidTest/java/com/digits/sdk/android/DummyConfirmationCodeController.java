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

import android.os.CountDownTimer;
import android.os.ResultReceiver;
import android.widget.TextView;

import com.twitter.sdk.android.core.SessionManager;

import static org.mockito.Mockito.mock;

public class DummyConfirmationCodeController extends ConfirmationCodeController {
    DummyConfirmationCodeController(ResultReceiver resultReceiver, StateButton stateButton,
                                    InvertedStateButton resendButton,
                                    InvertedStateButton callMeButton,
                                    SpacedEditText phoneEditText, String phoneNumber,
                                    SessionManager<DigitsSession> sessionManager,
                                    DigitsClient client, ErrorCodes errors,
                                    ActivityClassManager activityClassManager,
                                    DigitsEventCollector digitsEventCollector,
                                    boolean isEmailCollection, TextView timerText,
                                    DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
        super(resultReceiver, stateButton, resendButton, callMeButton, phoneEditText, phoneNumber,
                sessionManager, client, errors, activityClassManager, digitsEventCollector,
                isEmailCollection, timerText, digitsEventDetailsBuilder);
    }

    @Override
    CountDownTimer createCountDownTimer(final int disableDurationMillis,
                                        final TextView timerText,
                                        final InvertedStateButton resentButton,
                                        final InvertedStateButton callMeButton) {
        return mock(CountDownTimer.class);
    }

    public CountDownTimer getCountDownTimer(){
        return countDownTimer;
    }
}
