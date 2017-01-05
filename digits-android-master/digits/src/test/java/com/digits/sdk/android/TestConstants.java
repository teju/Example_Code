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

public class TestConstants {

    public static final String TWITTER_URL = "http://twitter.com";
    public static final String CONSUMER_KEY = "testKey";
    public static final String CONSUMER_SECRET = "testSecret";
    public static final String PARTNER_KEY = "X19EaWdpdHNAUEBydG5lcl9fdGVzdEtleQ==";
    public static final String TOKEN = "token";
    public static final String SECRET = "secret";
    public static final long USER_ID = 11;
    public static final TwitterAuthToken ANY_TOKEN = new TwitterAuthToken("", "");


    public static final String RAW_PHONE = "+123456789";
    public static final String ES_RAW_PHONE = "+3423456789";
    public static final String YE_RAW_PHONE = "+96723456789";
    public static final String PHONE = "123456789";
    public static final String PHONE_NO_COUNTRY_CODE = "23456789";
    public static final String PHONE_PLUS_SYMBOL_NO_COUNTRY_CODE = "23456789";
    public static final String US_COUNTRY_CODE = "1";
    public static final String US_ISO2 = "us";
    public static final String US_ISO3 = "usa";
    public static final String ES_COUNTRY_CODE = "34";
    public static final String YE_COUNTRY_CODE = "967";
    public static final String ES_ISO2 = "es";
    public static final String YE_ISO2 = "ye";
    public static final DigitsException ANY_EXCEPTION = new DigitsException("");
    public static final Email EMAIL = new Email("support@fabric.io", false);
    public static final boolean ANY_BOOLEAN = Boolean.TRUE;
    public static final int THEME_ID = 12;


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
        response.phoneNumber = TestConstants.PHONE;
        response.token = new TwitterAuthToken(TestConstants.TOKEN, TestConstants.SECRET);
        response.userId = TestConstants.USER_ID;
        response.email = TestConstants.EMAIL;
        return response;
    }

    public static VerifyAccountResponse getInvalidVerifyAccountResponse() {
        final VerifyAccountResponse response = new VerifyAccountResponse();
        response.phoneNumber = TestConstants.PHONE;
        response.token = new TwitterAuthToken(null, null);
        response.userId = 0;
        return response;
    }
}
