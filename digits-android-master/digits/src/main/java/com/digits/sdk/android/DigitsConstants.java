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

class DigitsConstants {
    public final static Uri TWITTER_TOS = Uri.parse("https://twitter.com/tos");
    public final static Uri DIGITS_TOS = Uri.parse("https://www.digits.com");
    public final static int RESEND_TIMER_DURATION_MILLIS = 15000;
    public final static int MIN_CONFIRMATION_CODE_LENGTH = 6;
    static final String GUEST_AUTH_REFRESH_LOG_MESSAGE = "Refreshing guest auth token";
    static final String hyphen = "-";
}
