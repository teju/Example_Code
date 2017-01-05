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
package com.example.app.digits;

import com.crashlytics.android.answers.Answers;
import com.crashlytics.android.answers.CustomEvent;

import com.digits.sdk.android.ContactsDeletionFailureDetails;
import com.digits.sdk.android.ContactsDeletionStartDetails;
import com.digits.sdk.android.ContactsDeletionSuccessDetails;
import com.digits.sdk.android.ContactsLookupFailureDetails;
import com.digits.sdk.android.ContactsLookupStartDetails;
import com.digits.sdk.android.ContactsLookupSuccessDetails;
import com.digits.sdk.android.ContactsPermissionForDigitsApprovedDetails;
import com.digits.sdk.android.ContactsPermissionForDigitsDeclinedDetails;
import com.digits.sdk.android.ContactsPermissionForDigitsImpressionDetails;
import com.digits.sdk.android.ContactsUploadFailureDetails;
import com.digits.sdk.android.ContactsUploadStartDetails;
import com.digits.sdk.android.ContactsUploadSuccessDetails;
import com.digits.sdk.android.Digits;
import com.digits.sdk.android.DigitsEventLogger;
import com.digits.sdk.android.DigitsEventDetails;
import com.digits.sdk.android.LogoutEventDetails;

/**
 * Log events to any analytics provider of your choice by implementing a CustomLogger injected into
 * {@link Digits.Builder#withDigitsEventLogger(DigitsEventLogger)}()}.
 * 
 * In this example we log events to Fabric's {@link Answers}.
 */
public class CustomLogger extends DigitsEventLogger {
    @Override
    public void loginBegin(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("LoginBegin")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void phoneNumberImpression(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("PhoneNumberImpression")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void phoneNumberSubmit(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("PhoneNumberSubmit")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void phoneNumberSuccess(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("PhoneNumberSuccess")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void confirmationCodeImpression(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ConfirmationCodeImpression")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void confirmationCodeSubmit(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ConfirmationCodeSubmit")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void confirmationCodeSuccess(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ConfirmationCodeSuccess")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void twoFactorPinImpression(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("TwoFactorPinImpression")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void twoFactorPinSubmit(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("TwoFactorPinSubmit")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void twoFactorPinSuccess(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("TwoFactorPinSuccess")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void emailImpression(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("EmailImpression")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void emailSubmit(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("EmailSubmit")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void emailSuccess(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("EmailSuccess")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void failureImpression(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("FailureImpression")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void failureRetryClick(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("FailureRetryClick")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void failureDismissClick(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("FailureDismissClick")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void loginSuccess(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("LoginSuccess")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void loginFailure(DigitsEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("LoginFailure")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country)
                .putCustomAttribute("ElapsedTime", details.elapsedTimeInMillis / 1000));
    }

    @Override
    public void logout(LogoutEventDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("Logout")
                .putCustomAttribute("Language", details.language)
                .putCustomAttribute("Country", details.country));
    }

    @Override
    public void contactsPermissionForDigitsImpression(
            ContactsPermissionForDigitsImpressionDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsPermissionForDigitsImpression"));
    }

    @Override
    public void contactsPermissionForDigitsApproved(
            ContactsPermissionForDigitsApprovedDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsPermissionForDigitsApproved"));
    }

    @Override
    public void contactsPermissionForDigitsDeferred(
            ContactsPermissionForDigitsDeclinedDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsPermissionForDigitsDeferred"));
    }

    @Override
    public void contactsUploadStart(ContactsUploadStartDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsUploadStart"));
    }

    @Override
    public void contactsUploadSuccess(ContactsUploadSuccessDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsUploadSuccess")
                .putCustomAttribute("Uploaded", details.successContacts)
                .putCustomAttribute("Total", details.totalContacts));
    }

    @Override
    public void contactsUploadFailure(ContactsUploadFailureDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsUploadFailure")
                .putCustomAttribute("Failed", details.failedContacts)
                .putCustomAttribute("Total", details.totalContacts));
    }

    @Override
    public void contactsLookupStart(ContactsLookupStartDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsLookupStart"));
    }

    @Override
    public void contactsLookupSuccess(ContactsLookupSuccessDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsLookupSuccess")
                .putCustomAttribute("Matches", details.matchCount));
    }

    @Override
    public void contactsLookupFailure(ContactsLookupFailureDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsLookupFailure"));
    }

    @Override
    public void contactsDeletionStart(ContactsDeletionStartDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsDeletionStart"));
    }

    @Override
    public void contactsDeletionSuccess(ContactsDeletionSuccessDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsDeletionSuccess"));
    }

    @Override
    public void contactsDeletionFailure(ContactsDeletionFailureDetails details) {
        Answers.getInstance().logCustom(new CustomEvent("ContactsDeletionFailure"));
    }
}