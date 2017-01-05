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

import android.content.IntentFilter;
import android.content.pm.PackageManager;
import android.content.res.Resources;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.os.ResultReceiver;
import android.text.SpannedString;
import android.view.View;

import static org.mockito.Matchers.any;
import static org.mockito.Matchers.anyInt;
import static org.mockito.Mockito.atLeast;
import static org.mockito.Mockito.doReturn;
import static org.mockito.Mockito.spy;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoMoreInteractions;
import static org.mockito.Mockito.when;

public class ConfirmationCodeActivityDelegateTests extends
        DigitsActivityDelegateTests<ConfirmationCodeActivityDelegate> {
    final DigitsEventDetailsBuilder completeDetails =
            new DigitsEventDetailsBuilder()
                    .withAuthStartTime(ANY_LONG)
                    .withLanguage(LANG)
                    .withCountry(US_ISO2);
    @Override
    public ConfirmationCodeActivityDelegate getDelegate() {
        return spy(new DummyConfirmationCodeActivityDelegate(digitsEventCollector));
    }

    public void testIsValid() {
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, "");
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, completeDetails);

        assertTrue(delegate.isValid(bundle));
    }

    public void testIsValid_missingResultReceiver() {
        final Bundle bundle = new Bundle();
        bundle.putString(DigitsClient.EXTRA_PHONE, "");
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, completeDetails);

        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingPhoneNumber() {
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, completeDetails);

        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingAuthStartTime() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withLanguage(LANG)
                        .withCountry(US_ISO2);
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, "");
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);

        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingLanguage() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(ANY_LONG)
                        .withCountry(US_ISO2);
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, "");
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);

        assertFalse(delegate.isValid(bundle));
    }

    public void testIsValid_missingCountry() {
        final DigitsEventDetailsBuilder eventDetailsBuilder =
                new DigitsEventDetailsBuilder()
                        .withAuthStartTime(ANY_LONG)
                        .withLanguage(LANG);
        final Bundle bundle = new Bundle();
        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, new ResultReceiver(null));
        bundle.putString(DigitsClient.EXTRA_PHONE, "");
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);

        assertFalse(delegate.isValid(bundle));
    }

    public void testGetLayoutId() {
        assertEquals(R.layout.dgts__activity_confirmation, delegate.getLayoutId());
    }

    @Override
    public void testSetUpEditText_nextAction() throws Exception {
        final Drawable dr = getContext().getResources()
                .getDrawable(Resources.getSystem()
                        .getIdentifier("indicator_input_error", "drawable", "android"));
        when(activity.getResources()).thenReturn(getContext().getResources());
        delegate.bucketedTextChangeListener = bucketedTextChangeListener;

        super.testSetUpEditText_nextAction();
        verify(editText).setText(getContext().getResources()
                .getString(R.string.dgts__confirmationEditTextPlaceholder));
        verify(editText).addTextChangedListener(bucketedTextChangeListener);
        verify(editText).setCompoundDrawablePadding(dr.getIntrinsicWidth() * -1);
    }

    @Override
    public void testSetUpEditText_noNextAction() throws Exception {
        when(activity.getResources()).thenReturn(getContext().getResources());
        delegate.bucketedTextChangeListener = bucketedTextChangeListener;
        super.testSetUpEditText_nextAction();
    }

    @Override
    public void testSetUpSendButton() throws Exception {
        super.testSetUpSendButton();
        verify(button).setStatesText(R.string.dgts__create_account, R.string.dgts__sending,
                R.string.dgts__done);
        verify(button).showStart();
        verify(button).setEnabled(false);
    }

    public void testOnResume() {
        delegate.controller = controller;
        delegate.eventDetailsBuilder = new DigitsEventDetailsBuilder()
                .withLanguage(LANG)
                .withCountry(US_ISO2)
                .withAuthStartTime(ANY_LONG);

        delegate.onResume();
        verify(controller).onResume();
        verify(digitsEventCollector).signupScreenImpression(detailsArgumentCaptor.capture());
        final DigitsEventDetails details = detailsArgumentCaptor.getValue();
        assertEquals(LANG, details.language);
        assertEquals(US_ISO2, details.country);
        assertNotNull(details.elapsedTimeInMillis);
    }

    public void testSetupResendButton() throws Exception {
        delegate.setupResendButton(activity, controller, digitsEventCollector, resendButton);
        verify(resendButton).setEnabled(false);
        verify(resendButton).setOnClickListener(captorClick.capture());
        final View.OnClickListener listener = captorClick.getValue();
        listener.onClick(null);
        verify(digitsEventCollector).resendClickOnSignupScreen();
        verify(controller, atLeast(0)).clearError();
        verify(controller).resendCode(activity, resendButton, Verification.sms);
    }

    public void testSetupCallMeButton_voiceEnabled() throws Exception {
        final AuthConfig config = new AuthConfig();
        config.isVoiceEnabled = Boolean.TRUE;

        delegate.setupCallMeButton(activity, controller, digitsEventCollector,
                callMeButton, config);

        verify(callMeButton).setOnClickListener(captorClick.capture());
        final View.OnClickListener listener = captorClick.getValue();
        listener.onClick(null);
        verify(digitsEventCollector).callMeClickOnSignupScreen();
        verify(controller, atLeast(0)).clearError();
        verify(controller).resendCode(activity, callMeButton, Verification.voicecall);
        verify(callMeButton).setEnabled(false);
        verify(callMeButton).setVisibility(View.VISIBLE);
    }

    public void testSetupCallMeButton_voiceDisabled() throws Exception {
        final AuthConfig config = new AuthConfig();
        config.isVoiceEnabled = Boolean.FALSE;

        delegate.setupCallMeButton(activity, controller, digitsEventCollector,
                callMeButton, config);
        verify(callMeButton).setEnabled(false);
        verify(callMeButton).setVisibility(View.GONE);
    }

    public void testSetUpSmsIntercept_permissionDenied() {
        when(activity.checkCallingOrSelfPermission("android.permission.RECEIVE_SMS"))
                .thenReturn(PackageManager.PERMISSION_DENIED);

        delegate.setUpSmsIntercept(activity, editText);

        verify(activity).checkCallingOrSelfPermission("android.permission.RECEIVE_SMS");
        verifyNoMoreInteractions(activity);
    }

    public void testSetUpSmsIntercept_permissionGranted() {
        when(activity.checkCallingOrSelfPermission("android.permission.RECEIVE_SMS"))
                .thenReturn(PackageManager.PERMISSION_GRANTED);

        delegate.setUpSmsIntercept(activity, editText);

        verify(activity).checkCallingOrSelfPermission("android.permission.RECEIVE_SMS");
        verify(activity).registerReceiver(any(SmsBroadcastReceiver.class), any(IntentFilter.class));
    }

    @Override
    public void testSetUpTermsText() throws Exception {
        delegate.tosFormatHelper = tosFormatHelper;
        doReturn(new SpannedString("")).when(tosFormatHelper).getFormattedTerms(anyInt());
        super.testSetUpTermsText();
        verify(tosFormatHelper).getFormattedTerms(R.string.dgts__terms_text_create);
        verify(textView).setText(new SpannedString(""));
    }

    public class DummyConfirmationCodeActivityDelegate extends ConfirmationCodeActivityDelegate {

        public DummyConfirmationCodeActivityDelegate(DigitsEventCollector digitsEventCollector) {
            super(digitsEventCollector);
        }
    }
}
