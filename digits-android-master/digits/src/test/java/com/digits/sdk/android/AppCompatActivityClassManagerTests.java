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

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import static org.junit.Assert.assertEquals;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class AppCompatActivityClassManagerTests  {
    private AppCompatClassManagerImp activityClassManager;

    @Before
    public void setUp() throws Exception {

        activityClassManager = new AppCompatClassManagerImp();
    }

    @Test
    public void testGetPhoneNumberActivity() throws Exception {
        assertEquals(PhoneNumberActionBarActivity.class,
                activityClassManager.getPhoneNumberActivity());
    }

    @Test
    public void testGetConfirmationActivity() throws Exception {
        assertEquals(ConfirmationCodeActionBarActivity.class,
                activityClassManager.getConfirmationActivity());
    }

    @Test
    public void testGetLoginCodeActivity() throws Exception {
        assertEquals(LoginCodeActionBarActivity.class, activityClassManager.getLoginCodeActivity());
    }

    @Test
    public void testFailureActivity() throws Exception {
        assertEquals(FailureActionBarActivity.class,
                activityClassManager.getFailureActivity());
    }

    @Test
    public void testContactsActivity() throws Exception {
        assertEquals(ContactsActionBarActivity.class, activityClassManager.getContactsActivity());
    }

    @Test
    public void testPinCodeActivity() throws Exception {
        assertEquals(PinCodeActionBarActivity.class,
                activityClassManager.getPinCodeActivity());
    }

    @Test
    public void testEmailRequestActivity() throws Exception {
        assertEquals(EmailRequestActionBarActivity.class,
                activityClassManager.getEmailRequestActivity());
    }
}
