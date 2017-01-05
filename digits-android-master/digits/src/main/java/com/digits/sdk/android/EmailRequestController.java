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
import android.text.TextUtils;
import android.util.Patterns;
import android.widget.EditText;

import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.SessionManager;

import java.util.regex.Matcher;

import io.fabric.sdk.android.services.common.CommonUtils;

public class EmailRequestController extends DigitsControllerImpl {
    private String phoneNumber;

    EmailRequestController(StateButton stateButton, EditText editText,
                           ResultReceiver resultReceiver, String phoneNumber,
                           DigitsEventCollector digitsEventCollector,
                           DigitsEventDetailsBuilder details) {
        this(resultReceiver, stateButton, editText, Digits.getSessionManager(),
                Digits.getInstance().getActivityClassManager(),
                Digits.getInstance().getDigitsClient(), phoneNumber,
                digitsEventCollector, new EmailErrorCodes(stateButton.getContext().getResources()),
                details);
    }

    EmailRequestController(ResultReceiver resultReceiver, StateButton stateButton,
                           EditText editText, SessionManager<DigitsSession> sessionManager,
                           ActivityClassManager activityClassManager, DigitsClient client,
                           String phoneNumber, DigitsEventCollector digitsEventCollector,
                           ErrorCodes emailErrorCodes,
                           DigitsEventDetailsBuilder details) {
        super(resultReceiver, stateButton, editText, client, emailErrorCodes,
                activityClassManager, sessionManager, digitsEventCollector,
                details);
        this.phoneNumber = phoneNumber;
    }

    @Override
    Uri getTosUri() {
        return DigitsConstants.DIGITS_TOS;
    }

    @Override
    public void executeRequest(final Context context) {
        digitsEventCollector.submitClickOnEmailScreen(eventDetailsBuilder
                .withCurrentTime(System.currentTimeMillis()).build());
        if (validateInput(editText.getText())) {
            sendButton.showProgress();
            CommonUtils.hideKeyboard(context, editText);
            final String email = editText.getText().toString();
            final DigitsSession session = sessionManager.getActiveSession();
            if (session != null && !session.isLoggedOutUser()) {
                final ApiInterface service =
                        getSdkService();
                service.email(email, new DigitsCallback<DigitsSessionResponse>(context, this,
                        sessionManager) {
                    @Override
                    public void success(Result<DigitsSessionResponse> result) {
                        digitsEventCollector.submitEmailSuccess(eventDetailsBuilder
                                .withCurrentTime(System.currentTimeMillis()).build());
                        loginSuccess(context, session, phoneNumber, eventDetailsBuilder);
                    }
                });
            } else {
                handleError(context, new UnrecoverableException(""));
            }
        } else {
            editText.setError(context.getString(R.string.dgts__invalid_email));
        }
    }

    ApiInterface getSdkService() {
        return Digits.getInstance().getApiClientManager().getApiClient().getService();
    }

    @Override
    public void scribeControllerFailure() {
        digitsEventCollector.submitEmailFailure();
    }

    @Override
    void scribeControllerException(DigitsException exception) {
        digitsEventCollector.submitEmailException(exception);
    }

    @Override
    public boolean validateInput(CharSequence text) {
        return !TextUtils.isEmpty(text) && validate(text.toString());
    }

    private boolean validate(String email) {
        final Matcher matcher = Patterns.EMAIL_ADDRESS.matcher(email);
        return matcher.find();
    }
}
