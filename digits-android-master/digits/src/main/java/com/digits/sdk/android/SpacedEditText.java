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
import android.content.res.TypedArray;
import android.text.Editable;
import android.text.Spannable;
import android.text.SpannableStringBuilder;
import android.text.style.ScaleXSpan;
import android.util.AttributeSet;
import android.widget.EditText;

/**
 * Android does not provide a letterSpacing/kerning option until API 21.
 * {@link SpacedEditText} provides kerning by injecting spaces between characters of the string set
 * using {@link SpacedEditText#setText(CharSequence, BufferType)}.
 *
 * An alternative would be to {@link EditText#setScaleX(float)}. However that stretches/squeezes the
 * entire content resulting in fonts appearing stretched/squeezed.
 *
 * Hence, {@link SpacedEditText} in addition to inserting the spaces, also sets the ScaleXSpan on
 * them, thus helping us preserve the font's intended appearance.
 *
 * Reference:
 * http://stackoverflow.com/questions/1640659/how-to-adjust-text-kerning-in-android-textview
 */
public class SpacedEditText extends EditText {
    private float proportion;
    private SpannableStringBuilder originalText;

    public SpacedEditText(Context context) {
        super(context);
    }

    public SpacedEditText(Context context, AttributeSet attrs) {
        super(context, attrs);
        initAttrs(context, attrs);
    }

    void initAttrs(Context context, AttributeSet attrs) {
        originalText = new SpannableStringBuilder("");
        final TypedArray array =
                context.obtainStyledAttributes(attrs, R.styleable.SpacedEditText);
        //Controls the ScaleXSpan applied on the injected spaces
        proportion = array.getFloat(R.styleable.SpacedEditText_spacingProportion, 1);
        array.recycle();
    }

    @Override
    public void setText(CharSequence text, BufferType type) {
        originalText = new SpannableStringBuilder(text);
        final SpannableStringBuilder spacedOutString = getSpacedOutString(text);
        super.setText(spacedOutString, BufferType.SPANNABLE);
    }

    /**
     * Set the selection after recalculating the index intended by the caller.
     * @param index
     */
    @Override
    public void setSelection(int index) {
        //if the index is the leading edge, there are no spaces before it.
        //for all other cases, the index is preceeded by index - 1 spaces.
        int spacesUptoIndex;
        if (index == 0) {
            spacesUptoIndex = 0;
        } else {
            spacesUptoIndex = index - 1;
        }
        final int recalculatedIndex = index + spacesUptoIndex;

        super.setSelection(recalculatedIndex);
    }

    private SpannableStringBuilder getSpacedOutString(CharSequence text) {
        final SpannableStringBuilder builder =  new SpannableStringBuilder();
        final int textLength = text.length();
        int lastSpaceIndex = -1;

        //Insert a space in front of all characters upto the last character
        //Scale the space without scaling the character to preserve font appearance
        for (int i = 0; i < textLength - 1; i++) {
            builder.append(text.charAt(i));
            builder.append(" ");
            lastSpaceIndex += 2;
            builder.setSpan(new ScaleXSpan(proportion), lastSpaceIndex, lastSpaceIndex + 1,
                    Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);
        }

        //Append the last character
        if (textLength != 0) builder.append(text.charAt(textLength - 1));

        return builder;
    }

    public Editable getUnspacedText() {
        return this.originalText;
    }
}
