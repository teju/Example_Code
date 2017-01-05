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

public class PinCodeActivityTests extends DigitsActivityTests<PinCodeActivity> {

    private static final String REQUEST_ID = "1111";
    private static final String PHONE = "123456789";
    private static final String USER_ID = "6767";


    public PinCodeActivityTests() {
        super(PinCodeActivity.class);

    }

    @Override
    public void setUp() throws Exception {
        super.setUp();

        final Bundle bundle = getBundle();
        final Intent launchIntent = new Intent(getInstrumentation().getContext(),
                PinCodeActivity.class);
        launchIntent.putExtras(bundle);

        setActivityIntent(launchIntent);
    }

    public Bundle getBundle() {
        final Bundle bundle = super.getBundle();
        bundle.putString(DigitsClient.EXTRA_PHONE, PHONE);
        bundle.putString(DigitsClient.EXTRA_REQUEST_ID, REQUEST_ID);
        bundle.putString(DigitsClient.EXTRA_USER_ID, String.valueOf(USER_ID));
        return bundle;
    }

    public void testVerifyPinCode() {
        final Activity activity = getActivity();

        Spoon.screenshot(activity, "PinCodeActivity-startup");
    }
}
