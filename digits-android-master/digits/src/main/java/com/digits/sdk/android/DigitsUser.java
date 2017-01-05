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

import com.google.gson.annotations.SerializedName;

/**
 * Digits user.
 */
public class DigitsUser {
    /**
     * The integer representation of the unique identifier for this DigitsUser. This number is
     * greater than 53 bits and some programming languages may have difficulty/silent defects in
     * interpreting it. Using a signed 64 bit integer for storing this identifier is safe. Use
     * id_str for fetching the identifier to stay on the safe side. See Twitter IDs, JSON and
     * Snowflake.
     */
    @SerializedName("id")
    public final long id;

    /**
     * The string representation of the unique identifier for this DigitsUser. Implementations
     * should use this rather than the large, possibly un-consumable integer in id.
     */
    @SerializedName("id_str")
    public final String idStr;

    public DigitsUser(long id, String idStr) {
        this.id = id;
        this.idStr = idStr;
    }
}
