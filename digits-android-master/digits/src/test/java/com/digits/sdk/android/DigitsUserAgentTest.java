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

import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import static org.junit.Assert.assertEquals;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsUserAgentTest {
    private static final String ANY_ANDROID_VERSION = "5.0";
    private static final String ANY_KIT_VERSION = "1.3.0";
    private static final String ANY_CONSUMER_KEY = "consumerKey";

    @Test
    public void testToString() throws Exception {
        final DigitsUserAgent userAgent =
          new DigitsUserAgent(ANY_KIT_VERSION, ANY_ANDROID_VERSION, ANY_CONSUMER_KEY);
        assertEquals(userAgentString(ANY_KIT_VERSION, ANY_ANDROID_VERSION, ANY_CONSUMER_KEY),
            userAgent.toString());
    }

    @Test
    public void testToString_nullVersions() throws Exception {
        final DigitsUserAgent userAgent = new DigitsUserAgent(null, null, null);
        assertEquals(userAgentString(null, null, null), userAgent.toString());
    }

    protected String userAgentString(String digitsVersion, String androidVersion,
                                     String consumerKey) {
        return "Digits/" + digitsVersion + " ( " + consumerKey
                + "; Android " + androidVersion + ")";
    }
}
