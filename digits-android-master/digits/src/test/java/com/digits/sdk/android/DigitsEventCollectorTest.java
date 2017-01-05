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

import com.digits.sdk.android.DigitsScribeConstants.Component;
import com.twitter.sdk.android.core.internal.scribe.EventNamespace;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.mockito.Captor;
import org.mockito.MockitoAnnotations;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.HashSet;

import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.times;
import static org.mockito.Mockito.verify;
import static org.mockito.Matchers.any;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsEventCollectorTest {
    @Captor
    private ArgumentCaptor<EventNamespace> eventNamespaceArgumentCaptor;
    private final DigitsScribeClient digitsScribeClient = mock(DigitsScribeClient.class);
    private final FailFastEventDetailsChecker failFastEventDetailsChecker =
            mock(FailFastEventDetailsChecker.class);
    private DigitsEventCollector digitsEventCollector;
    private final DigitsException exception = new DigitsException("exception");
    private final DigitsEventDetails details = new DigitsEventDetailsBuilder()
            .withAuthStartTime(System.currentTimeMillis())
            .withCurrentTime(System.currentTimeMillis())
            .build();
    private final DigitsEventLogger digitsEventLogger = mock(DigitsEventLogger.class);
    private HashSet<DigitsEventLogger> loggers;
    private final LogoutEventDetails logoutEventDetails =
            new LogoutEventDetails("en", "US");

    @Before
    public void setUp() throws Exception {
        MockitoAnnotations.initMocks(this);
        loggers = new HashSet();
        loggers.add(digitsEventLogger);
        loggers.add(digitsEventLogger);
        digitsEventCollector = new DigitsEventCollector(digitsScribeClient,
                failFastEventDetailsChecker, loggers);
    }

    @Test
    public void testAuthImpression() {
        digitsEventCollector.authImpression(details);
        verify(failFastEventDetailsChecker).loginBegin(details);
        verify(digitsScribeClient).impression(Component.EMPTY);
        verify(digitsEventLogger, times(1)).loginBegin(details);
    }

    @Test
    public void testAuthSuccess() {
        digitsEventCollector.authSuccess(details);
        verify(failFastEventDetailsChecker).loginSuccess(details);
        verify(digitsScribeClient).loginSuccess();
        verify(digitsEventLogger, times(1)).loginSuccess(details);
    }

    @Test
    public void testAuthFailure() {
        digitsEventCollector.authFailure(details);
        verify(failFastEventDetailsChecker).loginFailure(details);
        verify(digitsScribeClient).failure(Component.EMPTY);
        verify(digitsEventLogger, times(1)).loginFailure(details);
    }

    //Phone screen events
    @Test
    public void testPhoneScreenImpression() {
        digitsEventCollector.phoneScreenImpression(details);
        verify(failFastEventDetailsChecker).phoneNumberImpression(details);
        verify(digitsScribeClient).impression(Component.AUTH);
        verify(digitsEventLogger, times(1)).phoneNumberImpression(details);
    }

    @Test
    public void testCountryCodeClickOnPhoneScreen() {
        digitsEventCollector.countryCodeClickOnPhoneScreen();
        verify(digitsScribeClient).click(Component.AUTH,
                DigitsScribeConstants.Element.COUNTRY_CODE);
    }

    @Test
    public void testSubmitClickOnPhoneScreen() {
        digitsEventCollector.submitClickOnPhoneScreen(details);
        verify(failFastEventDetailsChecker).phoneNumberSubmit(details);
        verify(digitsScribeClient).click(Component.AUTH, DigitsScribeConstants.Element.SUBMIT);
        verify(digitsEventLogger, times(1)).phoneNumberSubmit(details);
    }

    @Test
    public void testRetryClickOnPhoneScreen() {
        digitsEventCollector.retryClickOnPhoneScreen(details);
        verify(failFastEventDetailsChecker).phoneNumberSubmit(details);
        verify(digitsScribeClient).click(Component.AUTH, DigitsScribeConstants.Element.RETRY);
        verify(digitsEventLogger, times(1)).phoneNumberSubmit(details);
    }

    @Test
    public void testSubmitPhoneSuccess() {
        digitsEventCollector.submitPhoneSuccess(details);
        verify(failFastEventDetailsChecker).phoneNumberSuccess(details);
        verify(digitsScribeClient).success(Component.AUTH);
        verify(digitsEventLogger, times(1)).phoneNumberSuccess(details);
    }

    @Test
    public void testSubmitPhoneFailure() {
        digitsEventCollector.submitPhoneFailure();
        verify(digitsScribeClient).failure(Component.AUTH);
    }

    @Test
    public void testSubmitPhoneException() {
        digitsEventCollector.submitPhoneException(exception);
        verify(digitsScribeClient).error(Component.AUTH, exception);
    }

    //Login screen events
    @Test
    public void testLoginScreenImpression() {
        digitsEventCollector.loginScreenImpression(details);
        verify(failFastEventDetailsChecker).confirmationCodeImpression(details);
        verify(digitsScribeClient).impression(Component.LOGIN);
        verify(digitsEventLogger, times(1)).confirmationCodeImpression(details);
    }

    @Test
    public void testSubmitClickOnLoginScreen() {
        digitsEventCollector.submitClickOnLoginScreen(details);
        verify(failFastEventDetailsChecker).confirmationCodeSubmit(details);
        verify(digitsScribeClient).click(Component.LOGIN, DigitsScribeConstants.Element.SUBMIT);
        verify(digitsEventLogger, times(1)).confirmationCodeSubmit(details);
    }

    @Test
    public void testResendClickOnLoginScreen() {
        digitsEventCollector.resendClickOnLoginScreen();
        verify(digitsScribeClient).click(Component.LOGIN, DigitsScribeConstants.Element.RESEND);
    }

    @Test
    public void testCallMeClickOnLoginScreen() {
        digitsEventCollector.callMeClickOnLoginScreen();
        verify(digitsScribeClient).click(Component.LOGIN, DigitsScribeConstants.Element.CALL);
    }

    @Test
    public void testLoginCodeSuccess() {
        digitsEventCollector.loginCodeSuccess(details);
        verify(failFastEventDetailsChecker).confirmationCodeSuccess(details);
        verify(digitsScribeClient).success(Component.LOGIN);
        verify(digitsEventLogger, times(1)).confirmationCodeSuccess(details);
    }

    @Test
    public void testLoginFailure() {
        digitsEventCollector.loginFailure();
        verify(digitsScribeClient).failure(Component.LOGIN);
    }

    @Test
    public void testLoginException() {
        digitsEventCollector.loginException(exception);
        verify(digitsScribeClient).error(Component.LOGIN, exception);
    }

    //Signup screen events
    @Test
    public void testConfirmationScreenImpression() {
        digitsEventCollector.signupScreenImpression(details);
        verify(failFastEventDetailsChecker).confirmationCodeImpression(details);
        verify(digitsScribeClient).impression(Component.SIGNUP);
        verify(digitsEventLogger, times(1)).confirmationCodeImpression(details);
    }

    @Test
    public void testSubmitClickOnSignupScreen() {
        digitsEventCollector.submitClickOnSignupScreen(details);
        verify(failFastEventDetailsChecker).confirmationCodeSubmit(details);
        verify(digitsScribeClient).click(Component.SIGNUP, DigitsScribeConstants.Element.SUBMIT);
        verify(digitsEventLogger, times(1)).confirmationCodeSubmit(details);
    }

    @Test
    public void testResendClickOnSignupScreen() {
        digitsEventCollector.resendClickOnSignupScreen();
        verify(digitsScribeClient).click(Component.SIGNUP, DigitsScribeConstants.Element.RESEND);
    }

    @Test
    public void testCallMeClickOnSignupScreen() {
        digitsEventCollector.callMeClickOnSignupScreen();
        verify(digitsScribeClient).click(Component.SIGNUP, DigitsScribeConstants.Element.CALL);
    }

    @Test
    public void testSignupSuccess() {
        digitsEventCollector.signupSuccess(details);
        verify(failFastEventDetailsChecker).confirmationCodeSuccess(details);
        verify(digitsScribeClient).success(Component.SIGNUP);
        verify(digitsEventLogger, times(1)).confirmationCodeSuccess(details);
    }

    @Test
    public void testSignupFailure() {
        digitsEventCollector.signupFailure();
        verify(digitsScribeClient).failure(Component.SIGNUP);
    }

    @Test
    public void testSignupException() {
        digitsEventCollector.signupException(exception);
        verify(digitsScribeClient).error(Component.SIGNUP, exception);
    }

    //Pin screen events
    @Test
    public void testPinScreenImpression() {
        digitsEventCollector.pinScreenImpression(details);
        verify(failFastEventDetailsChecker).twoFactorPinImpression(details);
        verify(digitsScribeClient).impression(Component.PIN);
        verify(digitsEventLogger, times(1)).twoFactorPinImpression(details);
    }

    @Test
    public void testSubmitClickOnPinScreen() {
        digitsEventCollector.submitClickOnPinScreen(details);
        verify(failFastEventDetailsChecker).twoFactorPinSubmit(details);
        verify(digitsScribeClient).click(Component.PIN, DigitsScribeConstants.Element.SUBMIT);
        verify(digitsEventLogger, times(1)).twoFactorPinSubmit(details);
    }

    @Test
    public void testTwoFactorPinVerificationSuccess() {
        digitsEventCollector.twoFactorPinVerificationSuccess(details);
        verify(failFastEventDetailsChecker).twoFactorPinSuccess(details);
        verify(digitsScribeClient).success(Component.PIN);
        verify(digitsEventLogger, times(1)).twoFactorPinSuccess(details);
    }

    @Test
    public void testTwoFactorPinVerificationFailure() {
        digitsEventCollector.twoFactorPinVerificationFailure();
        verify(digitsScribeClient).failure(Component.PIN);
    }

    @Test
    public void testTwoFactorPinVerificationException() {
        digitsEventCollector.twoFactorPinVerificationException(exception);
        verify(digitsScribeClient).error(Component.PIN, exception);
    }

    //Email screen events
    @Test
    public void testEmailScreenImpression() {
        digitsEventCollector.emailScreenImpression(details);
        verify(failFastEventDetailsChecker).emailImpression(details);
        verify(digitsScribeClient).impression(Component.EMAIL);
        verify(digitsEventLogger, times(1)).emailImpression(details);
    }

    @Test
    public void testSubmitClickOnEmailScreen() {
        digitsEventCollector.submitClickOnEmailScreen(details);
        verify(failFastEventDetailsChecker).emailSubmit(details);
        verify(digitsScribeClient).click(Component.EMAIL, DigitsScribeConstants.Element.SUBMIT);
        verify(digitsEventLogger, times(1)).emailSubmit(details);
    }

    @Test
    public void testSubmitEmailSuccess() {
        digitsEventCollector.submitEmailSuccess(details);
        verify(failFastEventDetailsChecker).emailSuccess(details);
        verify(digitsScribeClient).success(Component.EMAIL);
        verify(digitsEventLogger, times(1)).emailSuccess(details);
    }

    @Test
    public void testSubmitEmailFailure() {
        digitsEventCollector.submitEmailFailure();
        verify(digitsScribeClient).failure(Component.EMAIL);
    }

    @Test
    public void testSubmitEmailException() {
        digitsEventCollector.submitEmailException(exception);
        verify(digitsScribeClient).error(Component.EMAIL, exception);
    }

    //Failure screen events
    @Test
    public void testFailureScreenImpression() {
        digitsEventCollector.failureScreenImpression(details);
        verify(failFastEventDetailsChecker).failureImpression(details);
        verify(digitsScribeClient).impression(Component.FAILURE);
        verify(digitsEventLogger, times(1)).failureImpression(details);
    }

    @Test
    public void testRetryClickOnFailureScreen() {
        digitsEventCollector.retryClickOnFailureScreen(details);
        verify(failFastEventDetailsChecker).failureRetryClick(details);
        verify(digitsScribeClient).click(Component.FAILURE, DigitsScribeConstants.Element.RETRY);
        verify(digitsEventLogger, times(1)).failureRetryClick(details);
    }

    @Test
    public void testDismissClickOnFailureScreen() {
        digitsEventCollector.dismissClickOnFailureScreen(details);
        verify(failFastEventDetailsChecker).failureDismissClick(details);
        verify(digitsScribeClient).click(Component.FAILURE, DigitsScribeConstants.Element.DISMISS);
        verify(digitsEventLogger, times(1)).failureDismissClick(details);
    }

    //Contacts upload screen events
    @Test
    public void testContactsPermissionForDigitsImpression() {
        digitsEventCollector.contactsPermissionImpression(
                new ContactsPermissionForDigitsImpressionDetails());
        verify(digitsScribeClient).impression(Component.CONTACTS);
    }

    @Test
    public void testContactsPermissionDeferred() {
        digitsEventCollector.contactsPermissionDeferred(
                new ContactsPermissionForDigitsDeclinedDetails());
        verify(digitsScribeClient).click(Component.CONTACTS, DigitsScribeConstants.Element.CANCEL);
    }

    @Test
    public void testBackClickOnContactScreen() {
        digitsEventCollector.backClickOnContactScreen();
        verify(digitsScribeClient).click(Component.CONTACTS, DigitsScribeConstants.Element.BACK);
    }

    @Test
    public void testContactsPermissionApproved() {
        digitsEventCollector.contactsPermissionApproved(
                new ContactsPermissionForDigitsApprovedDetails());
        verify(digitsScribeClient).click(Component.CONTACTS, DigitsScribeConstants.Element.SUBMIT);
    }

    @Test
    public void testAuthCleared_withExternalLogger() {
        digitsEventCollector.authCleared(logoutEventDetails);
        verify(failFastEventDetailsChecker).logout(logoutEventDetails);
        verify(digitsEventLogger).logout(logoutEventDetails);
    }

    // Contacts upload permissions events

    @Test
    public void testContactsPermissionForDigitsImpression_withExternalLogger() {
        testContactsPermissionForDigitsImpression();
        verify(digitsEventLogger).contactsPermissionForDigitsImpression(
                any(ContactsPermissionForDigitsImpressionDetails.class));
    }

    @Test
    public void testContactsPermissionDeferred_withExternalLogger() {
        testContactsPermissionDeferred();
        verify(digitsEventLogger).contactsPermissionForDigitsDeferred(
                any(ContactsPermissionForDigitsDeclinedDetails.class));
    }

    @Test
    public void testContactsPermissionApproved_withExternalLogger() {
        testContactsPermissionApproved();
        verify(digitsEventLogger).contactsPermissionForDigitsApproved(
                any(ContactsPermissionForDigitsApprovedDetails.class));
    }

}
