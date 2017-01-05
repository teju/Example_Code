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
import com.twitter.sdk.android.core.TwitterException;

/**
 * Callback used to indicate the completion of asynchronous request. Callbacks are executed on
 * the application's main (UI) thread.
 *
 * @param <T> expected response type
 * @see com.twitter.sdk.android.core.Callback
 */
public abstract class ContactsCallback<T> extends Callback<T> {

    /**
     * Called when call completes successfully.
     *
     * @param result the parsed result.
     * @see com.twitter.sdk.android.core.Callback#success
     */
    @Override
    public abstract void success(Result<T> result);

    /**
     * Unsuccessful call due to network failure, non-2XX status code, or unexpected
     * exception.
     *
     * @param exception the exception.
     * @see com.twitter.sdk.android.core.Callback#failure
     */
    @Override
    public abstract void failure(TwitterException exception);
}
