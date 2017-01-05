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

import android.content.Context;

import com.twitter.sdk.android.core.Session;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.concurrent.ExecutorService;

import javax.net.ssl.SSLSocketFactory;


import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotSame;
import static org.junit.Assert.fail;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;


@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsApiClientManagerTests {

    final SandboxConfig sandboxConfig = new SandboxConfig();
    private MockDigitsApiClientManager digitsClient;
    private DigitsUserAgent digitsUserAgent;
    private TwitterCore twitterCore;
    private TwitterAuthConfig twitterAuthConfig;
    private SessionManager<DigitsSession> mockSessionManager;
    private DigitsSession activeSession;
    private DigitsApiClient activeClient;
    private ApiInterface service;
    private DigitsController controller;
    private AuthCallback callback;
    private ExecutorService executorService;
    private Context context;
    private SSLSocketFactory sslFactory;
    private DigitsRequestInterceptor interceptor;


    @Before
    public void setUp() throws Exception {
        context = mock(Context.class);
        digitsUserAgent = new DigitsUserAgent("digitsVersion", "androidVersion", "appName");
        interceptor = mock(DigitsRequestInterceptor.class);
        mockSessionManager = mock(SessionManager.class);
        activeSession = DigitsSession.create(TestConstants.LOGGED_OUT_USER, "");
        activeClient = mock(DigitsApiClient.class);
        when(activeClient.getSession()).thenReturn(activeSession);
        when(mockSessionManager.getActiveSession()).thenReturn(activeSession);
        sslFactory = mock(SSLSocketFactory.class);
        twitterCore = mock(TwitterCore.class);
        twitterAuthConfig = new TwitterAuthConfig(TestConstants.CONSUMER_KEY,
                TestConstants.CONSUMER_SECRET);
        service = mock(ApiInterface.class);
        controller = mock(DigitsController.class);
        callback = mock(AuthCallback.class);
        executorService = mock(ExecutorService.class);
        when(twitterCore.getContext()).thenReturn(mock(Context.class));

        when(twitterCore.getAuthConfig()).thenReturn(twitterAuthConfig);
        when(twitterCore.getSSLSocketFactory()).thenReturn(mock(SSLSocketFactory.class));
        when(controller.getErrors()).thenReturn(mock(ErrorCodes.class));


        digitsClient = new MockDigitsApiClientManager(
                twitterCore, executorService, mockSessionManager, activeClient, interceptor);
    }

    @Test
    public void testConstructor_nullTwitter() throws Exception {
        try {
            new MockDigitsApiClientManager(
                    null, executorService, mockSessionManager, activeClient, interceptor);
            fail("Expected IllegalArgumentException to be thrown");
        } catch (IllegalArgumentException e) {
            assertEquals("twitter must not be null", e.getMessage());
        }
    }

    @Test
    public void testConstructor_nullSessionManager() throws Exception {
        try {
            new MockDigitsApiClientManager(
                    twitterCore, executorService, null, activeClient, interceptor);
            fail("Expected IllegalArgumentException to be thrown");
        } catch (IllegalArgumentException e) {
            assertEquals("sessionManager must not be null", e.getMessage());
        }
    }

    @Test
    public void testGetApiClient_withSameSession() {
        final MockDigitsApiClientManager client =
                new MockDigitsApiClientManager(
                        twitterCore, executorService, mockSessionManager, activeClient);
        // Want to ensure it returns same client
        final DigitsApiClient activeClient = digitsClient.getApiClient();

        final Session active = activeClient.getSession();
        assertEquals(activeSession, active);
        assertEquals(activeClient, digitsClient.getApiClient());
    }

    @Test
    public void testGetApiClient_withDifferentSession() {
        final DigitsSession session2 = DigitsSession.create(TestConstants.LOGGED_OUT_USER, "");
        final MockDigitsApiClientManager client =
                new MockDigitsApiClientManager(twitterCore, executorService, mockSessionManager,
                        activeClient, interceptor);
        // Want to ensure it returns different client
        final DigitsApiClient firstClient = digitsClient.getApiClient();
        final Session active = firstClient.getSession();
        assertEquals(active, mockSessionManager.getActiveSession());
        assertEquals(activeClient, firstClient);

        mockSessionManager = mock(SessionManager.class);
        when(mockSessionManager.getActiveSession()).thenReturn(session2);
        activeClient = mock(DigitsApiClient.class);
        when(activeClient.getSession()).thenReturn(session2);
        assertNotSame(activeClient, firstClient);

        final DigitsApiClient newClient = digitsClient.getApiClient();
        final Session newActive = newClient.getSession();
        assertEquals(newActive, mockSessionManager.getActiveSession());
    }

    class MockDigitsApiClientManager extends DigitsApiClientManager {

        public MockDigitsApiClientManager(TwitterCore twitterCore,
                                          ExecutorService executorService,
                                          SessionManager<DigitsSession> sessionManager,
                                          DigitsApiClient apiClient,
                                          DigitsRequestInterceptor interceptor) {
            super(twitterCore, executorService, sessionManager, apiClient,
                    interceptor, sandboxConfig);
        }


        public MockDigitsApiClientManager(TwitterCore twitterCore,
                                    ExecutorService executorService,
                                    SessionManager<DigitsSession> sessionManager,
                                          DigitsApiClient apiClient) {
            super(twitterCore, executorService, sessionManager, apiClient, interceptor,
                    sandboxConfig);
        }

        @Override
        protected DigitsApiClient createNewClient() {
            return activeClient;
        }
    }
}
