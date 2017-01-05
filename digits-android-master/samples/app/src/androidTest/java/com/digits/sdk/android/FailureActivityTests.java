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
import android.test.ActivityInstrumentationTestCase2;

import com.digits.sdk.android.WeakAuthCallback;
import com.squareup.spoon.Spoon;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterApiErrorConstants;

import static android.support.test.espresso.Espresso.onView;
import static android.support.test.espresso.assertion.ViewAssertions.matches;
import static android.support.test.espresso.matcher.ViewMatchers.hasContentDescription;
import static android.support.test.espresso.matcher.ViewMatchers.isClickable;
import static android.support.test.espresso.matcher.ViewMatchers.isDisplayed;
import static android.support.test.espresso.matcher.ViewMatchers.isEnabled;
import static android.support.test.espresso.matcher.ViewMatchers.withText;

public class FailureActivityTests extends
        ActivityInstrumentationTestCase2<FailureActivity> {
    Intent launchIntent;

    public FailureActivityTests() {
        super(FailureActivity.class);
    }

    @Override
    public void setUp() throws Exception {
        super.setUp();

        final Bundle bundle = getBundle();

        launchIntent = new Intent(getInstrumentation().getContext(), FailureActivity.class);
        launchIntent.putExtras(bundle);

        setActivityIntent(launchIntent);
    }

    public Bundle getBundle() {
        final SessionManager<DigitsSession> sessionManager = Digits.getSessionManager();
        final LoginResultReceiver resultReceiver =
                new LoginResultReceiver((WeakAuthCallback) null, sessionManager, null);
        final Bundle bundle = new Bundle();
        final DigitsEventDetailsBuilder details = new DigitsEventDetailsBuilder()
                .withAuthStartTime(1L).withCurrentTime(2L).withLanguage("en");
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, resultReceiver);
        bundle.putSerializable(DigitsClient.EXTRA_FALLBACK_REASON, new DigitsException("",
                TwitterApiErrorConstants.DEVICE_REGISTRATION_INVALID_INPUT, new AuthConfig()));
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, details);

        return bundle;
    }

    public void testVerifyHeaderImage() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "FailureActivity-startup");

        onView(ViewMatchers.withId(R.id.dgts__header_image))
                .check(matches(isEnabled()))
                .check(matches(hasContentDescription()));
    }

    public void testVerifyDismissButton() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "FailureActivity-startup");

        onView(ViewMatchers.withId(R.id.dgts__dismiss_button))
                .check(matches(isEnabled()))
                .check(matches(isClickable()))
                .check(matches(withText(R.string.dgts__dismiss)));
    }

    public void testVerifyTryAnotherPhoneButton() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "FailureActivity-startup");

        onView(ViewMatchers.withId(R.id.dgts__try_another_phone))
                .check(matches(isEnabled()))
                .check(matches(isClickable()))
                .check(matches(withText(R.string.dgts__try_another_phone)));
    }

    public void testVerifyDefaultMessage() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "FailureActivity-startup");

        verifyErrorText(R.string.dgts__confirmation_error,
                R.string.dgts__confirmation_error_alternative);
    }

    private void verifyErrorText(int expectedTitleId, int expectedTextId) {
        onView(ViewMatchers.withId(R.id.dgts__error_title))
                .check(matches(isDisplayed()))
                .check(matches(withText(expectedTitleId)));
        onView(ViewMatchers.withId(R.id.dgts__error_text))
                .check(matches(isDisplayed()))
                .check(matches(withText(expectedTextId)));
    }
}
