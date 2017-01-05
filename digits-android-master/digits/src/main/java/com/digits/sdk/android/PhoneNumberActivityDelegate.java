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
import android.os.Bundle;
import android.os.ResultReceiver;
import android.text.TextUtils;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import io.fabric.sdk.android.services.common.CommonUtils;

class PhoneNumberActivityDelegate extends DigitsActivityDelegateImpl implements TosView {
    protected final static String CANCELLATION_EXCEPTION_MESSAGE =
            "Authentication canceled by user";
    private final DigitsEventCollector digitsEventCollector;
    private Activity activity;

    CountryListSpinner countryCodeSpinner;
    StateButton sendButton;
    EditText phoneEditText;
    TextView termsTextView;
    PhoneNumberController controller;
    TosFormatHelper tosFormatHelper;

    public PhoneNumberActivityDelegate(DigitsEventCollector digitsEventCollector) {
        this.digitsEventCollector = digitsEventCollector;
    }

    @Override
    public int getLayoutId() {
        return R.layout.dgts__activity_phone_number;
    }

    @Override
    public boolean isValid(Bundle bundle) {
        final boolean isValidBundle = BundleManager.assertContains(bundle,
                DigitsClient.EXTRA_RESULT_RECEIVER, DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);
        if (isValidBundle) {
            final DigitsEventDetailsBuilder eventDetailsBuilder =
                    bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);

            return eventDetailsBuilder.authStartTime != null
                    && eventDetailsBuilder.language != null;
        }
        return false;
    }

    @Override
    public void init(Activity activity, Bundle bundle) {
        this.activity = activity;
        eventDetailsBuilder = bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);
        countryCodeSpinner = (CountryListSpinner) activity.findViewById(R.id.dgts__countryCode);
        sendButton = (StateButton) activity.findViewById(R.id.dgts__sendCodeButton);
        phoneEditText = (EditText) activity.findViewById(R.id.dgts__phoneNumberEditText);
        termsTextView = (TextView) activity.findViewById(R.id.dgts__termsText);
        controller = initController(bundle);
        tosFormatHelper = new TosFormatHelper(activity);

        setUpEditText(activity, controller, phoneEditText);

        setUpSendButton(activity, controller, sendButton);

        setUpTermsText(activity, controller, termsTextView);

        setUpCountrySpinner(countryCodeSpinner);

        setupPhoneNumber(SimManager.createSimManager(activity), bundle);

        CommonUtils.openKeyboard(activity, phoneEditText);
    }

    void setupPhoneNumber(SimManager simManager, Bundle bundle) {
        final String bundledPhoneNumber = bundle.getString(DigitsClient.EXTRA_PHONE);
        final PhoneNumber normalizedPhoneNumber;

        if (TextUtils.isEmpty(bundledPhoneNumber)) {
            normalizedPhoneNumber = PhoneNumberUtils.getPhoneNumber("", simManager);
        } else {
            normalizedPhoneNumber = PhoneNumberUtils.getPhoneNumber(bundledPhoneNumber, simManager);
        }

        controller.setPhoneNumber(normalizedPhoneNumber);
        controller.setCountryCode(normalizedPhoneNumber);
    }

    PhoneNumberController initController(Bundle bundle) {
        return new PhoneNumberController(bundle
                .<ResultReceiver>getParcelable(DigitsClient.EXTRA_RESULT_RECEIVER), sendButton,
                phoneEditText, countryCodeSpinner, this, digitsEventCollector, bundle.getBoolean
                (DigitsClient.EXTRA_EMAIL), eventDetailsBuilder);
    }

    @Override
    public void setUpTermsText(Activity activity, DigitsController controller, TextView termsText) {
        termsText.setText(tosFormatHelper.getFormattedTerms(R.string.dgts__terms_text));
        super.setUpTermsText(activity, controller, termsText);
    }

    protected void setUpCountrySpinner(CountryListSpinner countryCodeSpinner) {
        countryCodeSpinner.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                digitsEventCollector.countryCodeClickOnPhoneScreen();
                controller.clearError();
            }
        });
    }

    @Override
    public void onResume() {
        final DigitsEventDetailsBuilder deb = eventDetailsBuilder
                .withCurrentTime(System.currentTimeMillis());
        digitsEventCollector.phoneScreenImpression(deb.build());
        controller.onResume();
    }

    @Override
    public void setText(int resourceId) {
        termsTextView.setText(tosFormatHelper.getFormattedTerms(resourceId));
    }

    protected void onBackPressed() {
        controller.sendFailure(CANCELLATION_EXCEPTION_MESSAGE);
    }
}
