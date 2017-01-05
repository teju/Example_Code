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

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.concurrent.ExecutorService;

import javax.net.ssl.SSLSocketFactory;

import static org.junit.Assert.assertTrue;
import static org.mockito.Mockito.mock;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsApiClientTests {
    private TwitterAuthConfig authConfig;
    private TwitterCore twitterCore;
    private DigitsSession guestSession;
    private DigitsApiClient digitsApiClient;
    private ActivityClassManagerFactory activityClassManagerFactory;
    private ContactsPreferenceManager prefManager;

    @Before
    public void setUp() throws Exception {
        authConfig = new TwitterAuthConfig(TestConstants.CONSUMER_SECRET,
                TestConstants.CONSUMER_KEY);
        twitterCore = new TwitterCore(authConfig);
        guestSession = DigitsSession.create(DigitsSessionTests.getNewLoggedOutUser(),
                TestConstants.PHONE);
        activityClassManagerFactory = new ActivityClassManagerFactory();
        prefManager = mock(ContactsPreferenceManager.class);
        digitsApiClient = new DigitsApiClient(guestSession, twitterCore,
                mock(SSLSocketFactory.class),
                mock(ExecutorService.class),
                mock(DigitsRequestInterceptor.class),
                mock(ApiInterface.class));
    }

    @Test
    public void testGetSdkService() throws Exception {
        final ApiInterface sdkService = digitsApiClient.getService();
        final ApiInterface newSdkService = digitsApiClient.getService();
        assertTrue(sdkService == newSdkService);
    }
}

