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
import android.content.Intent;
import android.os.Bundle;

import com.squareup.spoon.Spoon;

public class PhoneNumberActionBarActivityThemeTests extends
        DigitsActivityTests<PhoneNumberActionBarActivity> {

    public PhoneNumberActionBarActivityThemeTests() {
        super(PhoneNumberActionBarActivity.class);
    }

    @Override
    public void setUp() throws Exception {
        super.setUp();

        final Bundle bundle = getBundle();
        final Intent launchIntent = new Intent();
        launchIntent.putExtras(bundle);

        setActivityIntent(launchIntent);
    }

    @Override
    public void tearDown() throws Exception {
        super.tearDown();

        Digits.getInstance().setTheme(ThemeUtils.DEFAULT_THEME);
    }

    public void testVerifyAppCompatTheme() {
        Digits.getInstance().setTheme(R.style.Theme_AppCompat);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "phoneNumberActionBarActivity-Theme_AppCompat");
    }

    public void testVerifyAppCompatLightTheme() {
        Digits.getInstance().setTheme(R.style.Theme_AppCompat_Light);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "phoneNumberActionBarActivity-Theme_AppCompat_Light");
    }

    public void testVerifyAppCompatLightDarkActionBarTheme() {
        Digits.getInstance().setTheme(R.style.Theme_AppCompat_Light_DarkActionBar);
        final Activity activity = getActivity();

        Spoon.screenshot(activity,
                "phoneNumberActionBarActivity-Theme_AppCompat_Light_DarkActionBar");
    }
}
