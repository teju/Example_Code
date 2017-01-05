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
import android.graphics.drawable.Drawable;
import android.util.AttributeSet;

public class InvertedStateButton extends StateButton {
    public InvertedStateButton(Context context) {
        this(context, null);
    }

    public InvertedStateButton(Context context, AttributeSet attrs) {
        this(context, attrs, 0);
    }

    public InvertedStateButton(Context context, AttributeSet attrs, int defStyle) {
        super(context, attrs, defStyle);
    }

    @Override
    void initView(Context context){
        accentColor = ThemeUtils.getAccentColor(getResources(), context.getTheme());
        buttonThemer = new ButtonThemer(getResources());
        buttonThemer.setBackgroundAccentColorInverse(this, accentColor);
        buttonThemer.setTextAccentColorInverse(textView, accentColor);
        setImageAccentColor();
        setSpinnerAccentColor();
    }

    @Override
    int getTextColor() {
        return buttonThemer.getTextColorInverse(accentColor);
    }

    @Override
    Drawable getProgressDrawable() {
        return ThemeUtils.isLightColor(accentColor) ?
                getResources().getDrawable(R.drawable.progress_light)
                : getResources().getDrawable(R.drawable.progress_dark);
    }
}
