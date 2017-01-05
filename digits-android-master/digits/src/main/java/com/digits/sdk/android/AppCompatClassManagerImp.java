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

class AppCompatClassManagerImp implements ActivityClassManager {
    @Override
    public Class<? extends Activity> getPhoneNumberActivity() {
        return PhoneNumberActionBarActivity.class;
    }

    @Override
    public Class<? extends Activity> getConfirmationActivity() {
        return ConfirmationCodeActionBarActivity.class;
    }

    @Override
    public Class<? extends Activity> getLoginCodeActivity() {
        return LoginCodeActionBarActivity.class;
    }

    @Override
    public Class<? extends Activity> getFailureActivity() {
        return FailureActionBarActivity.class;
    }

    @Override
    public Class<? extends Activity> getContactsActivity() {
        return ContactsActionBarActivity.class;
    }

    @Override
    public Class<? extends Activity> getPinCodeActivity() {
        return PinCodeActionBarActivity.class;
    }

    @Override
    public Class<? extends Activity> getEmailRequestActivity() {
        return EmailRequestActionBarActivity.class;
    }
}
