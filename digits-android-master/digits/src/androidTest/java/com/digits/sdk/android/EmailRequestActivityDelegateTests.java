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

import android.os.Bundle;
import android.os.ResultReceiver;
import android.text.InputType;
import android.text.SpannedString;

import static org.mockito.Matchers.anyInt;
import static org.mockito.Mockito.doReturn;
import static org.mockito.Mockito.spy;
import static org.mockito.Mockito.verify;

public class EmailRequestActivityDelegateTests extends
        DigitsActivityDelegateTests<EmailRequestActivityDelegate> {
    @Override
    public EmailRequestActivityDelegate getDelegate() {
        return spy(new DummyEmailRequestActivityDelegate(digitsEventCollector));
    }

    public void testIsValid() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(1L)
                        .withLanguage("lang")
                        .withCountry("US");
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, TestConstants.ANY_PHONE);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);
        assertTrue(delegate.isValid(bundle));
    }

    public void testIsValid_missingResultReceiver() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(1L)
                        .withLanguage("lang")
                        .withCountry("US");
        final Bundle bundle = new Bundle();
        bundle.putString(DigitsClient.EXTRA_PHONE, TestConstants.ANY_PHONE);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);
        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingPhoneNumber() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(1L)
                        .withLanguage("lang")
                        .withCountry("US");
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);
        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingAuthStartTime() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withLanguage("lang")
                        .withCountry("US");
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, TestConstants.ANY_PHONE);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);
        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingLanguage() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(1L)
                        .withCountry("US");
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, TestConstants.ANY_PHONE);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);
        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingCountry() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(1L)
                        .withLanguage("lang");
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, TestConstants.ANY_PHONE);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);
        assertFalse(delegate.isValid(bundle));
    }

    public void testGetLayoutId() {
        assertEquals(R.layout.dgts__activity_email, delegate.getLayoutId());
    }

    @Override
    public void testSetUpTermsText() throws Exception {
        delegate.tosFormatHelper = tosFormatHelper;
        doReturn(new SpannedString("")).when(tosFormatHelper)
                .getFormattedTerms(anyInt());
        super.testSetUpTermsText();
        verify(tosFormatHelper).getFormattedTerms(R.string.dgts__terms_email_request);
        verify(textView).setText(new SpannedString(""));
    }

    @Override
    public void testSetUpSendButton() throws Exception {
        super.testSetUpSendButton();
        verify(button).setStatesText(R.string.dgts__continue, R.string.dgts__sending,
                R.string.dgts__done);
        verify(button).showStart();
    }

    public void testSetUpEditText() throws Exception {
        super.testSetUpEditText_noNextAction();
        verify(editText).setInputType(InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
    }

    public void testOnResume() {
        delegate.controller = controller;
        delegate.eventDetailsBuilder = new DigitsEventDetailsBuilder()
                .withLanguage("lang")
                .withCountry("US")
                .withAuthStartTime(1L);

        delegate.onResume();
        verify(controller).onResume();
        verify(digitsEventCollector).emailScreenImpression(detailsArgumentCaptor.capture());
        final DigitsEventDetails details = detailsArgumentCaptor.getValue();
        assertNotNull(details.language);
        assertNotNull(details.country);
        assertNotNull(details.elapsedTimeInMillis);
    }

    public class DummyEmailRequestActivityDelegate extends EmailRequestActivityDelegate {
        DummyEmailRequestActivityDelegate(DigitsEventCollector digitsEventCollector) {
            super(digitsEventCollector);
        }
    }
}
