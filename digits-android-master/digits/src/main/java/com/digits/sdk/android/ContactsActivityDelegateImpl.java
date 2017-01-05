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
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

class ContactsActivityDelegateImpl implements ContactsActivityDelegate {
    private final DigitsEventCollector digitsEventCollector;
    final Activity activity;
    final ContactsController controller;

    public ContactsActivityDelegateImpl(Activity activity) {
        this(activity, new ContactsControllerImpl(),
                Digits.getInstance().getDigitsEventCollector());
    }

    public ContactsActivityDelegateImpl(Activity activity, ContactsController controller,
                                        DigitsEventCollector digitsEventCollector) {
        this.activity = activity;
        this.controller = controller;
        this.digitsEventCollector = digitsEventCollector;
    }

    public void init() {
        digitsEventCollector.contactsPermissionImpression(
                new ContactsPermissionForDigitsImpressionDetails());
        setContentView();
        setUpViews();
    }

    public void onBackPressed(){
        digitsEventCollector.backClickOnContactScreen();
    }

    protected void setContentView() {
        activity.setContentView(R.layout.dgts__activity_contacts);
    }

    protected void setUpViews() {
        final Button notNowButton = (Button) activity.findViewById(R.id.dgts__not_now);
        final Button okayButton = (Button) activity.findViewById(R.id.dgts__okay);
        final TextView description = (TextView) activity.findViewById(R.id.dgts__upload_contacts);

        setUpNotNowButton(notNowButton);
        setUpOkayButton(okayButton);
        setUpDescription(description);
    }

    protected void setUpDescription(TextView textView) {
        textView.setText(getFormattedDescription());
    }

    protected String getApplicationName() {
        return activity.getApplicationInfo().loadLabel(activity.getPackageManager()).toString();
    }

    protected String getFormattedDescription() {
        return activity.getString(R.string.dgts__upload_contacts, getApplicationName());
    }

    protected void setUpNotNowButton(Button button) {
        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                digitsEventCollector.contactsPermissionDeferred(
                        new ContactsPermissionForDigitsDeclinedDetails());
                activity.finish();
            }
        });
    }

    protected void setUpOkayButton(Button button) {
        button.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                digitsEventCollector.contactsPermissionApproved(
                        new ContactsPermissionForDigitsApprovedDetails());
                activity.setResult(ContactsActivity.RESULT_OK);
                controller.startUploadService(activity);
                activity.finish();
            }
        });
    }
}
