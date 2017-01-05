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

import android.annotation.SuppressLint;
import android.annotation.TargetApi;
import android.app.Activity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;

import com.squareup.spoon.Spoon;

import android.support.test.filters.SdkSuppress;

public class PhoneNumberActivityThemeTests extends
        DigitsActivityTests<PhoneNumberActivity> {

    public PhoneNumberActivityThemeTests() {
        super(PhoneNumberActivity.class);
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

    @SdkSuppress(minSdkVersion = Build.VERSION_CODES.LOLLIPOP)
    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public void testVerifyMaterialDarkTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Material);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Material");
    }

    @SdkSuppress(minSdkVersion = Build.VERSION_CODES.LOLLIPOP)
    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public void testVerifyMaterialLightTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Material_Light);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Material_Light");
    }

    @SdkSuppress(minSdkVersion = Build.VERSION_CODES.LOLLIPOP)
    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public void testVerifyMaterialLightDarkActionBarTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Material_Light_DarkActionBar);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Material_Light_DarkActionBar");
    }

    @SdkSuppress(minSdkVersion = Build.VERSION_CODES.ICE_CREAM_SANDWICH)
    @TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
    public void testVerifyHoloDarkTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Holo);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Holo");
    }

    @SdkSuppress(minSdkVersion = Build.VERSION_CODES.ICE_CREAM_SANDWICH)
    @TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
    public void testVerifyHoloLightTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Holo_Light);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Holo_Light");
    }

    @SdkSuppress(minSdkVersion = Build.VERSION_CODES.ICE_CREAM_SANDWICH)
    @TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
    public void testVerifyHoloLightDarkActionBarTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Holo_Light_DarkActionBar);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Holo_Light_DarkActionBar");
    }

    public void testVerifyDarkTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme");
    }

    public void testVerifyLightTheme() {
        Digits.getInstance().setTheme(android.R.style.Theme_Light);
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-Theme_Light");
    }
}
