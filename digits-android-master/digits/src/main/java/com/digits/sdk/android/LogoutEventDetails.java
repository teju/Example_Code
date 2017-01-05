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

import android.support.annotation.NonNull;

/**
 * Event Details passed into the {@link com.digits.sdk.android.DigitsEventLogger#logout(LogoutEventDetails)}} method.
 */
public class LogoutEventDetails {
    @NonNull
    public final String language;

    @NonNull
    public final String country;

    public LogoutEventDetails(@NonNull String language, @NonNull String country) {
        this.language = language;
        this.country = country;
    }

    @Override
    public String toString() {
        final StringBuilder stringBuilder = new StringBuilder();
        stringBuilder.append(this.getClass().getName() + "{");
        if (language != null) stringBuilder.append("language='" + language + '\'');
        if (country != null) stringBuilder.append(",country='" + country + '\'');
        stringBuilder.append("}");
        return stringBuilder.toString();
    }
}
