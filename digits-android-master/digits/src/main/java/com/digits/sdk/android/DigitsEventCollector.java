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
import com.digits.sdk.android.DigitsScribeConstants.Element;

import java.util.Set;

class DigitsEventCollector {
    private final DigitsScribeClient digitsScribeClient;
    private final Set<DigitsEventLogger> eventLoggers;
    private final FailFastEventDetailsChecker failFastEventDetailsChecker;

    DigitsEventCollector(DigitsScribeClient digitsScribeClient,
                         FailFastEventDetailsChecker failFastEventDetailsChecker,
                         Set<DigitsEventLogger> loggers){

        if (digitsScribeClient == null) {
            throw new IllegalArgumentException("digits scribe client must not be null");
        }

        if (failFastEventDetailsChecker == null) {
            throw new IllegalArgumentException("failFastEventDetailsChecker must not be null");
        }

        if (loggers == null) {
            throw new IllegalArgumentException("loggers must not be null");
        }

        this.digitsScribeClient = digitsScribeClient;
        this.failFastEventDetailsChecker = failFastEventDetailsChecker;
        this.eventLoggers = loggers;
    }

    //Auth/External API events
    public void authImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.loginBegin(details);

        digitsScribeClient.impression(Component.EMPTY);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.loginBegin(details);
        }
    }

    public void authSuccess(DigitsEventDetails details) {
        failFastEventDetailsChecker.loginSuccess(details);

        digitsScribeClient.loginSuccess();
        for (DigitsEventLogger logger: eventLoggers) {
            logger.loginSuccess(details);
        }
    }

    public void authFailure(DigitsEventDetails details) {
        failFastEventDetailsChecker.loginFailure(details);

        digitsScribeClient.failure(Component.EMPTY);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.loginFailure(details);
        }
    }

    //Logout event
    public void authCleared(LogoutEventDetails details) {
        failFastEventDetailsChecker.logout(details);

        //No scribing identified
        for (DigitsEventLogger logger: eventLoggers) {
            logger.logout(details);
        }
    }

    //Phone screen events
    public void phoneScreenImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.phoneNumberImpression(details);

        digitsScribeClient.impression(Component.AUTH);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.phoneNumberImpression(details);
        }
    }
    public void countryCodeClickOnPhoneScreen() {
        digitsScribeClient.click(Component.AUTH, Element.COUNTRY_CODE);
    }

    public void submitClickOnPhoneScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.phoneNumberSubmit(details);

        digitsScribeClient.click(Component.AUTH, Element.SUBMIT);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.phoneNumberSubmit(details);
        }
    }

    public void retryClickOnPhoneScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.phoneNumberSubmit(details);

        digitsScribeClient.click(Component.AUTH, Element.RETRY);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.phoneNumberSubmit(details);
        }
    }

    public void submitPhoneSuccess(DigitsEventDetails details) {
        failFastEventDetailsChecker.phoneNumberSuccess(details);

        digitsScribeClient.success(Component.AUTH);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.phoneNumberSuccess(details);
        }
    }

    public void submitPhoneFailure() {
        digitsScribeClient.failure(Component.AUTH);
    }

    public void submitPhoneException(DigitsException exception) {
        digitsScribeClient.error(Component.AUTH, exception);
    }

    //Login screen events
    public void loginScreenImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.confirmationCodeImpression(details);

        digitsScribeClient.impression(Component.LOGIN);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.confirmationCodeImpression(details);
        }
    }

    public void submitClickOnLoginScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.confirmationCodeSubmit(details);

        digitsScribeClient.click(Component.LOGIN, Element.SUBMIT);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.confirmationCodeSubmit(details);
        }
    }

    public void resendClickOnLoginScreen() {
        digitsScribeClient.click(Component.LOGIN, Element.RESEND);
    }

    public void callMeClickOnLoginScreen() {
        digitsScribeClient.click(Component.LOGIN, Element.CALL);
    }

    public void loginCodeSuccess(DigitsEventDetails details) {
        failFastEventDetailsChecker.confirmationCodeSuccess(details);

        digitsScribeClient.success(Component.LOGIN);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.confirmationCodeSuccess(details);
        }
    }

    public void loginFailure() {
        digitsScribeClient.failure(Component.LOGIN);
    }

    public void loginException(DigitsException exception) {
        digitsScribeClient.error(Component.LOGIN, exception);
    }

    //Signup screen events
    public void signupScreenImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.confirmationCodeImpression(details);

        digitsScribeClient.impression(Component.SIGNUP);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.confirmationCodeImpression(details);
        }
    }

    public void submitClickOnSignupScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.confirmationCodeSubmit(details);

        digitsScribeClient.click(Component.SIGNUP, Element.SUBMIT);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.confirmationCodeSubmit(details);
        }
    }

    public void resendClickOnSignupScreen() {
        digitsScribeClient.click(Component.SIGNUP, Element.RESEND);
    }

    public void callMeClickOnSignupScreen() {
        digitsScribeClient.click(Component.SIGNUP, Element.CALL);
    }

    public void signupSuccess(DigitsEventDetails details) {
        failFastEventDetailsChecker.confirmationCodeSuccess(details);

        digitsScribeClient.success(Component.SIGNUP);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.confirmationCodeSuccess(details);
        }
    }

    public void signupFailure() {
        digitsScribeClient.failure(Component.SIGNUP);
    }

    public void signupException(DigitsException exception) {
        digitsScribeClient.error(Component.SIGNUP, exception);
    }

    //Pin screen events
    public void pinScreenImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.twoFactorPinImpression(details);

        digitsScribeClient.impression(Component.PIN);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.twoFactorPinImpression(details);
        }
    }

    public void submitClickOnPinScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.twoFactorPinSubmit(details);

        digitsScribeClient.click(Component.PIN, Element.SUBMIT);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.twoFactorPinSubmit(details);
        }
    }

    public void twoFactorPinVerificationSuccess(DigitsEventDetails details) {
        failFastEventDetailsChecker.twoFactorPinSuccess(details);

        digitsScribeClient.success(Component.PIN);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.twoFactorPinSuccess(details);
        }
    }

    public void twoFactorPinVerificationFailure() {
        digitsScribeClient.failure(Component.PIN);
    }

    public void twoFactorPinVerificationException(DigitsException exception) {
        digitsScribeClient.error(Component.PIN, exception);
    }

    //Email screen events
    public void emailScreenImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.emailImpression(details);

        digitsScribeClient.impression(Component.EMAIL);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.emailImpression(details);
        }
    }

    public void submitClickOnEmailScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.emailSubmit(details);

        digitsScribeClient.click(Component.EMAIL, Element.SUBMIT);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.emailSubmit(details);
        }
    }

    public void submitEmailSuccess(DigitsEventDetails details) {
        failFastEventDetailsChecker.emailSuccess(details);

        digitsScribeClient.success(Component.EMAIL);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.emailSuccess(details);
        }
    }

    public void submitEmailFailure() {
        digitsScribeClient.failure(Component.EMAIL);
    }

    public void submitEmailException(DigitsException exception) {
        digitsScribeClient.error(Component.EMAIL, exception);
    }


    //Failure screen events
    public void failureScreenImpression(DigitsEventDetails details) {
        failFastEventDetailsChecker.failureImpression(details);

        digitsScribeClient.impression(Component.FAILURE);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.failureImpression(details);
        }
    }

    public void retryClickOnFailureScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.failureRetryClick(details);

        digitsScribeClient.click(Component.FAILURE, Element.RETRY);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.failureRetryClick(details);
        }
    }

    public void dismissClickOnFailureScreen(DigitsEventDetails details) {
        failFastEventDetailsChecker.failureDismissClick(details);

        digitsScribeClient.click(Component.FAILURE, Element.DISMISS);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.failureDismissClick(details);
        }
    }

    //Contacts upload  events
    public void contactsPermissionImpression(ContactsPermissionForDigitsImpressionDetails details) {
        failFastEventDetailsChecker.contactsPermissionForDigitsImpression(details);

        digitsScribeClient.impression(Component.CONTACTS);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.contactsPermissionForDigitsImpression(details);
        }
    }

    public void backClickOnContactScreen() {
        digitsScribeClient.click(Component.CONTACTS, Element.BACK);
    }

    public void contactsPermissionDeferred(ContactsPermissionForDigitsDeclinedDetails details) {
        failFastEventDetailsChecker.contactsPermissionForDigitsDeferred(details);

        digitsScribeClient.click(Component.CONTACTS, Element.CANCEL);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.contactsPermissionForDigitsDeferred(details);
        }
    }

    public void contactsPermissionApproved(ContactsPermissionForDigitsApprovedDetails details) {
        failFastEventDetailsChecker.contactsPermissionForDigitsApproved(details);

        digitsScribeClient.click(Component.CONTACTS, Element.SUBMIT);
        for (DigitsEventLogger logger: eventLoggers) {
            logger.contactsPermissionForDigitsApproved(details);
        }
    }

    //Contact upload events
    public void startContactsUpload(ContactsUploadStartDetails details) {
        failFastEventDetailsChecker.contactsUploadStart(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsUploadStart(details);
        }
    }

    public void succeedContactsUpload(ContactsUploadSuccessDetails details) {
        failFastEventDetailsChecker.contactsUploadSuccess(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsUploadSuccess(details);
        }
    }

    public void failedContactsUpload(ContactsUploadFailureDetails details) {
        failFastEventDetailsChecker.contactsUploadFailure(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsUploadFailure(details);
        }
    }

    //Contacts found events
    public void startFindMatches(ContactsLookupStartDetails details) {
        failFastEventDetailsChecker.contactsLookupStart(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsLookupStart(details);
        }
    }

    public void failedFindMatches(ContactsLookupFailureDetails details) {
        failFastEventDetailsChecker.contactsLookupFailure(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsLookupFailure(details);
        }
    }

    public void succeedFindMatches(ContactsLookupSuccessDetails details) {
        failFastEventDetailsChecker.contactsLookupSuccess(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsLookupSuccess(details);
        }
    }

    //Contacts deleted
    public void startDeleteContacts(ContactsDeletionStartDetails details){
        failFastEventDetailsChecker.contactsDeletionStart(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsDeletionStart(details);
        }
    }

    public void succeedDeleteContacts(ContactsDeletionSuccessDetails details){
        failFastEventDetailsChecker.contactsDeletionSuccess(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsDeletionSuccess(details);
        }
    }

    public void failedDeleteContacts(ContactsDeletionFailureDetails details){
        failFastEventDetailsChecker.contactsDeletionFailure(details);

        //No scribing identified
        for (DigitsEventLogger logger : eventLoggers) {
            logger.contactsDeletionFailure(details);
        }
    }
}
