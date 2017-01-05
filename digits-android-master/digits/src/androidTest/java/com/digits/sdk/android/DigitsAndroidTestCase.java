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

import android.app.Activity;

import java.lang.reflect.Field;

import io.fabric.sdk.android.FabricAndroidTestCase;

import static org.mockito.Mockito.verifyZeroInteractions;

public class DigitsAndroidTestCase extends FabricAndroidTestCase {
    static final String ERROR_MESSAGE = "random error";
    static final DigitsException EXCEPTION = new DigitsException(ERROR_MESSAGE);
    static final UnrecoverableException UNRECOVERABLE_EXCEPTION =
            new UnrecoverableException(ERROR_MESSAGE);
    static final int ANY_REQUEST = 1010;
    static final int ANY_RESULT = Activity.RESULT_OK;
    static final long ANY_LONG = 11231L;

    protected static final String TWITTER_URL = "http://twitter.com";
    protected static final String US_COUNTRY_CODE = "1";
    protected static final String US_ISO2 = "us";
    protected static final String LANG = "en";

    protected void verifyNoInteractions(Object... objects) {
        for (Object object : objects) {
            verifyZeroInteractions(object);
        }
    }

    /**
     * Verifies resultCode is set on the activity by using reflection. This is handy since {@link
     * Activity#setResult(int)} is final method and can't be mock.
     *
     * @throws NoSuchFieldException
     * @throws IllegalAccessException
     */
    protected void verifyResultCode(Activity activity, int resultCode) throws NoSuchFieldException,
            IllegalAccessException {
        final Field field = Activity.class.getDeclaredField("mResultCode");
        field.setAccessible(true);
        final int actualResultCode = (Integer) field.get(activity);
        assertEquals(resultCode, actualResultCode);
    }
}
