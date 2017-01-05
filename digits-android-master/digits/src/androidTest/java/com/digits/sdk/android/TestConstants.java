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

import com.twitter.sdk.android.core.TwitterAuthToken;
import com.twitter.sdk.android.core.TwitterException;

public class TestConstants {
    public static final String TOKEN = "token";
    public static final String SECRET = "secret";
    public static final long USER_ID = 11;
    public static final String ANY_PHONE = "1234566";
    public static final String VALID_EMAIL = "support@fabric.io";
    public static final String INVALID_EMAIL = "invalidEmail@";
    public static final Email EMAIL = new Email(VALID_EMAIL, false);
    public static final TwitterException ANY_EXCEPTION = new TwitterException("");


    public static DigitsSessionResponse LOGGED_OUT_USER = getDigitsSessionResponse(
            TestConstants.TOKEN, TestConstants.SECRET, DigitsSession.LOGGED_OUT_USER_ID);


    public static DigitsSessionResponse DIGITS_USER = getDigitsSessionResponse(
            TestConstants.TOKEN, TestConstants.SECRET, TestConstants.USER_ID);

    private static DigitsSessionResponse getDigitsSessionResponse(String token, String secret,
                                                                  long userId) {
        final DigitsSessionResponse response = new DigitsSessionResponse();
        response.token = token;
        response.secret = secret;
        response.userId = userId;
        return response;
    }

    public static VerifyAccountResponse getVerifyAccountResponse() {
        final VerifyAccountResponse response = new VerifyAccountResponse();
        response.phoneNumber = TestConstants.ANY_PHONE;
        response.token = new TwitterAuthToken(TestConstants.TOKEN, TestConstants.SECRET);
        response.userId = TestConstants.USER_ID;
        response.email = TestConstants.EMAIL;
        return response;
    }

    public static VerifyAccountResponse getVerifyAccountResponseNoEmail() {
        final VerifyAccountResponse response = new VerifyAccountResponse();
        response.phoneNumber = TestConstants.ANY_PHONE;
        response.token = new TwitterAuthToken(TestConstants.TOKEN, TestConstants.SECRET);
        response.userId = TestConstants.USER_ID;
        return response;
    }
}
