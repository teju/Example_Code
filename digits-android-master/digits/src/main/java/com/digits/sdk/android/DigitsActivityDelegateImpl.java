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
import android.text.method.LinkMovementMethod;
import android.view.KeyEvent;
import android.view.View;
import android.view.inputmethod.EditorInfo;
import android.widget.EditText;
import android.widget.RelativeLayout;
import android.widget.TextView;

abstract class DigitsActivityDelegateImpl implements DigitsActivityDelegate {
    DigitsEventDetailsBuilder eventDetailsBuilder;

    @Override
    public void onDestroy() {
        // no-op Not currently used by all delegates
    }

    public void setUpSendButton(final Activity activity, final DigitsController controller,
                                StateButton stateButton) {
        stateButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                controller.clearError();
                controller.executeRequest(activity);
            }
        });

    }

    void setupCountDownTimer(final DigitsController controller,
                             final TextView timerText,
                             final AuthConfig config){
        setTimerAlignment(timerText, config.isVoiceEnabled);
        controller.startTimer();
    }

    protected void setUpEditPhoneNumberLink(final Activity activity,
                                            final LinkTextView editPhoneLink,
                                            String phoneNumber) {
        editPhoneLink.setText(phoneNumber);
        editPhoneLink.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                activity.setResult(DigitsActivity.RESULT_CHANGE_PHONE_NUMBER);
                activity.finish();
            }
        });
    }

    public void setUpEditText(final Activity activity, final DigitsController controller,
                              EditText editText) {
        editText.setOnEditorActionListener(new TextView.OnEditorActionListener() {
            @Override
            public boolean onEditorAction(TextView v, int actionId, KeyEvent event) {
                if (actionId == EditorInfo.IME_ACTION_NEXT) {
                    controller.clearError();
                    controller.executeRequest(activity);
                    return true;
                }
                return false;
            }
        });
        editText.addTextChangedListener(controller.getTextWatcher());
    }

    public void setUpTermsText(final Activity activity, final DigitsController controller,
                               TextView termsText) {
        termsText.setMovementMethod(LinkMovementMethod.getInstance());

        termsText.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                controller.clearError();
            }
        });
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Activity activity) {
        // no-op Not used by all the delegates
    }

    private void setTimerAlignment(final TextView timerText, boolean isVoiceEnabled){
        final int resendElementToAlign = isVoiceEnabled ?
                R.id.dgts__callMeButton : R.id.dgts__resendConfirmationButton;
        final RelativeLayout.LayoutParams params =
                (RelativeLayout.LayoutParams) timerText.getLayoutParams();

        params.addRule(RelativeLayout.ALIGN_RIGHT, resendElementToAlign);
        params.addRule(RelativeLayout.ALIGN_BOTTOM, resendElementToAlign);

        timerText.setLayoutParams(params);
    }

    BucketedTextChangeListener.ContentChangeCallback createBucketOnEditCallback(
            final StateButton button) {
        return new BucketedTextChangeListener.ContentChangeCallback() {
            @Override
            public void whileComplete() {
                button.setEnabled(true);
            }

            @Override
            public void whileIncomplete() {
                button.setEnabled(false);
            }
        };
    }
}
