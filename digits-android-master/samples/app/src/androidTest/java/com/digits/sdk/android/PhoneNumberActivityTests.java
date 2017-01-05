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

import static android.support.test.espresso.Espresso.onView;
import static android.support.test.espresso.action.ViewActions.click;
import static android.support.test.espresso.assertion.ViewAssertions.matches;
import static android.support.test.espresso.matcher.ViewMatchers.hasFocus;
import static android.support.test.espresso.matcher.ViewMatchers.withId;

public class PhoneNumberActivityTests extends DigitsActivityTests<PhoneNumberActivity> {

    public PhoneNumberActivityTests() {
        super(PhoneNumberActivity.class);
    }

    @Override
    public void setUp() throws Exception {
        super.setUp();

        final Bundle bundle = getBundle();
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, digitsEventDetailsBuilder);

        final Intent launchIntent = new Intent();
        launchIntent.putExtras(bundle);

        setActivityIntent(launchIntent);
    }

    public void testVerifyCountryCodeSpinner() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-startup");

        onView(withId(R.id.dgts__countryCode)).perform(click());
    }

    public void testVerifyEditText() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PhoneNumberActivity-startup");

        onView(withId(R.id.dgts__phoneNumberEditText)).check(matches(hasFocus()));
    }
}
