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

import org.junit.Before;
import org.junit.Test;

import static org.junit.Assert.assertEquals;
import static org.mockito.Mockito.mock;


public class DigitsAuthConfigTests {
    private AuthCallback callback;
    private ConfirmationCodeCallback confirmationCodeCallback;

    @Before
    public void setUp() throws Exception {
        callback = mock(AuthCallback.class);
        confirmationCodeCallback = mock(ConfirmationCodeCallback.class);
    }

    @Test
    public void testDigitsAuthConfigBuilder_nullCallback() {
        final DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                .withPhoneNumber(TestConstants.PHONE)
                .withThemeResId(TestConstants.THEME_ID)
                .withEmailCollection();
        try {
            digitsAuthConfigBuilder.build();
        } catch (IllegalArgumentException ex) {
            assertEquals("AuthCallback must not be null",
                    ex.getMessage());
        }
    }

    @Test
    public void testDigitsAuthConfigBuilder_nullPhoneNumberWithConfirmationCodeCallback() {
        final DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                .withThemeResId(TestConstants.THEME_ID)
                .withPartnerKey(TestConstants.PARTNER_KEY)
                .withAuthCallBack(callback)
                .withEmailCollection()
                .withCustomPhoneNumberScreen(confirmationCodeCallback);
        try {
            digitsAuthConfigBuilder.build();
        } catch (IllegalArgumentException ex) {
            assertEquals("PhoneNumber and partnerKey must be set when confirmationCodeCallback " +
                            "is used. Please contact support for more information.",
                    ex.getMessage());
        }
    }

    @Test
    public void testDigitsAuthConfigBuilder_nullPartnerKeyWithConfirmationCodeCallback() {
        final DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                .withThemeResId(TestConstants.THEME_ID)
                .withPhoneNumber(TestConstants.PHONE)
                .withAuthCallBack(callback)
                .withEmailCollection()
                .withCustomPhoneNumberScreen(confirmationCodeCallback);
        try {
            digitsAuthConfigBuilder.build();
        } catch (IllegalArgumentException ex) {
            assertEquals("PhoneNumber and partnerKey must be set when confirmationCodeCallback " +
                            "is used. Please contact support for more information.",
                    ex.getMessage());
        }
    }

    @Test
    public void testDigitsAuthConfigBuilder_allParamSuccess() {
        final DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                .withPhoneNumber(TestConstants.PHONE)
                .withThemeResId(TestConstants.THEME_ID)
                .withAuthCallBack(callback)
                .withCustomPhoneNumberScreen(confirmationCodeCallback)
                .withPartnerKey(TestConstants.PARTNER_KEY)
                .withEmailCollection();

        final DigitsAuthConfig digitsAuthConfig = digitsAuthConfigBuilder.build();
        assertEquals(true, digitsAuthConfig.isEmailRequired);
        assertEquals(TestConstants.THEME_ID, digitsAuthConfig.themeResId);
        assertEquals(TestConstants.PHONE, digitsAuthConfig.phoneNumber);
        assertEquals(callback, digitsAuthConfig.authCallback);
    }

    @Test
    public void testDigitsAuthConfigBuilder_partialParamSuccess() {
        final DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withCustomPhoneNumberScreen(confirmationCodeCallback)
                .withPartnerKey(TestConstants.PARTNER_KEY)
                .withPhoneNumber(TestConstants.PHONE);

        final DigitsAuthConfig digitsAuthConfig = digitsAuthConfigBuilder.build();
        assertEquals(callback, digitsAuthConfig.authCallback);
        assertEquals(false, digitsAuthConfig.isEmailRequired);
        assertEquals(TestConstants.PHONE, digitsAuthConfig.phoneNumber);
        assertEquals(confirmationCodeCallback, digitsAuthConfig.confirmationCodeCallback);
    }

    @Test
    public void testDigitsAuthConfigBuilder_copyConstructors() {
        final DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                .withPhoneNumber(TestConstants.PHONE)
                .withThemeResId(TestConstants.THEME_ID)
                .withPartnerKey(TestConstants.PARTNER_KEY)
                .withCustomPhoneNumberScreen(confirmationCodeCallback)
                .withAuthCallBack(callback)
                .withEmailCollection();

        final DigitsAuthConfig.Builder digitsAuthConfigBuilder1 =
                new DigitsAuthConfig.Builder(digitsAuthConfigBuilder);

        assertEquals(digitsAuthConfigBuilder.authCallback, digitsAuthConfigBuilder1.authCallback);
        assertEquals(digitsAuthConfigBuilder.isEmailRequired,
                digitsAuthConfigBuilder1.isEmailRequired);
        assertEquals(digitsAuthConfigBuilder.phoneNumber, digitsAuthConfigBuilder1.phoneNumber);
        assertEquals(digitsAuthConfigBuilder.themeResId, digitsAuthConfigBuilder1.themeResId);
        assertEquals(digitsAuthConfigBuilder.partnerKey, TestConstants.PARTNER_KEY);
        assertEquals(digitsAuthConfigBuilder.confirmationCodeCallback, confirmationCodeCallback);
    }
}
