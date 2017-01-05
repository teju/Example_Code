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

import android.content.Intent;
import android.telephony.SmsMessage;
import android.widget.EditText;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.RuntimeEnvironment;
import org.robolectric.annotation.Config;

import java.util.regex.Matcher;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNull;
import static org.mockito.Mockito.*;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class SmsBroadcastReceiverTests {
    final String TEST_CODE = "635589";
    final String TEST_MESSAGE =
            "40404 - Confirmation code: 635589. Enter this code in your app. (Digits by Twitter)";
    final String TEST_MESSAGE_JP =
            "認証コードは 635589です。このコードをアプリに入力してください。(Digits by Twitter)";
    final String TEST_MESSAGE_CN =
            "確認碼： 635589。在你的應用程式中輸入這個確認碼 (Digits by Twitter)";
    final String TEST_MESSAGE_DE =
            "Bestätigungscode:  635589. Gib diesen Code in Deine App ein. (Digits by Twitter)";
    final String TEST_MESSAGE_RU =
            "Код подтверждения:  635589. Введите этот код в своём приложении. (Digits by Twitter)";
    byte[] pdu = {7, -111, 65, 64, 84, 5, 0, -8, 4, 11, -111, -111, 97, 117, 50, 21, -8, 0, 0,
            81, 32, 48, -127, 4, 82, 10, 83, 52, 24, 13, 70, 3, -75, 64, -61, -73, -37, -100,
            -106, -73, -61, -12, -12, -37, 13, 26, -65, -55, 101, 29, -56, 54, -85, -43, 112, 57,
            23, -88, -24, -90, -105, -27, 32, 58, 58, 61, 7, -115, -33, -28, 50, 40, -19, 6, -27,
            -33, 117, 57, 40, 12, -121, -69, 64, 40, 98, -6, -100, -90, -49, 65, -30, 60, -120,
            122, 79, -45, -23, 101, 121, 10};
    SmsBroadcastReceiver receiver;
    EditText editText;
    SmsMessage sms;

    @Before
    public void setUp() throws Exception {
        sms = mock(SmsMessage.class);
        editText = mock(EditText.class);
        receiver = new SmsBroadcastReceiver(editText);
    }

    @Test
    public void testPattern() {
        assertEquals(TEST_CODE, getCode(TEST_MESSAGE));
        assertEquals(TEST_CODE, getCode(TEST_MESSAGE_JP));
        assertEquals(TEST_CODE, getCode(TEST_MESSAGE_CN));
        assertEquals(TEST_CODE, getCode(TEST_MESSAGE_DE));
        assertEquals(TEST_CODE, getCode(TEST_MESSAGE_RU));
    }

    private String getCode(String msg) {
        final Matcher matcher = receiver.patternConfirmationCode.matcher(msg);
        if (matcher.find()) {
            return matcher.group(1);
        }

        return null;
    }

    @Test
    public void testGetConfirmationCode_validMessage() {
        when(sms.getDisplayMessageBody()).thenReturn(TEST_MESSAGE);

        assertEquals(TEST_CODE, receiver.getConfirmationCode(sms));

        verify(sms).getDisplayMessageBody();
    }

    @Test
    public void testGetConfirmationCode_nullMessage() {
        assertNull(receiver.getConfirmationCode(sms));

        verify(sms).getDisplayMessageBody();
    }

    @Test
    public void testGetConfirmationCode_validMessageInList() {
        when(sms.getDisplayMessageBody()).thenReturn(TEST_MESSAGE);

        final SmsMessage[] messages = new SmsMessage[2];
        messages[0] = mock(SmsMessage.class);
        messages[1] = sms;

        assertEquals(TEST_CODE, receiver.getConfirmationCode(messages));

        verify(sms).getDisplayMessageBody();
    }

    @Test
    public void testGetMessagesFromIntent() {
        final Intent intent = mock(Intent.class);
        when(intent.getSerializableExtra(SmsBroadcastReceiver.PDU_EXTRA)).thenReturn(new
                Object[]{pdu});

        final SmsMessage[] messages = receiver.getMessagesFromIntent(intent);

        assertEquals(1, messages.length);
        assertEquals(TEST_MESSAGE, messages[0].getDisplayMessageBody());
    }

    @Test
    public void testOnReceive() {
        final Intent intent = mock(Intent.class);
        when(intent.getSerializableExtra(SmsBroadcastReceiver.PDU_EXTRA)).thenReturn(new
                Object[]{pdu});

        receiver.onReceive(RuntimeEnvironment.application, intent);

        verify(editText).setText(TEST_CODE);
        verify(editText).setSelection(TEST_CODE.length());
    }
}
