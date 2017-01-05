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

import io.fabric.sdk.android.services.network.HttpMethod;

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterAuthToken;
import com.twitter.sdk.android.core.internal.oauth.OAuth1aHeaders;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.HashMap;

import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsOAuthSigningTest {
    private static final String ANY_AUTH_HEADER = "Digits Authority!";
    private static final String SESSION_ID = "12345";
    private static final String SESSION_KEY = "session_id";

    private OAuth1aHeaders oAuthHeaders;
    private TwitterAuthConfig config;
    private TwitterAuthToken token;
    private DigitsOAuthSigning oAuthSigning;

    @Before
    public void setUp() throws Exception {
        oAuthHeaders = mock(OAuth1aHeaders.class);
        config = mock(TwitterAuthConfig.class);
        token = mock(TwitterAuthToken.class);
        when(oAuthHeaders.getAuthorizationHeader(config, token, null, HttpMethod.GET.name(),
                DigitsOAuthSigning.VERIFY_CREDENTIALS_URL, null)).thenReturn(ANY_AUTH_HEADER);
        oAuthSigning = new DigitsOAuthSigning(config, token, oAuthHeaders);

    }

    @Test
    public void testGetOAuthEchoHeadersForVerifyCredentials() throws Exception {
        oAuthSigning.getOAuthEchoHeadersForVerifyCredentials();

        verify(oAuthHeaders).getOAuthEchoHeaders(config, token, null, HttpMethod.GET.name(),
                DigitsOAuthSigning.VERIFY_CREDENTIALS_URL, null);
    }

    @Test
    public void testGetOAuthEchoHeadersForVerifyCredentials_withPostParams() throws Exception {
        final HashMap<String, String> optParams = new HashMap<>();
        optParams.put(SESSION_KEY, SESSION_ID);
        oAuthSigning.getOAuthEchoHeadersForVerifyCredentials(optParams);

        verify(oAuthHeaders).getOAuthEchoHeaders(config, token, null, HttpMethod.GET.name(),
                DigitsOAuthSigning.VERIFY_CREDENTIALS_URL + "?" + SESSION_KEY + "=" + SESSION_ID,
                null);
    }
}
