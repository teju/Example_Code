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

import java.lang.reflect.Field;

class ActivityClassManagerFactory {

    ActivityClassManager createActivityClassManager(Context context, int themeResId) {
        try {
            Class.forName("android.support.v7.app.ActionBarActivity");

            final ThemeAttributes attributes = new ThemeAttributes();
            if (isAppCompatTheme(context, themeResId, attributes)) {
                return new AppCompatClassManagerImp();
            } else {
                return new ActivityClassManagerImp();
            }
        } catch (Exception e) {
            return new ActivityClassManagerImp();
        }
    }

    private boolean isAppCompatTheme(Context context, int themeResId, ThemeAttributes attributes) {

        final TypedArray a = context.obtainStyledAttributes(themeResId, attributes.styleableTheme);
        final boolean result = a.hasValue(attributes.styleableThemeWindowActionBar);
        a.recycle();

        return result;
    }

    static class ThemeAttributes {
        private final static String CLASS_NAME = "android.support.v7.appcompat.R$styleable";
        private final int[] styleableTheme;
        private final int styleableThemeWindowActionBar;

        public ThemeAttributes() throws Exception {
            final Class<?> clazz = Class.forName(CLASS_NAME);
            Field field = clazz.getField("Theme");
            styleableTheme = (int[]) field.get(field.getType());

            field = clazz.getField("Theme_windowActionBar");
            styleableThemeWindowActionBar = (int) field.get(field.getType());
        }
    }
}
