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
import com.twitter.sdk.android.core.TwitterException;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import retrofit.RetrofitError;

import static org.mockito.Matchers.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;


@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class ContactsCallbackTests {

    private ContactsCallback contactsCallback;

    @Before
    public void setUp() throws Exception {
        contactsCallback = mock(ContactsCallback.class);
    }

    @Test
    public void testFailure() throws Exception {
        contactsCallback.failure(RetrofitError.unexpectedError("", new NullPointerException("")));
        verify(contactsCallback).failure(any(TwitterException.class));
    }

    @Test
    public void testSuccess() throws Exception {
        contactsCallback.success(null, null);
        verify(contactsCallback).success(any(Result.class));
    }
}
