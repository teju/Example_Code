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

import android.net.Uri;

import io.fabric.sdk.android.services.network.HttpMethod;

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterAuthToken;
import com.twitter.sdk.android.core.internal.oauth.OAuth1aHeaders;

import java.util.Map;
/**
 * Provides helper methods to generate OAuth Headers for Digits
 **/
public class DigitsOAuthSigning {
    protected static final String VERIFY_CREDENTIALS_URL = DigitsApi.BASE_HOST_URL + "/1" +
            ".1/sdk/account.json";
    protected final TwitterAuthConfig authConfig;
    protected final TwitterAuthToken authToken;
    protected final OAuth1aHeaders oAuth1aHeaders;

    /**
     * Constructs OAuthSigning with TwitterAuthConfig and TwitterAuthToken
     *
     * @param authConfig The auth config.
     * @param authToken The auth token to use to sign the request.
     */
    public DigitsOAuthSigning(TwitterAuthConfig authConfig, TwitterAuthToken authToken) {
        this(authConfig, authToken, new OAuth1aHeaders());
    }

    DigitsOAuthSigning(TwitterAuthConfig authConfig, TwitterAuthToken authToken,
            OAuth1aHeaders oAuth1aHeaders) {
        if (authConfig == null) {
            throw new IllegalArgumentException("authConfig must not be null");
        }
        if (authToken == null) {
            throw new IllegalArgumentException("authToken must not be null");
        }

        this.authConfig = authConfig;
        this.authToken = authToken;
        this.oAuth1aHeaders = oAuth1aHeaders;
    }

    /**
     * Returns OAuth Echo header for <a href="https://api.digits.com/1.1/sdk/account.json">/sdk/account.json</a>
     * endpoint.
     *
     * @return A map of OAuth Echo headers
     */
    public Map<String, String> getOAuthEchoHeadersForVerifyCredentials() {
        return oAuth1aHeaders.getOAuthEchoHeaders(authConfig, authToken, null,
                HttpMethod.GET.name(), VERIFY_CREDENTIALS_URL, null);
    }

    /**
     * Returns OAuth Echo header for <a href="https://api.digits.com/1.1/sdk/account.json">/sdk/account.json</a>
     * endpoint.
     *
     * @param optParams optional custom params to add the request URL. These extra parameters help
     * as a Nonce between the client's session and the Echo header to validate that this header
     * cannot be reused by another client's session.
     * @return A map of OAuth Echo headers
     */
    public Map<String, String> getOAuthEchoHeadersForVerifyCredentials(Map<String, String>
            optParams) {
        return oAuth1aHeaders.getOAuthEchoHeaders(authConfig, authToken, null,
                HttpMethod.GET.name(), createProviderUrlWithQueryParams(optParams), null);
    }

    private String createProviderUrlWithQueryParams(Map<String, String> optParams) {
        if (optParams == null) {
            return VERIFY_CREDENTIALS_URL;
        }
        final Uri.Builder uriHeader = Uri.parse(VERIFY_CREDENTIALS_URL).buildUpon();
        for (String key : optParams.keySet()) {
            uriHeader.appendQueryParameter(key, optParams.get(key));
        }
        return uriHeader.toString();
    }
}

