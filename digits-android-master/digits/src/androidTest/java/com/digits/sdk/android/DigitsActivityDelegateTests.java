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

import org.mockito.ArgumentCaptor;

import static org.mockito.Matchers.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoMoreInteractions;


public abstract class DigitsActivityDelegateTests<T extends DigitsActivityDelegateImpl> extends
        DigitsAndroidTestCase {
    private static final int ANY_CODE = 0;
    private static final int ANY_ACTION = 1;
    T delegate;
    Activity activity;
    DigitsController controller;
    InvertedStateButton resendButton, callMeButton;
    LinkTextView editPhoneNumberLink;
    StateButton button;
    ArgumentCaptor<View.OnClickListener> captorClick;
    ArgumentCaptor<TextView.OnEditorActionListener> captorEditor;
    EditText editText;
    TextView textView;
    TextView timerText;
    DigitsEventCollector digitsEventCollector;
    RelativeLayout.LayoutParams layoutParams;
    TosFormatHelper tosFormatHelper;
    ArgumentCaptor<DigitsEventDetails> detailsArgumentCaptor;
    BucketedTextChangeListener bucketedTextChangeListener;

    @Override
    public void setUp() throws Exception {
        super.setUp();
        digitsEventCollector = mock(DummyDigitsEventCollector.class);
        delegate = getDelegate();
        activity = mock(Activity.class);
        controller = mock(DigitsController.class);
        button = mock(StateButton.class);
        resendButton = mock(InvertedStateButton.class);
        callMeButton = mock(InvertedStateButton.class);
        editPhoneNumberLink = mock(LinkTextView.class);
        captorClick = ArgumentCaptor.forClass(View.OnClickListener.class);
        captorEditor = ArgumentCaptor.forClass(TextView.OnEditorActionListener.class);
        editText = mock(EditText.class);
        textView = mock(TextView.class);
        timerText = mock(TextView.class);
        layoutParams = mock(RelativeLayout.LayoutParams.class);
        tosFormatHelper = mock(TosFormatHelper.class);
        detailsArgumentCaptor = ArgumentCaptor.forClass(DigitsEventDetails.class);
        bucketedTextChangeListener = mock(DummyBucketedTextChangeListener.class);
    }

    public abstract T getDelegate();

    public void testSetUpSendButton() throws Exception {
        delegate.setUpSendButton(activity, controller, button);

        verify(button).setOnClickListener(captorClick.capture());
        final View.OnClickListener listener = captorClick.getValue();
        listener.onClick(null);
        verify(controller).clearError();
        verify(controller).executeRequest(activity);
    }

    public void testSetUpEditText_nextAction() throws Exception {
        delegate.setUpEditText(activity, controller, editText);

        verify(editText).setOnEditorActionListener(captorEditor.capture());
        final TextView.OnEditorActionListener listener = captorEditor.getValue();
        listener.onEditorAction(editText, EditorInfo.IME_ACTION_NEXT, new KeyEvent(ANY_ACTION,
                ANY_CODE));
        verify(controller).clearError();
        verify(controller).executeRequest(activity);
        verify(controller).getTextWatcher();
    }

    public void testSetUpEditText_noNextAction() throws Exception {
        delegate.setUpEditText(activity, controller, editText);

        verify(editText).setOnEditorActionListener(captorEditor.capture());
        final TextView.OnEditorActionListener listener = captorEditor.getValue();
        listener.onEditorAction(editText, EditorInfo.IME_ACTION_DONE, new KeyEvent(ANY_ACTION,
                ANY_CODE));
        verify(controller).getTextWatcher();
        verifyNoMoreInteractions(controller);
    }

    public void testSetUpTermsText() throws Exception {
        delegate.setUpTermsText(activity, controller, textView);
        verify(textView).setMovementMethod(any(LinkMovementMethod.class));
        verify(textView).setOnClickListener(captorClick.capture());
        final View.OnClickListener listener = captorClick.getValue();
        listener.onClick(null);
        verify(controller).clearError();
    }
}
