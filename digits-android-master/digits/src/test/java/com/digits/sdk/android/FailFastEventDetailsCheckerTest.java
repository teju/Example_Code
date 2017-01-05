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

import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)

public class FailFastEventDetailsCheckerTest {
    private FailFastEventDetailsChecker failFastEventDetailsChecker =
            FailFastEventDetailsChecker.instance;
    private final DigitsEventDetails detailsWithoutCountry = new DigitsEventDetailsBuilder()
            .withAuthStartTime(1L).withLanguage("en").withCurrentTime(2L).build();
    private final DigitsEventDetails details = new DigitsEventDetailsBuilder()
            .withAuthStartTime(1L).withLanguage("en").withCountry("US").withCurrentTime(2L).build();
    private final LogoutEventDetails logoutEventDetails =
            new LogoutEventDetails("en", "US");

    private final LogoutEventDetails logoutEventDetailsWithoutCountry =
            new LogoutEventDetails("en", null);

    @Test(expected = IllegalArgumentException.class)
    public void testLoginSuccess_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.loginSuccess(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testLoginFailure_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.loginFailure(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testLoginSessionCleared_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.logout(logoutEventDetailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testPhoneNumberSubmit_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.phoneNumberSubmit(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testPhoneNumberSuccess_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.phoneNumberSuccess(detailsWithoutCountry);
    }


    @Test(expected = IllegalArgumentException.class)
    public void testConfirmationCodeImpression_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.confirmationCodeImpression(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testConfirmationCodeSubmit_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.confirmationCodeSubmit(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testConfirmationCodeSuccess_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.confirmationCodeSuccess(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testTwoFactorPinImpression_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.twoFactorPinImpression(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testTwoFactorPinSubmit_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.twoFactorPinSubmit(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testTwoFactorPinSuccess_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.twoFactorPinSuccess(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testEmailImpression_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.emailImpression(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testEmailSubmit_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.emailSubmit(detailsWithoutCountry);
    }

    @Test(expected = IllegalArgumentException.class)
    public void testEmailSuccess_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.emailSuccess(detailsWithoutCountry);
    }

    public void testLoginSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.loginSuccess(details);
    }

    public void testLoginFailure_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.loginFailure(details);
    }

    public void testLoginSessionCleared_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.logout(logoutEventDetails);
    }

    public void testPhoneNumberSubmit_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.phoneNumberSubmit(details);
    }

    public void testPhoneNumberSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.phoneNumberSuccess(details);
    }


    public void testConfirmationCodeImpression_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.confirmationCodeImpression(details);
    }

    public void testConfirmationCodeSubmit_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.confirmationCodeSubmit(details);
    }

    public void testConfirmationCodeSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.confirmationCodeSuccess(details);
    }

    public void testTwoFactorPinImpression_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.twoFactorPinImpression(details);
    }

    public void testTwoFactorPinSubmit_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.twoFactorPinSubmit(details);
    }

    public void testTwoFactorPinSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.twoFactorPinSuccess(details);
    }

    public void testEmailImpression_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.emailImpression(details);
    }

    public void testEmailSubmit_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.emailSubmit(details);
    }

    public void testEmailSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.emailSuccess(details);
    }

    public void testPhoneNumberImpression_incompleteDetailsObject() throws Exception {
        failFastEventDetailsChecker.phoneNumberImpression(detailsWithoutCountry);
    }

    public void testFailureImpression_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.failureImpression(detailsWithoutCountry);
    }

    public void testFailureRetryClick_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.failureRetryClick(detailsWithoutCountry);
    }

    public void testFailureDismissClick_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.failureDismissClick(detailsWithoutCountry);
    }

    public void testContactsUploadSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.contactsUploadSuccess(new ContactsUploadSuccessDetails(1, 1));
    }

    public void testContactsUploadFailure_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.contactsUploadFailure(new ContactsUploadFailureDetails(1, 1));
    }

    public void testContactsLookupSuccess_completeDetailsObject() throws Exception {
        failFastEventDetailsChecker.contactsLookupSuccess(new ContactsLookupSuccessDetails(1));
    }
}
