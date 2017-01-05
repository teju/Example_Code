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

import java.util.Locale;

class FailFastEventDetailsChecker extends DigitsEventLogger {

    static final FailFastEventDetailsChecker instance = new FailFastEventDetailsChecker();

    private FailFastEventDetailsChecker() { }

    @Override
    public void loginBegin(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void loginSuccess(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void loginFailure(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void logout(LogoutEventDetails s) {
        throwIncompleteDetailsExWhenFalse(
                s.country != null && s.language != null,
                s
        );
    }

    @Override
    public void phoneNumberImpression(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void phoneNumberSubmit(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void phoneNumberSuccess(DigitsEventDetails d) {
        super.phoneNumberSuccess(d);
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void confirmationCodeImpression(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void confirmationCodeSubmit(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void confirmationCodeSuccess(DigitsEventDetails d) {
        super.confirmationCodeSuccess(d);
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void twoFactorPinImpression(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void twoFactorPinSubmit(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void twoFactorPinSuccess(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void emailImpression(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void emailSubmit(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void emailSuccess(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.country != null && d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void failureImpression(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void failureRetryClick(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    @Override
    public void failureDismissClick(DigitsEventDetails d) {
        throwIncompleteDetailsExWhenFalse(
                d.language != null && d.elapsedTimeInMillis != null,
                d
        );
    }

    // These events have no nullable parameters, and thus no input validation to be done

    @Override
    public void contactsUploadSuccess(ContactsUploadSuccessDetails details) {
        // No input validations to be done here

    }

    @Override
    public void contactsUploadFailure(ContactsUploadFailureDetails details) {
        // No input validations to be done here

    }

    @Override
    public void contactsLookupSuccess(ContactsLookupSuccessDetails details) {
        // No input validations to be done here

    }


    // EmptyDigitsDetails: These events have no parameters, and thus no input validation to be done

    @Override
    public void contactsPermissionForDigitsImpression(
            ContactsPermissionForDigitsImpressionDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsPermissionForDigitsApproved(
            ContactsPermissionForDigitsApprovedDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsPermissionForDigitsDeferred(
            ContactsPermissionForDigitsDeclinedDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsUploadStart(ContactsUploadStartDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsLookupStart(ContactsLookupStartDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsLookupFailure(ContactsLookupFailureDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsDeletionStart(ContactsDeletionStartDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsDeletionSuccess(ContactsDeletionSuccessDetails details) {
        // No input validations to be done here
    }

    @Override
    public void contactsDeletionFailure(ContactsDeletionFailureDetails details) {
        // No input validations to be done here
    }

    private <T> void throwIncompleteDetailsExWhenFalse(boolean bool, T d) {
        if (!bool) {
            throw new IllegalArgumentException(String.format(Locale.US,
                    "Incomplete DigitsEventDetails object %s", d.toString()));
        }
    }

    private void throwIncompleteDetailsExWhenFalse(boolean bool, LogoutEventDetails d) {
        if (!bool) {
            throw new IllegalArgumentException(String.format(Locale.US,
                    "Incomplete DigitsEventDetails object %s", d.toString()));
        }
    }
}
