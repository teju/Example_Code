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

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.telephony.SmsMessage;
import android.widget.EditText;

import java.lang.ref.WeakReference;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class SmsBroadcastReceiver extends BroadcastReceiver {
    static final String PDU_EXTRA = "pdus";
    final Pattern patternConfirmationCode = Pattern.compile("\\s(\\d{6}).*Digits by Twitter");
    final WeakReference<EditText> editTextWeakReference;

    SmsBroadcastReceiver(EditText editText) {
        editTextWeakReference = new WeakReference<>(editText);
    }

    @Override
    public void onReceive(Context context, Intent intent) {
        final SmsMessage[] messages = getMessagesFromIntent(intent);
        final String confirmationCode = getConfirmationCode(messages);
        if (confirmationCode != null) {
            final EditText editText = editTextWeakReference.get();
            if (editText != null) {
                editText.setText(confirmationCode);
                editText.setSelection(confirmationCode.length());
            }
        }
    }

    String getConfirmationCode(SmsMessage[] messages) {
        for (SmsMessage message : messages) {
            final String result = getConfirmationCode(message);
            if (result != null) {
                return result;
            }
        }

        return null;
    }

    String getConfirmationCode(SmsMessage message) {
        final String body = message.getDisplayMessageBody();
        if (body != null) {
            final Matcher matcher = patternConfirmationCode.matcher(body);
            if (matcher.find()) {
                return matcher.group(1);
            }
        }

        return null;
    }

    SmsMessage[] getMessagesFromIntent(Intent intent) {
        final Object[] messages = (Object[]) intent.getSerializableExtra(PDU_EXTRA);
        final int pduCount = messages.length;
        final SmsMessage[] msgs = new SmsMessage[pduCount];
        for (int i = 0; i < pduCount; i++) {
            msgs[i] = SmsMessage.createFromPdu((byte[]) messages[i]);
        }
        return msgs;
    }
}
