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
import android.graphics.PorterDuff;
import android.util.AttributeSet;
import android.widget.ImageView;


public class ColorPrimaryImageView extends ImageView {

    public ColorPrimaryImageView(Context context) {
        super(context);
        init(context);
    }

    public ColorPrimaryImageView(Context context, AttributeSet attrs) {
        this(context, attrs, 0);
    }

    public ColorPrimaryImageView(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        init(context);
    }

    private void init(Context context) {
        setColorFilter(getTextColorPrimary(context), PorterDuff.Mode.SRC_IN);
    }

    private int getTextColorPrimary(Context context) {
        final TypedArray array = context.getTheme().obtainStyledAttributes(
                new int[]{android.R.attr.textColorPrimary});
        final int color = array.getColor(0, getResources().getColor(
                R.color.dgts__default_logo_name));
        array.recycle();
        return color;
    }
}

