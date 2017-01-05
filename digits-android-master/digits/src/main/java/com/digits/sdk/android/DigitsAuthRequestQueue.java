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
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.internal.AuthRequestQueue;
import com.twitter.sdk.android.core.internal.SessionProvider;

/**
 * Queues requests until a {@link ApiInterface} with a session is ready. Gets an active session
 * from the {@link SessionProvider} or requests {@link SessionProvider} perform authentication.
 */
class DigitsAuthRequestQueue extends AuthRequestQueue {
    final DigitsApiClientManager apiClientManager;

    DigitsAuthRequestQueue(DigitsApiClientManager apiClientManager,
                           SessionProvider sessionProvider) {
        super(sessionProvider);

        this.apiClientManager = apiClientManager;
    }

    protected synchronized boolean addClientRequest(final Callback<ApiInterface> callback) {
        return addRequest(new Callback<Session>() {
            @Override
            public void success(Result<Session> result) {
                callback.success(new Result<>(
                        apiClientManager.getApiClient().getService(), null));
            }

            @Override
            public void failure(TwitterException exception) {
                callback.failure(exception);
            }
        });
    }

}
