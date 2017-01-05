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
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.text.Editable;

import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.TwitterApiErrorConstants;
import com.twitter.sdk.android.core.TwitterException;

import org.mockito.ArgumentCaptor;

import java.util.Locale;

import static org.mockito.Mockito.atMost;
import static org.mockito.Mockito.eq;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.times;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

public class PhoneNumberControllerTests extends DigitsControllerTests<PhoneNumberController> {
    private CountryListSpinner countrySpinner;
    private Verification verification;
    private TosView tosView;
    private ArgumentCaptor<Runnable> runnableCaptor;
    private Intent intent;

    @Override
    public void setUp() throws Exception {
        super.setUp();
        verification = Verification.sms;
        countrySpinner = mock(CountryListSpinner.class);
        tosView = mock(TosView.class);
        digitsEventDetailsBuilder = new DigitsEventDetailsBuilder().withAuthStartTime(1L)
                .withLanguage("lang");
        controller = new PhoneNumberController(resultReceiver,
                sendButton, phoneEditText, countrySpinner, digitsClient, errors,
                new ActivityClassManagerImp(), sessionManager, tosView, digitsEventCollector,
                false, digitsEventDetailsBuilder);

        runnableCaptor = ArgumentCaptor.forClass(Runnable.class);
        intent  = mock(Intent.class);

        assertFalse(controller.voiceEnabled);
        assertFalse(controller.emailCollection);
        assertFalse(controller.resendState);
        when(countrySpinner.getTag()).thenReturn(COUNTRY_INFO);
        when(countrySpinner.getText()).thenReturn(COUNTRY);
    }

