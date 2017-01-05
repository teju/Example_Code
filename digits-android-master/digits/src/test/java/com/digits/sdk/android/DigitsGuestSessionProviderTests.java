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

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.Session;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterApiException;
import com.twitter.sdk.android.core.internal.oauth.OAuth2Service;
import com.twitter.sdk.android.core.internal.oauth.OAuth2Token;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.net.HttpURLConnection;
import java.util.ArrayList;
import java.util.List;

import retrofit.client.Header;
import retrofit.client.Response;

import static org.junit.Assert.assertEquals;
import static org.mockito.Matchers.any;
import static org.mockito.Matchers.eq;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoMoreInteractions;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsGuestSessionProviderTests {
    SessionManager<DigitsSession> sessionManager;
    Callback<Session> sessionCallback;
    List<SessionManager<? extends Session>> sessionManagers;

    @Before
    public void setUp() throws Exception {
        sessionManager = mock(SessionManager.class);
        sessionCallback = mock(Callback.class);

        sessionManagers = new ArrayList<>(1);
        sessionManagers.add(sessionManager);
    }

    @Test
    public void testRequestAuth() {
        final OAuth2Service oAuth2Service = mock(OAuth2Service.class);
        final DigitsGuestSessionProvider provider =
                new DigitsGuestSessionProvider(sessionManager, sessionManagers, oAuth2Service);
        provider.requestAuth(sessionCallback);

        final ArgumentCaptor<DigitsGuestSessionProvider.GuestAuthCallback> captor =
                ArgumentCaptor.forClass(DigitsGuestSessionProvider.GuestAuthCallback.class);
        verify(oAuth2Service).requestGuestAuthToken(captor.capture());
        final DigitsGuestSessionProvider.GuestAuthCallback callback = captor.getValue();
        assertEquals(sessionCallback, callback.callback);
        assertEquals(sessionManager, callback.sessionManager);
    }

    @Test
    public void testGuestAuthCallback_success() throws Exception {
        final Response response = new Response(TestConstants.TWITTER_URL,
                HttpURLConnection.HTTP_ACCEPTED, "", new ArrayList<Header>(), null);
        final DigitsGuestSessionProvider.GuestAuthCallback guestAuthCallback =
                new DigitsGuestSessionProvider.GuestAuthCallback(sessionManager, sessionCallback);
        guestAuthCallback.success(mock(OAuth2Token.class), response);

        verify(sessionManager)
                .setSession(eq(DigitsSession.LOGGED_OUT_USER_ID), any(DigitsSession.class));
        verify(sessionCallback).success(any(Result.class));
    }

    @Test
    public void testGuestAuthCallback_failure() throws Exception {
        final TwitterApiException exception = mock(TwitterApiException.class);
        final DigitsGuestSessionProvider.GuestAuthCallback guestAuthCallback =
                new DigitsGuestSessionProvider.GuestAuthCallback(sessionManager, sessionCallback);
        guestAuthCallback.failure(exception);

        verifyNoMoreInteractions(sessionManager);
        verify(sessionCallback).failure(exception);
    }
}
