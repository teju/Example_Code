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

import android.content.ComponentName;
import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;

import org.mockito.ArgumentCaptor;
import org.mockito.Captor;
import org.mockito.MockitoAnnotations;

import static org.mockito.Matchers.any;
import static org.mockito.Matchers.eq;
import static org.mockito.Mockito.times;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

public class PinCodeControllerTests extends DigitsControllerTests<PinCodeController> {
    @Captor
    ArgumentCaptor<Callback<VerifyAccountResponse>> callbackArgumentCaptor;
    @Captor
    private ArgumentCaptor<Intent> intentArgumentCaptor;

    @Override
    public void setUp() throws Exception {
        super.setUp();
        MockitoAnnotations.initMocks(this);
        digitsEventDetailsBuilder = new DigitsEventDetailsBuilder().withAuthStartTime(1L)
                .withCountry("US")
                .withLanguage("en");

        controller = new PinCodeController(resultReceiver, sendButton, phoneEditText,
                sessionManager, digitsClient, REQUEST_ID, USER_ID, PHONE_WITH_COUNTRY_CODE,
                errors, new ActivityClassManagerImp(), digitsEventCollector, false,
                digitsEventDetailsBuilder);
    }

    public void testExecuteRequest_success() throws Exception {
        final DigitsCallback<DigitsSessionResponse> callback = executeRequest();
        final DigitsSessionResponse response = TestConstants.DIGITS_USER;
        final Result<DigitsSessionResponse> result = new Result(response, null);
        callback.success(result);
        verify(digitsEventCollector)
                .twoFactorPinVerificationSuccess(digitsEventDetailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = digitsEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        assertEquals(sessionManager.isSet, true);
        assertEquals(sessionManager.getActiveSession(), DigitsSession.create(response,
                PHONE_WITH_COUNTRY_CODE));
        verify(sendButton).showFinish();
        final ArgumentCaptor<Runnable> runnableArgumentCaptor = ArgumentCaptor.forClass
                (Runnable.class);
        verify(phoneEditText).postDelayed(runnableArgumentCaptor.capture(),
                eq(DigitsControllerImpl.POST_DELAY_MS));
        final Runnable runnable = runnableArgumentCaptor.getValue();
        runnable.run();

        final ArgumentCaptor<Bundle> bundleArgumentCaptor = ArgumentCaptor.forClass(Bundle.class);
        verify(resultReceiver).send(eq(LoginResultReceiver.RESULT_OK),
                bundleArgumentCaptor.capture());
        assertEquals(PHONE_WITH_COUNTRY_CODE, bundleArgumentCaptor.getValue().getString
                (DigitsClient.EXTRA_PHONE));
    }

    @Override
    DigitsCallback executeRequest() {
        when(phoneEditText.getText()).thenReturn(Editable.Factory.getInstance().newEditable(CODE));
        final ArgumentCaptor<DigitsCallback> callbackArgumentCaptor = ArgumentCaptor.forClass
                (DigitsCallback.class);
        controller.executeRequest(context);
        verify(digitsEventCollector)
                .submitClickOnPinScreen(digitsEventDetailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = digitsEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(sendButton).showProgress();
        verify(digitsClient).verifyPin(eq(REQUEST_ID), eq(USER_ID), eq(CODE),
                callbackArgumentCaptor.capture());
        return callbackArgumentCaptor.getValue();
    }

    public void testValidateInput_null() throws Exception {
        assertFalse(controller.validateInput(null));
    }

    public void testValidateInput_empty() throws Exception {
        assertFalse(controller.validateInput(EMPTY_CODE));
    }

    @Override
    public void verifyControllerFailure(int times) {
        verify(digitsEventCollector, times(times)).twoFactorPinVerificationFailure();
    }

    @Override
    public void verifyControllerError(DigitsException e, int times) {
        verify(digitsEventCollector, times(times)).twoFactorPinVerificationException(EXCEPTION);
    }

    public void testExecuteRequest_successWithEmailRequestSessionHasEmail() throws Exception {
        final DigitsSessionResponse response = TestConstants.DIGITS_USER;
        final Result<DigitsSessionResponse> result = new Result(response, null);
        final Result<VerifyAccountResponse> resultEmailRequest = new Result(
                TestConstants.getVerifyAccountResponse(), null);
        controller = new DummyPinCodeController(resultReceiver, sendButton, phoneEditText,
                sessionManager, digitsClient, REQUEST_ID, USER_ID, PHONE_WITH_COUNTRY_CODE, errors,
                new ActivityClassManagerImp(), digitsEventCollector, true,
                digitsEventDetailsBuilder);

        final DigitsCallback<DigitsSessionResponse> callback = executeRequest();
        callback.success(result);
        verify(controller.getAccountService()).verifyAccount(callbackArgumentCaptor.capture());
        final Callback<VerifyAccountResponse> emailRequestCallback = callbackArgumentCaptor
                .getValue();
        emailRequestCallback.success(resultEmailRequest);
        verify(digitsEventCollector)
                .twoFactorPinVerificationSuccess(digitsEventDetailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = digitsEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        final DigitsSession session = DigitsSession.create(
                TestConstants.getVerifyAccountResponse());
        verifyEmailRequest(session);
    }


    public void testExecuteRequest_successWithEmailRequestFailure() throws Exception {
        final DigitsSessionResponse response = TestConstants.DIGITS_USER;
        final Result<DigitsSessionResponse> result = new Result(response, null);
        controller = new DummyPinCodeController(resultReceiver, sendButton, phoneEditText,
                sessionManager, digitsClient, REQUEST_ID, USER_ID, PHONE_WITH_COUNTRY_CODE, errors,
                new ActivityClassManagerImp(), digitsEventCollector, true,
                digitsEventDetailsBuilder);

        final DigitsCallback<DigitsSessionResponse> callback = executeRequest();
        callback.success(result);

        verify(controller.getAccountService()).verifyAccount(callbackArgumentCaptor.capture());
        final Callback<VerifyAccountResponse> emailRequestCallback = callbackArgumentCaptor
                .getValue();
        emailRequestCallback.failure(TestConstants.ANY_EXCEPTION);
        verify(digitsEventCollector).twoFactorPinVerificationException(any(DigitsException.class));
        verify(phoneEditText).setError(null);
        verify(sendButton).showError();
    }

    public void testExecuteRequest_successWithEmailRequestSessionNoEmail() throws Exception {
        final DigitsSessionResponse response = TestConstants.DIGITS_USER;
        final Result<DigitsSessionResponse> result = new Result(response, null);
        final Result<VerifyAccountResponse> resultEmailRequest = new Result(
                TestConstants.getVerifyAccountResponseNoEmail(), null);
        final ComponentName emailRequestComponent = new ComponentName(context,
                controller.activityClassManager.getEmailRequestActivity());
        controller = new DummyPinCodeController(resultReceiver, sendButton, phoneEditText,
                sessionManager, digitsClient, REQUEST_ID, USER_ID, PHONE_WITH_COUNTRY_CODE, errors,
                new ActivityClassManagerImp(), digitsEventCollector, true,
                digitsEventDetailsBuilder);

        final DigitsCallback<DigitsSessionResponse> callback = executeRequest();
        callback.success(result);
        verify(controller.getAccountService()).verifyAccount(callbackArgumentCaptor.capture());
        final Callback<VerifyAccountResponse> emailRequestCallback = callbackArgumentCaptor
                .getValue();
        emailRequestCallback.success(resultEmailRequest);
        final DigitsSession session = DigitsSession.create(
                response, PHONE_WITH_COUNTRY_CODE);
        assertEquals(sessionManager.getActiveSession(), session);
        verify(context).startActivityForResult(intentArgumentCaptor.capture(), eq(DigitsActivity
                .REQUEST_CODE));
        final Intent intent = intentArgumentCaptor.getValue();
        assertEquals(emailRequestComponent, intent.getComponent());
        final Bundle bundle = intent.getExtras();
        assertTrue(BundleManager.assertContains(bundle, DigitsClient.EXTRA_PHONE,
                DigitsClient.EXTRA_RESULT_RECEIVER));
    }

    private void verifyEmailRequest(DigitsSession session) {
        assertEquals(sessionManager.getActiveSession(), session);
        verify(sendButton).showFinish();
        final ArgumentCaptor<Runnable> runnableArgumentCaptor = ArgumentCaptor.forClass
                (Runnable.class);
        verify(phoneEditText).postDelayed(runnableArgumentCaptor.capture(),
                eq(DigitsControllerImpl.POST_DELAY_MS));
        final Runnable runnable = runnableArgumentCaptor.getValue();
        runnable.run();

        final ArgumentCaptor<Bundle> bundleArgumentCaptor = ArgumentCaptor.forClass(Bundle.class);
        verify(resultReceiver).send(eq(LoginResultReceiver.RESULT_OK),
                bundleArgumentCaptor.capture());
        assertEquals(PHONE_WITH_COUNTRY_CODE, bundleArgumentCaptor.getValue().getString
                (DigitsClient.EXTRA_PHONE));
    }
}
