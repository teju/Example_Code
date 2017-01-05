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
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

import io.fabric.sdk.android.services.common.CommonUtils;

class FailureActivityDelegateImpl implements FailureActivityDelegate {
    final Activity activity;
    final FailureController controller;
    final DigitsEventCollector digitsEventCollector;
    DigitsEventDetailsBuilder eventDetailsBuilder;

    public FailureActivityDelegateImpl(Activity activity) {
        this(activity, new FailureControllerImpl(), Digits.getInstance().getDigitsEventCollector());
    }

    public FailureActivityDelegateImpl(Activity activity, FailureController controller,
                                       DigitsEventCollector digitsEventCollector) {
        this.activity = activity;
        this.controller = controller;
        this.digitsEventCollector = digitsEventCollector;
    }

    public void init() {
        final Bundle bundle = activity.getIntent().getExtras();

        if (isBundleValid(bundle)) {
            setContentView();
            setUpViews();
            eventDetailsBuilder = bundle.getParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER);

            digitsEventCollector.failureScreenImpression(eventDetailsBuilder
                    .withCurrentTime(System.currentTimeMillis()).build());
        } else {
            throw new IllegalAccessError("This activity can only be started from Digits");
        }
    }

    protected boolean isBundleValid(Bundle bundle) {
        return BundleManager.assertContains(bundle, DigitsClient.EXTRA_RESULT_RECEIVER);
    }

    protected void setContentView() {
        activity.setContentView(R.layout.dgts__activity_failure);
    }

    protected void setUpViews() {
        final Button dismissButton = (Button) activity.findViewById(R.id.dgts__dismiss_button);
        final TextView tryAnotherNumberButton = (TextView) activity.findViewById(R.id
                .dgts__try_another_phone);

        setUpDismissButton(dismissButton);
        setUpTryAnotherPhoneButton(tryAnotherNumberButton);
    }

    protected void setUpDismissButton(Button button) {
        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                digitsEventCollector.dismissClickOnFailureScreen(eventDetailsBuilder
                        .withCurrentTime(System.currentTimeMillis()).build());
                CommonUtils.finishAffinity(activity, DigitsActivity.RESULT_FINISH_DIGITS);
                controller.sendFailure(getBundleResultReceiver(), getBundleException(),
                        eventDetailsBuilder);
            }
        });
    }

    protected void setUpTryAnotherPhoneButton(TextView textView) {
        textView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                digitsEventCollector.retryClickOnFailureScreen(eventDetailsBuilder
                        .withCurrentTime(System.currentTimeMillis()).build());
                controller.tryAnotherNumber(activity, getBundleResultReceiver());
                activity.finish();
            }
        });
    }

    private ResultReceiver getBundleResultReceiver() {
        final Bundle bundle = activity.getIntent().getExtras();
        return bundle.getParcelable(DigitsClient.EXTRA_RESULT_RECEIVER);
    }

    private DigitsException getBundleException() {
        final Bundle bundle = activity.getIntent().getExtras();
        return (DigitsException) bundle.getSerializable(DigitsClient.EXTRA_FALLBACK_REASON);
    }
}
