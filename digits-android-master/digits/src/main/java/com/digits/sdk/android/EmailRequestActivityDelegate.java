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
import android.text.InputType;
import android.widget.EditText;
import android.widget.TextView;

import io.fabric.sdk.android.services.common.CommonUtils;

public class EmailRequestActivityDelegate extends DigitsActivityDelegateImpl {
    EditText editText;
    StateButton stateButton;
    TextView termsText;
    DigitsController controller;
    Activity activity;
    DigitsEventCollector digitsEventCollector;
    TextView titleText;
    TosFormatHelper tosFormatHelper;

    EmailRequestActivityDelegate(DigitsEventCollector digitsEventCollector) {
        this.digitsEventCollector = digitsEventCollector;
    }

    @Override
    public int getLayoutId() {
        return R.layout.dgts__activity_email;
    }

    @Override
    public boolean isValid(Bundle bundle) {
        final boolean isValidBundle =
                BundleManager.assertContains(bundle, DigitsClient.EXTRA_RESULT_RECEIVER,
                DigitsClient.EXTRA_PHONE);

        if (isValidBundle){
            final DigitsEventDetailsBuilder digitsEventDetailsBuilder =
                    bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);

            return (digitsEventDetailsBuilder.authStartTime != null)
                    && (digitsEventDetailsBuilder.language != null)
                    && (digitsEventDetailsBuilder.country != null);
        }
        return false;
    }

    @Override
    public void init(Activity activity, Bundle bundle) {
        this.activity = activity;
        eventDetailsBuilder = bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);
        titleText = (TextView) activity.findViewById(R.id.dgts__titleText);
        editText = (EditText) activity.findViewById(R.id.dgts__confirmationEditText);
        stateButton = (StateButton) activity.findViewById(R.id.dgts__createAccount);
        termsText = (TextView) activity.findViewById(R.id.dgts__termsTextCreateAccount);

        controller = initController(bundle);
        tosFormatHelper = new TosFormatHelper(activity);

        editText.setHint(R.string.dgts__email_request_edit_hint);
        titleText.setText(R.string.dgts__email_request_title);

        setUpEditText(activity, controller, editText);
        setUpSendButton(activity, controller, stateButton);
        setUpTermsText(activity, controller, termsText);

        CommonUtils.openKeyboard(activity, editText);
    }

    @Override
    public void setUpEditText(final Activity activity, final DigitsController controller,
                              EditText editText) {
        editText.setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
        super.setUpEditText(activity, controller, editText);
    }

    @Override
    public void setUpSendButton(final Activity activity, final DigitsController controller,
                                StateButton stateButton) {
        stateButton.setStatesText(R.string.dgts__continue, R.string.dgts__sending,
                R.string.dgts__done);
        stateButton.showStart();
        super.setUpSendButton(activity, controller, stateButton);
    }

    @Override
    public void setUpTermsText(Activity activity, DigitsController controller, TextView termsText) {
        termsText.setText(tosFormatHelper.getFormattedTerms(R.string.dgts__terms_email_request));
        super.setUpTermsText(activity, controller, termsText);
    }

    private DigitsController initController(Bundle bundle) {
        return new EmailRequestController(stateButton, editText,
                bundle.<ResultReceiver>getParcelable(DigitsClient.EXTRA_RESULT_RECEIVER),
                bundle.getString(DigitsClient.EXTRA_PHONE), digitsEventCollector,
                eventDetailsBuilder);
    }

    @Override
    public void onResume() {
        digitsEventCollector.emailScreenImpression(eventDetailsBuilder
                .withCurrentTime(System.currentTimeMillis()).build());
        controller.onResume();
    }
}