    public void testExecuteRequest_phoneNotEmpty() throws Exception {
        when(phoneEditText.getText()).thenReturn(Editable.Factory.getInstance().newEditable
                (PHONE));
        final DummyPhoneNumberController dummyPhoneNumberController =
                new DummyPhoneNumberController(resultReceiver,
                        sendButton, phoneEditText, countrySpinner, digitsClient, errors,
                        new ActivityClassManagerImp(), sessionManager, tosView,
                        digitsEventCollector, false, digitsEventDetailsBuilder);

        when(errors.getDefaultMessage()).thenReturn(ERROR_MESSAGE);
        dummyPhoneNumberController.executeRequest(context);
        verify(digitsEventCollector)
                .submitClickOnPhoneScreen(digitsEventDetailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = digitsEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(sendButton).showProgress();
        verify(dummyPhoneNumberController.loginOrSignupComposer).start();
    }

    public void testCreateCompositeCallback_success() {
        final LoginOrSignupComposer loginOrSignupComposer =
                controller.createCompositeCallback(context, PHONE_WITH_COUNTRY_CODE);
        loginOrSignupComposer.success(intent);

        verify(sendButton).showFinish();
        verify(phoneEditText).postDelayed(runnableCaptor.capture(),
                eq(DigitsControllerImpl.POST_DELAY_MS));

        final Runnable runnable = runnableCaptor.getValue();
        runnable.run();
        verify(digitsEventCollector).submitPhoneSuccess(digitsEventDetailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = digitsEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(context).startActivityForResult(intent, DigitsActivity.REQUEST_CODE);
    }

    public void testExecuteRequest_resendWithVoiceVerificationEnabled() throws Exception {
        controller.voiceEnabled = true;
        controller.resend();
        final LoginOrSignupComposer loginOrSignupComposer =
                controller.createCompositeCallback(context, PHONE_WITH_COUNTRY_CODE);
        assertEquals(Verification.voicecall, loginOrSignupComposer.verificationType);
    }

    public void testExecuteRequest_resendWithVoiceVerificationDisabled() throws Exception {
        controller.resend();
        final LoginOrSignupComposer loginOrSignupComposer =
                controller.createCompositeCallback(context, PHONE_WITH_COUNTRY_CODE);

        assertEquals(Verification.sms, loginOrSignupComposer.verificationType);
    }

    @Override
    void verifyUnrecoverableException() {
        verifyControllerFailure(1);
        verifyNoInteractions(sendButton, phoneEditText);
        verify(context).startActivity(intentCaptor.capture());
        final Intent intent = intentCaptor.getValue();
        assertEquals(FailureActivity.class.getName(), intent.getComponent().getClassName());
        assertEquals(resultReceiver, intent.getExtras().get(DigitsClient.EXTRA_RESULT_RECEIVER));
        verify(context, times(0)).finish();
    }

    @Override
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

        verify(context, times(0)).finish();
    }

    @Override
    DigitsCallback<AuthResponse> executeRequest() {
        controller.executeRequest(context);
        if (controller.validateInput(phoneEditText.getText())) {
            verify(digitsClient).authDevice(eq(PHONE_WITH_COUNTRY_CODE), eq(getVerification()),
                    callbackCaptor.capture());
            assertNotNull(callbackCaptor.getValue());
            return callbackCaptor.getValue();
        } else {
            return new DigitsCallback<AuthResponse>(context, controller, sessionManager) {
                @Override
                public void success(Result<AuthResponse> result) {
                    // If it was a success, we should have valid input
                    throw new IllegalStateException();
                }

                @Override
                public void failure(TwitterException twitterException) {
                    // No op
                }
            };
        }
    }

    @Override
    public void testExecuteRequest_failure() throws Exception {
        //not used
    }

    public void testValidateInput_valid() throws Exception {
        assertTrue(controller.validateInput(CODE));
    }

    public void testValidateInput_null() throws Exception {
        assertFalse(controller.validateInput(null));
    }

    public void testValidateInput_empty() throws Exception {
        assertFalse(controller.validateInput(EMPTY_CODE));
    }

    @Override
    public void verifyControllerFailure(int times) {
        verify(digitsEventCollector, times(times)).submitPhoneFailure();
    }

    @Override
    public void verifyControllerError(DigitsException e, int times) {
        verify(digitsEventCollector, times(times)).submitPhoneException(EXCEPTION);
    }

    public void testSetPhoneNumber_validPhoneNumber() throws Exception {
        final PhoneNumber validPhoneNumber = new PhoneNumber(PHONE, US_ISO2, US_COUNTRY_CODE);
        controller.setPhoneNumber(validPhoneNumber);
        verify(phoneEditText).setText(validPhoneNumber.getPhoneNumber());
        verify(phoneEditText).setSelection(validPhoneNumber.getPhoneNumber().length());
    }

    public void testSetPhoneNumber_invalidPhoneNumber() throws Exception {
        controller.setPhoneNumber(PhoneNumber.emptyPhone());
        PhoneNumber invalidPhoneNumber = new PhoneNumber("", US_ISO2, US_COUNTRY_CODE);
        controller.setPhoneNumber(invalidPhoneNumber);
        invalidPhoneNumber = new PhoneNumber(PHONE, "", US_COUNTRY_CODE);
        controller.setPhoneNumber(invalidPhoneNumber);
        invalidPhoneNumber = new PhoneNumber(PHONE, US_ISO2, "");
        controller.setPhoneNumber(invalidPhoneNumber);
        verifyNoInteractions(phoneEditText);
    }

    public void testSetCountryCode_validPhoneNumber() throws Exception {
        final PhoneNumber validPhoneNumber = new PhoneNumber(PHONE, US_ISO2, US_COUNTRY_CODE);
        controller.setCountryCode(validPhoneNumber);
        verify(countrySpinner).setSelectedForCountry(new Locale("",
                        validPhoneNumber.getCountryIso()),
                validPhoneNumber.getCountryCode());
    }

    public void testSetCountryCode_validCountryNoPhoneNumber() throws Exception {
        final PhoneNumber validCountryNoPhoneNumber = new PhoneNumber("", US_ISO2, US_COUNTRY_CODE);
        controller.setCountryCode(validCountryNoPhoneNumber);
        verify(countrySpinner).setSelectedForCountry(new Locale("",
                        validCountryNoPhoneNumber.getCountryIso()),
                validCountryNoPhoneNumber.getCountryCode());
    }

    public void testSetCountryCode_invalidPhoneNumber() throws Exception {
        controller.setCountryCode(PhoneNumber.emptyPhone());
        PhoneNumber invalidPhoneNumber = new PhoneNumber(PHONE, "", US_COUNTRY_CODE);
        controller.setCountryCode(invalidPhoneNumber);
        invalidPhoneNumber = new PhoneNumber(PHONE, US_ISO2, "");
        controller.setCountryCode(invalidPhoneNumber);
        verifyNoInteractions(countrySpinner);
    }

    public void testOnTextChanged_withVoiceVerification() throws Exception {
        controller.resend();
        assertTrue(controller.resendState);
        controller.voiceEnabled = true;

        controller.onTextChanged(PHONE, 0, 0, 0);

        assertFalse(controller.resendState);
        verify(sendButton).setStatesText(R.string.dgts__continue,
                R.string.dgts__sending,
                R.string.dgts__done);
        verify(sendButton).showStart();
        verify(tosView).setText(R.string.dgts__terms_text);
    }

    public void testOnTextChanged_withSmsVerification() throws Exception {
        controller.onTextChanged(PHONE, 0, 0, 0);

        assertFalse(controller.resendState);
        assertFalse(controller.voiceEnabled);
        verifyNoInteractions(sendButton);
        verifyNoInteractions(tosView);
    }

    public void testResend_withVoiceEnabled() throws Exception {
        controller.voiceEnabled = true;

        controller.resend();

        assertResendWithVoiceEnabled();
    }

    private void assertResendWithVoiceEnabled() {
        assertTrue(controller.resendState);
        verify(sendButton).setStatesText(R.string.dgts__call_me, R.string.dgts__calling,
                R.string.dgts__calling);
        verify(tosView).setText(R.string.dgts__terms_text_call_me);
    }

    public void testResend_withVoiceDisabled() throws Exception {
        controller.resend();

        assertTrue(controller.resendState);
        verifyNoInteractions(sendButton);
        verifyNoInteractions(tosView);
    }

    public void testCreateCompositeCallback_operatorUnsupportedWithVoiceEnabled() throws Exception {
        assertFalse(controller.voiceEnabled);
        final LoginOrSignupComposer loginOrSignupComposer =
                controller.createCompositeCallback(context, PHONE_WITH_COUNTRY_CODE);
        loginOrSignupComposer.failure(new OperatorUnsupportedException(ERROR_MESSAGE, 1,
                createAuthConfig(true, true, true)));

        assertTrue(controller.voiceEnabled);
        assertResendWithVoiceEnabled();
        verify(phoneEditText).setError(ERROR_MESSAGE);
        verify(sendButton).showError();
    }

    public void testCreateCompositeCallback_operatorUnsupportedWithVoiceDisable() throws Exception {
        assertFalse(controller.voiceEnabled);
        controller.handleError(context, new OperatorUnsupportedException(ERROR_MESSAGE,
                TwitterApiErrorConstants.OPERATOR_UNSUPPORTED, createAuthConfig(true, false,
                true)));
        assertFalse(controller.voiceEnabled);
        verify(phoneEditText).setError(ERROR_MESSAGE);
        verify(sendButton).showError();
    }

    public void testCreateCompositeCallback_otherFailure() {
        final LoginOrSignupComposer loginOrSignupComposer =
                controller.createCompositeCallback(context, PHONE_WITH_COUNTRY_CODE);
        loginOrSignupComposer.failure(new DigitsException(ERROR_MESSAGE, 1, null));
        verify(phoneEditText).setError(ERROR_MESSAGE);
        verify(sendButton).showError();
    }

    public void testRetryScribing() throws Exception {
        controller.errorCount = 1;
        controller.executeRequest(context);
        verify(digitsEventCollector)
                .retryClickOnPhoneScreen(digitsEventDetailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = digitsEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
    }

    public void testSendFailure() throws Exception {
        when(countrySpinner.getTag()).thenReturn(COUNTRY_INFO);
        when(phoneEditText.getText()).thenReturn(Editable.Factory.getInstance()
                .newEditable(PHONE));
        final ArgumentCaptor<Bundle> captor = ArgumentCaptor.forClass(Bundle.class);

        controller.sendFailure(PhoneNumberActivityDelegate.CANCELLATION_EXCEPTION_MESSAGE);

        verify(resultReceiver).send(eq(LoginResultReceiver.RESULT_ERROR), captor.capture());
        final Bundle bundle = captor.getValue();
        assertEquals(PhoneNumberActivityDelegate.CANCELLATION_EXCEPTION_MESSAGE,
                bundle.getString(LoginResultReceiver.KEY_ERROR));
        final DigitsEventDetailsBuilder builder = bundle.getParcelable(
                DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);
        assertNotNull(builder);
        assertEquals(COUNTRY, builder.country);
    }

    private AuthConfig createAuthConfig(boolean tosUpdate, boolean isVoiceEnabled,
                                        boolean isEmailEnabled) {
        final AuthConfig authConfig = new AuthConfig();
        authConfig.tosUpdate = tosUpdate;
        authConfig.isVoiceEnabled = isVoiceEnabled;
        authConfig.isEmailEnabled = isEmailEnabled;
        return authConfig;
    }

    public Verification getVerification() {
        return verification;
    }
}
