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

import android.os.Build;

class DigitsUserAgent {
    private final String digitsVersion;
    private final String androidVersion;
    private final String consumerKey;

    public static DigitsUserAgent create() {
        final Digits digits = Digits.getInstance();
        return new DigitsUserAgent(digits.getVersion(),
                Build.VERSION.RELEASE, digits.getAuthConfig().getConsumerKey());
    }

    DigitsUserAgent(String digitsVersion, String androidVersion, String consumerKey) {
        this.digitsVersion = digitsVersion;
        this.consumerKey = consumerKey;
        this.androidVersion = androidVersion;
    }

    public String toString() {
        return "Digits/" + digitsVersion + " ( " + consumerKey
                + "; Android " + androidVersion + ")";
    }
}
