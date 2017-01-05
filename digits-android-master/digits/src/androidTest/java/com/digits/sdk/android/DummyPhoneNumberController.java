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

import android.content.Context;
import android.os.ResultReceiver;
import android.widget.EditText;

import com.twitter.sdk.android.core.SessionManager;

import static org.mockito.Mockito.mock;

public class DummyPhoneNumberController extends PhoneNumberController {
    LoginOrSignupComposer loginOrSignupComposer;

    DummyPhoneNumberController(ResultReceiver resultReceiver, StateButton stateButton,
                               EditText phoneEditText, CountryListSpinner countryCodeSpinner,
                               DigitsClient client, ErrorCodes errors,
                               ActivityClassManager activityClassManager,
                               SessionManager<DigitsSession> sessionManager,
                               TosView tosView, DigitsEventCollector digitsEventCollector,
                               boolean emailCollection, DigitsEventDetailsBuilder builder) {
        super(resultReceiver, stateButton, phoneEditText, countryCodeSpinner, client, errors,
                activityClassManager, sessionManager, tosView, digitsEventCollector,
                emailCollection, builder);
        this.loginOrSignupComposer = mock(DummyLoginOrSignupComposer.class);
    }

    @Override
    LoginOrSignupComposer createCompositeCallback(final Context context, final String phoneNumber) {
        return this.loginOrSignupComposer;
    }

    protected abstract class DummyLoginOrSignupComposer extends LoginOrSignupComposer {

        DummyLoginOrSignupComposer(Context context, DigitsClient digitsClient,
                                   SessionManager<DigitsSession> sessionManager,
                                   String phoneNumber, Verification verificationType,
                                   boolean emailCollection, ResultReceiver resultReceiver,
                                   ActivityClassManager activityClassManager,
                                   DigitsEventDetailsBuilder metricsBuilder) {
            super(context, digitsClient, sessionManager, phoneNumber, verificationType,
                    emailCollection,
                    resultReceiver, activityClassManager, metricsBuilder);
        }
    }
}
