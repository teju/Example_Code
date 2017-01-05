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
import android.view.View;
import android.widget.RelativeLayout;

import static org.mockito.Mockito.doNothing;
import static org.mockito.Mockito.doReturn;
import static org.mockito.Mockito.times;
import static org.mockito.Mockito.verify;

public class DigitsActivityDelegateImplTests extends DigitsActivityDelegateTests
        <DigitsActivityDelegateImplTests.MockDigitsActivityDelegateImpl> {
    static final Integer COUNTRY_CODE = 123;
    static final String PHONE_WITH_COUNTRY_CODE = "+" + COUNTRY_CODE + "123456789";

    @Override
    public void setUp() throws Exception {
        super.setUp();
        doReturn(layoutParams).when(timerText).getLayoutParams();
        doNothing().when(controller).startTimer();
    }

    @Override
    public MockDigitsActivityDelegateImpl getDelegate() {
        return new MockDigitsActivityDelegateImpl();
    }

    public void testSetupTimerText_voiceEnabled() throws Exception {
        final AuthConfig config = new AuthConfig();
        config.isVoiceEnabled = Boolean.TRUE;

        delegate.setupCountDownTimer(controller, timerText, config);

        verify(layoutParams).addRule(RelativeLayout.ALIGN_RIGHT,
                R.id.dgts__callMeButton);
        verify(layoutParams).addRule(RelativeLayout.ALIGN_BOTTOM,
                R.id.dgts__callMeButton);
        verify(controller).startTimer();
    }

    public void testSetupTimerText_voiceDisabled() throws Exception {
        final AuthConfig config = new AuthConfig();
        config.isVoiceEnabled = Boolean.FALSE;

        delegate.setupCountDownTimer(controller, timerText, config);

        verify(layoutParams).addRule(RelativeLayout.ALIGN_RIGHT,
                R.id.dgts__resendConfirmationButton);
        verify(layoutParams).addRule(RelativeLayout.ALIGN_BOTTOM,
                R.id.dgts__resendConfirmationButton);
        verify(controller).startTimer();

    }

    public void testSetUpEditPhoneLink() throws Exception {
        delegate.setUpEditPhoneNumberLink(activity, editPhoneNumberLink,
                PHONE_WITH_COUNTRY_CODE);
        verify(editPhoneNumberLink).setText(PHONE_WITH_COUNTRY_CODE);
        verify(editPhoneNumberLink).setOnClickListener(captorClick.capture());

        final View.OnClickListener listener = captorClick.getValue();
        listener.onClick(null);

        verify(activity).finish();
        verifyResultCode(activity, DigitsActivity.RESULT_CHANGE_PHONE_NUMBER);
    }

    public void testCreateBucketOnEditCallback() throws Exception {
        final BucketedTextChangeListener.ContentChangeCallback callback =
                delegate.createBucketOnEditCallback(button);

        callback.whileIncomplete();
        callback.whileIncomplete();
        callback.whileIncomplete();
        callback.whileIncomplete();
        verify(button, times(4)).setEnabled(false);

        callback.whileComplete();
        callback.whileComplete();
        verify(button, times(2)).setEnabled(true);
    }

    class MockDigitsActivityDelegateImpl extends DigitsActivityDelegateImpl {
        @Override
        public int getLayoutId() {
            return 0;
        }

        @Override
        public boolean isValid(Bundle bundle) {
            return false;
        }

        @Override
        public void init(Activity activity, Bundle bundle) {

        }

        @Override
        public void onResume() {

        }
    }
}
