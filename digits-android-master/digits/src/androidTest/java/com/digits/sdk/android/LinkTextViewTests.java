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

import android.content.Context;
import android.content.ContextWrapper;
import android.view.ContextThemeWrapper;

import com.digits.sdk.android.test.R;

import static org.mockito.Mockito.spy;
import static org.mockito.Mockito.when;


public class LinkTextViewTests extends DigitsAndroidTestCase {

    public void testLinkTextView_themeLinkColor() throws Exception {
        final Context context = new ContextThemeWrapper(getContext(),
                R.style.Digits_default_link_color);
        final LinkTextView textView = new LinkTextView(context);
        assertEquals((getContext().getResources().getColor(R.color.link_text)),
                textView.getCurrentTextColor());
    }

    public void testLinkTextView_defaultAccent() throws Exception {
        final Context context = spy(new ContextWrapper(getContext()));
        when(context.getTheme()).thenReturn(getContext().getResources().newTheme());
        final LinkTextView textView = new LinkTextView(context);
        assertEquals(getContext().getResources().getColor(R.color
                .dgts__default_accent), textView.getCurrentTextColor());
    }

    public void testLinkTextView_customAccent() throws Exception {
        final Context context = new ContextThemeWrapper(getContext(),
                R.style.Digits_default_accent);
        final LinkTextView textView = new LinkTextView(context);
        assertEquals(getContext().getResources().getColor(R.color
                .accent), textView.getCurrentTextColor());
    }

}
