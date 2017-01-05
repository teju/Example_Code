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

import java.lang.reflect.Field;

import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsSessionResponseTests {
    @Test
    public void testIsEmpty_true() throws Exception {
        final DigitsSessionResponse response = new DigitsSessionResponse();
        assertTrue(response.isEmpty());
    }

    @Test
    public void testIsEmpty_false() throws Exception {
        for (Field field : DigitsSessionResponse.class.getFields()) {
            final DigitsSessionResponse response = new DigitsSessionResponse();
            if (field.getName().equals("userId")) {
                field.setLong(response, TestConstants.USER_ID);
            } else {
                field.set(response, field.getType().newInstance());
            }
            assertFalse(response.isEmpty());
        }
    }
}
