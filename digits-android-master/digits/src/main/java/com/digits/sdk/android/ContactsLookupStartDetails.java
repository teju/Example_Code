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
 * ContactsLookupStartDetails contains information passed into the
 * DigitsEventLogger implementations.
 */
@Beta(Beta.Feature.Analytics)
public class ContactsLookupStartDetails {
    @NonNull
    public final boolean hasCursor;

    public ContactsLookupStartDetails(String cursor) {
        this.hasCursor = cursor != null && !cursor.isEmpty();
    }

    public ContactsLookupStartDetails(@NonNull boolean hasCursor) {
        this.hasCursor = hasCursor;
    }

    @Override
    public String toString() {
        final StringBuilder stringBuilder = new StringBuilder();
        stringBuilder.append(this.getClass().getName() + "{");
        stringBuilder.append("hasCursor='" + hasCursor + '\'');
        stringBuilder.append("}");
        return stringBuilder.toString();
    }
}
