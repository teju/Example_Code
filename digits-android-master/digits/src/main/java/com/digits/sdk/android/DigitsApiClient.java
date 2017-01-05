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

import com.twitter.sdk.android.core.AuthenticatedClient;
import com.twitter.sdk.android.core.TwitterCore;

import java.util.concurrent.ExecutorService;

import javax.net.ssl.SSLSocketFactory;

import retrofit.MockRestAdapter;
import retrofit.RestAdapter;
import retrofit.android.MainThreadExecutor;

class DigitsApiClient {
    private final ApiInterface service;
    private final DigitsSession session;
    private static final String NULL_SESSION_ERROR_LOG =
            "Attempting to connect to Digits API with null session. " +
                    "Please re-authenticate and try again";

    DigitsApiClient(DigitsSession session, TwitterCore twitterCore, SSLSocketFactory sslFactory,
                    ExecutorService executorService, DigitsRequestInterceptor interceptor) {
        this.session = session;
        final RestAdapter adapter = createAdapter(executorService, twitterCore,
                sslFactory, interceptor);
        this.service = adapter.create(ApiInterface.class);
    }

    DigitsApiClient(DigitsSession session, TwitterCore twitterCore, SSLSocketFactory sslFactory,
                    ExecutorService executorService, DigitsRequestInterceptor interceptor,
                    ApiInterface mockInterface) {
        if (mockInterface == null) {
            throw new IllegalArgumentException("mock interface cannot be null!");
        }

        this.session = session;
        final RestAdapter adapter = createAdapter(executorService, twitterCore,
                sslFactory, interceptor);
        this.service = MockRestAdapter.from(adapter)
                .create(ApiInterface.class, mockInterface);
    }

    public DigitsSession getSession() {
        return session;
    }

    public ApiInterface getService() {
        return service;
    }

    protected RestAdapter createAdapter(ExecutorService executorService,
                                                       TwitterCore twitterCore,
                                        SSLSocketFactory sslSocketFactory,
                                                       DigitsRequestInterceptor interceptor) {
        return new RestAdapter.Builder()
                .setEndpoint(new DigitsApi().getBaseHostUrl())
                .setRequestInterceptor(interceptor)
                .setExecutors(executorService,
                        new MainThreadExecutor())
                .setClient(new AuthenticatedClient(twitterCore.getAuthConfig(),
                        session, sslSocketFactory)).build();
    }

}
