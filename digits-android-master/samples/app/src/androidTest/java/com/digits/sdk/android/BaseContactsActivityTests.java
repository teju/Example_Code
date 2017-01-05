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
import android.support.test.espresso.matcher.ViewMatchers;
import android.test.ActivityInstrumentationTestCase2;

import com.squareup.spoon.Spoon;

import static android.support.test.espresso.Espresso.onView;
import static android.support.test.espresso.assertion.ViewAssertions.matches;
import static android.support.test.espresso.matcher.ViewMatchers.hasContentDescription;
import static android.support.test.espresso.matcher.ViewMatchers.isClickable;
import static android.support.test.espresso.matcher.ViewMatchers.isDisplayed;
import static android.support.test.espresso.matcher.ViewMatchers.isEnabled;

public abstract class BaseContactsActivityTests<T extends Activity> extends
        ActivityInstrumentationTestCase2<T> {

    BaseContactsActivityTests(Class<T> activityClass) {
        super(activityClass);
    }

    @Override
    public void setUp() throws Exception {
        super.setUp();

        final Intent launchIntent = new Intent(getInstrumentation().getContext(),
                ContactsActivity.class);

        setActivityIntent(launchIntent);
    }

    public void testVerifyView() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "ContactsActivity-startup");

        onView(ViewMatchers.withId(R.id.dgts__okay))
                .check(matches(ViewMatchers.withText(R.string.dgts__okay)))
                .check(matches(isDisplayed()))
                .check(matches(isEnabled()))
                .check(matches(isClickable()));

        onView(ViewMatchers.withId(R.id.dgts__not_now))
                .check(matches(ViewMatchers.withText(R.string.dgts__not_now)))
                .check(matches(isDisplayed()))
                .check(matches(isEnabled()))
                .check(matches(isClickable()));

        onView(ViewMatchers.withId(R.id.dgts__upload_contacts))
                .check(matches(ViewMatchers.withText(getFormattedDescription(activity))))
                .check(matches(isDisplayed()));

        onView(ViewMatchers.withId(R.id.dgts__find_your_friends))
                .check(matches(ViewMatchers.withText(R.string.dgts__find_your_friends)))
                .check(matches(isDisplayed()));

        onView(ViewMatchers.withId(R.id.dgts__header_image))
                .check(matches(isDisplayed()))
                .check(matches(hasContentDescription()));
    }

    String getFormattedDescription(Activity activity) {
        return activity.getString(R.string.dgts__upload_contacts, getApplicationName(activity));
    }

    String getApplicationName(Activity activity) {
        return activity.getApplicationInfo().loadLabel(activity.getPackageManager()).toString();
    }
}
