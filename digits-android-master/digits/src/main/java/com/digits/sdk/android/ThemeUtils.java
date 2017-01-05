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

import android.annotation.TargetApi;
import android.content.res.Resources;
import android.content.res.Resources.Theme;
import android.content.res.TypedArray;
import android.graphics.Color;
import android.graphics.drawable.Drawable;
import android.os.Build;
import android.util.TypedValue;

import java.lang.reflect.Field;

class ThemeUtils {
    public static final int DEFAULT_THEME = 0;
    public static final String THEME_RESOURCE_ID = "THEME_RESOURCE_ID";

    private ThemeUtils() {

    }

    static TypedValue getTypedValueColor(Theme theme, int colorResId) {
        final TypedValue typedValue = new TypedValue();
        theme.resolveAttribute(colorResId, typedValue, true);

        if (typedValue.type >= TypedValue.TYPE_FIRST_COLOR_INT &&
                typedValue.type <= TypedValue.TYPE_LAST_COLOR_INT) {
            return typedValue;
        }

        return null;
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    static int getAccentColor(Resources res, Theme theme) {
        // First check to see if dgts__accentColor was set
        TypedValue typedValue = getTypedValueColor(theme, R.attr.dgts__accentColor);
        if (typedValue != null) {
            return typedValue.data;
        }

        // Next pick a reasonable default
        // If API 21+ use android.R.attr.colorAccent
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            typedValue = getTypedValueColor(theme, android.R.attr.colorAccent);
            if (typedValue != null) {
                return typedValue.data;
            }
        }

        // If AppCompat use R.attr.colorAccent
        try {
            final Field field = R.attr.class.getDeclaredField("colorAccent");
            typedValue = getTypedValueColor(theme, field.getInt(field.getType()));
            if (typedValue != null) {
                return typedValue.data;
            }
        } catch (Exception e) {
            // Ignore error
        }

        // Use default holo blue
        return res.getColor(R.color.dgts__default_accent);
    }

    /**
     * This method uses HSL to determine in a human eyesight terms if a color is light or not.
     * See: http://en.wikipedia.org/wiki/HSL_and_HSV. The threshold values are from ITU Rec. 709
     * http://en.wikipedia.org/wiki/Rec._709#Luma_coefficients
     *
     * @param  color A color value
     * @return Whether or not the color is considered light
     */
    static boolean isLightColor(final int color) {
        final int r = Color.red(color);
        final int g = Color.green(color);
        final int b = Color.blue(color);

        final double threshold = 0.21 * r + 0.72 * g + 0.07 * b;
        return threshold > 170;
    }

    /**
     * This method calculates a combination of colors using an opacity of the foreground layered
     * over the background color. This allows us to optimize color calculations instead of setting
     * alpha values in the color attributes on the views directly.
     *
     * @param opacity      A value in the range of 0 to 1 that indicates the opacity desired for the
     *                     overlay color
     * @param overlayColor The foreground color that the opacity will be applied to
     * @param primaryColor The background color that the foreground color is applied to
     * @return             The combined color value
     */
    static int calculateOpacityTransform(final double opacity, final int overlayColor,
            final int primaryColor) {
        final int redPrimary = Color.red(primaryColor);
        final int redOverlay = Color.red(overlayColor);
        final int greenPrimary = Color.green(primaryColor);
        final int greenOverlay = Color.green(overlayColor);
        final int bluePrimary = Color.blue(primaryColor);
        final int blueOverlay = Color.blue(overlayColor);

        final int redCalculated = (int) ((1 - opacity) * redPrimary + opacity * redOverlay);
        final int greenCalculated = (int) ((1 - opacity) * greenPrimary + opacity * greenOverlay);
        final int blueCalculated = (int) ((1 - opacity) * bluePrimary + opacity * blueOverlay);

        return Color.rgb(redCalculated, greenCalculated, blueCalculated);
    }

    static Drawable getLogoDrawable(Theme theme) {
        final TypedValue typedValue = new TypedValue();
        final int[] drawableAttr = new int[]{R.attr.dgts__logoDrawable};
        final int indexOfAttr = 0;
        final TypedArray a = theme.obtainStyledAttributes(typedValue.data, drawableAttr);
        return a.getDrawable(indexOfAttr);
    }
}
