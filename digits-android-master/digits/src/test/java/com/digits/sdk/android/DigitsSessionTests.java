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

import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.TwitterAuthToken;

import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.net.HttpURLConnection;
import java.util.ArrayList;

import retrofit.client.Header;
import retrofit.client.Response;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;
import static org.junit.Assert.fail;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsSessionTests {

    private static final String ANY_HEADER = "header";
    private static final String ANY_DATA = "data";

    @Test
    public void testCreate_user() throws Exception {
        final ArrayList<Header> headers = new ArrayList<>();
        headers.add(new Header(ANY_HEADER, ANY_DATA));
        headers.add(new Header(DigitsSession.TOKEN_HEADER, TestConstants.TOKEN));
        headers.add(new Header(DigitsSession.SECRET_HEADER, TestConstants.SECRET));

        final Response response = new Response(TestConstants.TWITTER_URL,
                HttpURLConnection.HTTP_ACCEPTED, "", headers, null);
        final DigitsUser user = new DigitsUser(TestConstants.USER_ID,
                DigitsSession.DEFAULT_PHONE_NUMBER);
        final DigitsSession session = DigitsSession.create(new Result<>(user, response),
                TestConstants.PHONE);
        final DigitsSession newSession = new DigitsSession(new TwitterAuthToken(TestConstants.TOKEN,
                TestConstants.SECRET),
                TestConstants.USER_ID, TestConstants.PHONE, DigitsSession.DEFAULT_EMAIL);
        assertEquals(session, newSession);
    }

    @Test
    public void testCreate_digitsUser() throws Exception {
        final DigitsSessionResponse response = getNewDigitsSessionResponse();
        final DigitsSession session = DigitsSession.create(response, TestConstants.PHONE);
        final DigitsSession newSession = new DigitsSession(
                new TwitterAuthToken(TestConstants.TOKEN, TestConstants.SECRET),
                TestConstants.USER_ID, TestConstants.PHONE, DigitsSession.DEFAULT_EMAIL);
        assertEquals(session, newSession);
    }

    static DigitsSessionResponse getNewLoggedOutUser() {
        return getDigitsSessionResponse(TestConstants.TOKEN, TestConstants.SECRET,
                DigitsSession.LOGGED_OUT_USER_ID);
    }

    static DigitsSessionResponse getNewDigitsSessionResponse() {
        return getDigitsSessionResponse(TestConstants.TOKEN, TestConstants.SECRET,
                TestConstants.USER_ID);
    }

    private static DigitsSessionResponse getDigitsSessionResponse(String token, String secret,
                                                                  long userId) {
        final DigitsSessionResponse response = new DigitsSessionResponse();
        response.token = token;
        response.secret = secret;
        response.userId = userId;
        return response;
    }

    @Test
    public void testCreate_nullDigitsSessionResponse() throws Exception {
        try {
            final DigitsSessionResponse response = null;
            DigitsSession.create(response, TestConstants.PHONE);
            fail();
        } catch (NullPointerException ex) {
            assertEquals("result must not be null", ex.getMessage());
        }
    }

    @Test
    public void testCreate_nullResult() throws Exception {
        try {
            final Result result = null;
            DigitsSession.create(result, TestConstants.PHONE);
            fail();
        } catch (NullPointerException ex) {
            assertEquals("result must not be null", ex.getMessage());
        }
    }

    @Test
    public void testCreate_nullResultData() throws Exception {
        try {
            final Response response = new Response(TestConstants.TWITTER_URL,
                    HttpURLConnection.HTTP_ACCEPTED, DigitsSession.DEFAULT_PHONE_NUMBER,
                    new ArrayList<Header>(), null);
            DigitsSession.create(new Result<DigitsUser>(null, response), TestConstants.PHONE);
            fail();
        } catch (NullPointerException ex) {
            assertEquals("result.data must not be null", ex.getMessage());
        }
    }

    @Test
    public void testCreate_nullResultResponse() throws Exception {
        try {
            DigitsSession.create(new Result<>(new DigitsUser(TestConstants.USER_ID,
                    DigitsSession.DEFAULT_PHONE_NUMBER), null), TestConstants.PHONE);
            fail();
        } catch (NullPointerException ex) {
            assertEquals("result.response must not be null", ex.getMessage());
        }
    }

    @Test
    public void testIsLoggedOutUser_false() throws Exception {
        final DigitsSession session = DigitsSession.create(getNewDigitsSessionResponse(),
                TestConstants.PHONE);
        assertFalse(session.isLoggedOutUser());
    }

    @Test
    public void testIsLoggedOutUser_true() throws Exception {
        final DigitsSession session = DigitsSession.create(getNewLoggedOutUser(), TestConstants
                .PHONE);
        assertTrue(session.isLoggedOutUser());
    }

    @Test
    public void testCreate_fromVerifyAccountResponse() throws Exception {
        final DigitsSession digitsSession =
                DigitsSession.create(TestConstants.getVerifyAccountResponse());
        final DigitsSession expectedSession = new DigitsSession(new TwitterAuthToken
                (TestConstants.TOKEN, TestConstants.SECRET), TestConstants.USER_ID,
                TestConstants.PHONE, TestConstants.EMAIL);
        assertEquals(expectedSession, digitsSession);
    }

    @Test
    public void testCreate_nullVerifyAccountResponse() throws Exception {
        try {
            DigitsSession.create(null);
            fail();
        } catch (NullPointerException ex) {
            assertEquals("verifyAccountResponse must not be null", ex.getMessage());
        }
    }

    @Test
    public void testIsValidUser() throws Exception {
        final DigitsSession digitsSession =
                DigitsSession.create(TestConstants.getVerifyAccountResponse());
        assertTrue(digitsSession.isValidUser());
    }

    @Test
    public void testIsNotValidUser_withLoggedOutUserId() throws Exception {
        final DigitsSession session = DigitsSession.create(getNewLoggedOutUser(),
                TestConstants.PHONE);
        assertFalse(session.isValidUser());
    }

    @Test
    public void testIsNotValidUser_withUnknownUserId() throws Exception {
        final DigitsSession session = new DigitsSession(new TwitterAuthToken(TestConstants.TOKEN,
                TestConstants.SECRET), DigitsSession.UNKNOWN_USER_ID);
        assertFalse(session.isValidUser());
    }

    @Test
    public void testIsNotValidUser_withInvalidToken() throws Exception {
        final DigitsSession session = new DigitsSession(new TwitterAuthToken(null,
                null), TestConstants.USER_ID);
        final DigitsSession session2 = new DigitsSession(null, TestConstants.USER_ID);
        assertFalse(session.isValidUser());
        assertFalse(session2.isValidUser());
    }
}
