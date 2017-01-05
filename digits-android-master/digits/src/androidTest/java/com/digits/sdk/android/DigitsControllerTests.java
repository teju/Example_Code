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

import android.annotation.TargetApi;
import android.app.Activity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.os.ResultReceiver;
import android.widget.TextView;

import com.twitter.sdk.android.core.TwitterApiErrorConstants;
import com.twitter.sdk.android.core.TwitterException;

import org.mockito.ArgumentCaptor;

import java.util.Locale;

import static org.mockito.Mockito.atMost;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyZeroInteractions;
import static org.mockito.Mockito.when;

public abstract class DigitsControllerTests<T extends DigitsControllerImpl> extends
        DigitsAndroidTestCase {
    static final String COUNTRY = "US";
    static final CountryInfo COUNTRY_INFO = new CountryInfo(new Locale("", COUNTRY), 123);
    static final String PHONE = "123456789";
    static final String PHONE_WITH_COUNTRY_CODE = "+" + COUNTRY_INFO.countryCode + "123456789";
    static final String CODE = "123456";
    static final String EMPTY_CODE = "";
    static final long USER_ID = 1234567;
    static final String REQUEST_ID = "881984";


    T controller;
    SpacedEditText phoneEditText;
    StateButton sendButton;
    InvertedStateButton resendButton, callMeButton;
    DigitsClient digitsClient;
    ArgumentCaptor<Intent> intentCaptor;
    ArgumentCaptor<DigitsCallback> callbackCaptor;
    ArgumentCaptor<Bundle> bundleCaptor;
    ResultReceiver resultReceiver;
    ErrorCodes errors;
    DummySessionManager sessionManager;
    Activity context;
    DigitsEventCollector digitsEventCollector;
    CountDownTimer countDownTimer;
    TextView timerTextView;
    DigitsEventDetailsBuilder digitsEventDetailsBuilder;
    ArgumentCaptor<DigitsEventDetails> digitsEventDetailsArgumentCaptor;

    @Override
    public void setUp() throws Exception {
        super.setUp();
        bundleCaptor = ArgumentCaptor.forClass(Bundle.class);
        callbackCaptor = ArgumentCaptor.forClass(DigitsCallback.class);
        intentCaptor = ArgumentCaptor.forClass(Intent.class);
        phoneEditText = mock(SpacedEditText.class);
        sendButton = mock(StateButton.class);
        resendButton = mock(InvertedStateButton.class);
        callMeButton = mock(InvertedStateButton.class);
        digitsClient = mock(DigitsClient.class);
        context = mock(Activity.class);
        resultReceiver = mock(ResultReceiver.class);
        sessionManager = new DummySessionManager(mock(DigitsSession.class));
        errors = mock(ErrorCodes.class);
        digitsEventCollector = mock(DummyDigitsEventCollector.class);
        countDownTimer = mock(CountDownTimer.class);
        timerTextView = mock(TextView.class);
        digitsEventDetailsArgumentCaptor = ArgumentCaptor.forClass(DigitsEventDetails.class);

        when(context.getPackageName()).thenReturn(getClass().getPackage().toString());
        when(context.getResources()).thenReturn(getContext().getResources());
    }

    public void testHandleError() throws Exception {
        controller.handleError(context, EXCEPTION);
        verifyControllerError(EXCEPTION, 1);
        verify(phoneEditText).setError(ERROR_MESSAGE);
        verify(sendButton).showError();
        verifyNoInteractions(digitsEventCollector);
        verifyZeroInteractions(context);
    }

    @TargetApi(Build.VERSION_CODES.JELLY_BEAN)
    public void testShowError_fiveTimesStartFallback() throws Exception {
        controller.handleError(context, EXCEPTION);
        controller.handleError(context, EXCEPTION);
        controller.handleError(context, EXCEPTION);
        controller.handleError(context, EXCEPTION);
        controller.handleError(context, EXCEPTION);
        verifyControllerError(EXCEPTION, 5);
        verifyControllerFailure(1);
        verify(phoneEditText, atMost(4)).setError(ERROR_MESSAGE);
        verify(sendButton, atMost(4)).showError();
        verify(context).startActivity(intentCaptor.capture());
        final Intent intent = intentCaptor.getValue();
        assertEquals(FailureActivity.class.getName(), intent.getComponent().getClassName());
        assertEquals(resultReceiver, intent.getExtras().get(DigitsClient.EXTRA_RESULT_RECEIVER));

        final DigitsEventDetailsBuilder details = intent.getExtras()
                .getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);
        assertNotNull(details.country);
        assertNotNull(details.language);
        assertNotNull(details.authStartTime);

        verify(context).finish();
    }

    public void testHandleError_unrecoverableExceptionStartFallback() throws Exception {
        controller.handleError(context, new UnrecoverableException(ERROR_MESSAGE));
        verifyUnrecoverableException();
    }

    void verifyUnrecoverableException() {
        verifyControllerFailure(1);
        verifyNoInteractions(sendButton, phoneEditText);
        verify(context).startActivity(intentCaptor.capture());
        final Intent intent = intentCaptor.getValue();
        assertEquals(FailureActivity.class.getName(), intent.getComponent().getClassName());
        assertEquals(resultReceiver, intent.getExtras().get(DigitsClient.EXTRA_RESULT_RECEIVER));
        verify(context).finish();
    }

    public void testStartFallback() throws Exception {
        controller.startFallback(context, resultReceiver, new DigitsException("",
                TwitterApiErrorConstants.USER_IS_NOT_SDK_USER, new AuthConfig()));
        verify(context).startActivity(intentCaptor.capture());
        final Intent intent = intentCaptor.getValue();
        assertEquals(FailureActivity.class.getName(), intent.getComponent().getClassName());
        assertEquals(resultReceiver, intent.getExtras().get(DigitsClient.EXTRA_RESULT_RECEIVER));

        final DigitsException reason = (DigitsException) intent.getExtras().get(DigitsClient
                .EXTRA_FALLBACK_REASON);
        assertEquals(TwitterApiErrorConstants.USER_IS_NOT_SDK_USER, reason.getErrorCode());
    }


    public void testOnResume() throws Exception {
        controller.onResume();
        verify(sendButton).showStart();
    }

    public void testClearError() throws Exception {
        controller.clearError();
        verify(phoneEditText).setError(null);
        verifyNoInteractions(sendButton, digitsClient);
    }

    public void testAfterTextChanged() throws Exception {
        controller.onTextChanged(null, 0, 0, 0);
        verify(phoneEditText).setError(null);
        verifyNoInteractions(sendButton);
    }

    public void testExecuteRequest_failure() throws Exception {
        when(errors.getDefaultMessage()).thenReturn(ERROR_MESSAGE);
        final DigitsCallback callback = executeRequest();
        callback.failure(new TwitterException(ERROR_MESSAGE));
        verify(phoneEditText).setError(ERROR_MESSAGE);
        verify(sendButton).showError();
    }

    public void testExecuteRequest_noInput() throws Exception {
        executeRequest();
        verifyNoInteractions(digitsClient);
    }

    abstract DigitsCallback executeRequest();

    public void testValidateInput_null() throws Exception {
        assertFalse(controller.validateInput(null));
    }

    public void testValidateInput_empty() throws Exception {
        assertFalse(controller.validateInput(EMPTY_CODE));
    }

    public abstract void verifyControllerFailure(int times);

    public abstract void verifyControllerError(DigitsException e, int times);

}
