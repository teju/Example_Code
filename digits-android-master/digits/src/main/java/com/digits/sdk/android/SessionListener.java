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

/**
 * Represents a listener for session changes.
 * Since these listeners are stored in a Map, you may want to implement {@link Object#equals
 * (Object)} and {@link Object#hashCode()}
 */
public interface SessionListener {
    /**
     * Notifies when the access token and secret, or the phone number for the current user have
     * changed.
     *
     * @param newSession New `Digits` session containing the updated values. The new phone number
     *                   in a normalized format
     */
    void changed(DigitsSession newSession);
}
