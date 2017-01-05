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

package com.example.app.digits;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.support.v4.app.NotificationCompat;
import android.util.Log;

import com.digits.sdk.android.ContactsUploadFailureResult;
import com.digits.sdk.android.ContactsUploadService;
import com.example.app.R;
import com.example.app.SampleApplication;

public class ContactsReceiver extends BroadcastReceiver {
    static final int NOTIFICATION_ID = 0;

    @Override
    public void onReceive(Context context, Intent intent) {
        final NotificationManager notificationManager = (NotificationManager)
                context.getSystemService(Context.NOTIFICATION_SERVICE);

        final Intent notifIntent = new Intent(context, FoundFriendsActivity.class);
        final PendingIntent notifPendingIntent = PendingIntent.getActivity(context, 0,
                notifIntent, 0);

        final String notifString;
        if (ContactsUploadService.UPLOAD_FAILED.equals(intent.getAction())) {
            final ContactsUploadFailureResult result = intent.getParcelableExtra(
                    ContactsUploadService.UPLOAD_FAILED_EXTRA);
            Log.e(SampleApplication.TAG, String.format(
                    "contact upload failed, result=%s", result));
            if (ContactsUploadFailureResult.Summary.PERMISSION.equals(result.summary)) {
                notifString = context.getString(R.string.contact_upload_failed_permission);
            } else {
                notifString = context.getString(R.string.contact_upload_failed);
            }
        } else {
            notifString = context.getString(R.string.you_have_new_friends);
        }

        final Notification notification = new NotificationCompat.Builder(context)
                .setContentTitle(notifString)
                .setContentIntent(notifPendingIntent)
                .setSmallIcon(android.R.drawable.sym_def_app_icon)
                .build();

        notificationManager.notify(NOTIFICATION_ID, notification);
    }
}
