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

import android.os.Bundle;

import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class BundleManagerTest {

    private static final String KEY_1 = "key1";
    private static final String KEY_2 = "key2";

    @Test
    public void testAssertContains_populatedBundle() throws Exception {
        final Bundle bundle = new Bundle();
        bundle.putString(KEY_1, "data");
        assertTrue(BundleManager.assertContains(bundle, KEY_1));
    }

    @Test
    public void testAssertContains_notFullyPopulated() throws Exception {
        final Bundle bundle = new Bundle();
        bundle.putString(KEY_1, "data");
        assertFalse(BundleManager.assertContains(bundle, KEY_1, KEY_2));
    }

    @Test
    public void testAssertContains_nullBundle() throws Exception {
        assertFalse(BundleManager.assertContains(null, KEY_1));
    }

    @Test
    public void testAssertContains_emptyBundle() throws Exception {
        assertFalse(BundleManager.assertContains(new Bundle(), KEY_1));
    }

    @SuppressWarnings("NullArgumentToVariableArgMethod")
    @Test
    public void testAssertContains_nullKey() throws Exception {
        assertFalse(BundleManager.assertContains(new Bundle(), (String) null));
    }
}
