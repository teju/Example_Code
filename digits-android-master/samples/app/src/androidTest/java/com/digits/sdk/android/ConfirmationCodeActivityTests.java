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
import android.support.test.espresso.matcher.ViewMatchers;

import com.squareup.spoon.Spoon;

import static android.support.test.espresso.Espresso.onView;
import static android.support.test.espresso.assertion.ViewAssertions.matches;
import static android.support.test.espresso.matcher.ViewMatchers.hasFocus;

public class ConfirmationCodeActivityTests extends DigitsActivityTests<ConfirmationCodeActivity> {
    private static final String PHONE = "+15553334444";
    private AuthConfig config;

    public ConfirmationCodeActivityTests() {
        super(ConfirmationCodeActivity.class);
        config = new AuthConfig();
        config.isVoiceEnabled = true;
        config.isEmailEnabled = config.tosUpdate = true;
    }

    @Override
    public void setUp() throws Exception {
        super.setUp();

        final Bundle bundle = getBundle();
        bundle.putString(DigitsClient.EXTRA_PHONE, PHONE);
        bundle.putParcelable(DigitsClient.EXTRA_AUTH_CONFIG, config);

        final Intent launchIntent = new Intent(getInstrumentation().getContext(),
                ConfirmationCodeActivity.class);
        launchIntent.putExtras(bundle);

        setActivityIntent(launchIntent);
    }

    public void testVerifyEditText() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "ConfirmationCodeActivity-startup");

        onView(ViewMatchers.withId(R.id.dgts__confirmationEditText)).check(matches(hasFocus()));
    }
}
