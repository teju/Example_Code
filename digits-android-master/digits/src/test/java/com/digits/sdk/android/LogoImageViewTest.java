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
import android.graphics.drawable.Drawable;
import android.os.Build;
import android.view.View;

import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.RuntimeEnvironment;
import org.robolectric.annotation.Config;
import org.robolectric.internal.ShadowExtractor;
import org.robolectric.shadows.ShadowDrawable;

import static org.junit.Assert.assertEquals;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class LogoImageViewTest {
    private static final int MAX_WIDTH = 100;
    private static final int ANY_HEIGHT = 10;

    @Test
    public void testConstructor_themeWithoutDrawable() throws Exception {
        final LogoImageView imageView = new LogoImageView(RuntimeEnvironment.application);
        assertEquals(View.GONE, imageView.getVisibility());
    }

    @Test
    public void testConstructor_themeWithDrawable() throws Exception {
        if (BuildConfig.DEBUG) {
            RuntimeEnvironment.application.setTheme(R.style.DigitsDebugLightTheme);
            final LogoImageView imageView = new LogoImageView(RuntimeEnvironment.application);
            final ShadowDrawable drawable = (ShadowDrawable) ShadowExtractor
                    .extract(imageView.getDrawable());
            assertEquals(R.drawable.dgts__logo, drawable.getCreatedFromResId());
            assertEquals(View.VISIBLE, imageView.getVisibility());
        }
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    @Test
    public void testDrawableKeepsAspectRatio() throws Exception {
        if (BuildConfig.DEBUG) {
            RuntimeEnvironment.application.setTheme(R.style.DigitsDebugLightTheme);
            final LogoImageView imageView = new LogoImageView(RuntimeEnvironment
                    .application);
            final Drawable drawable = imageView.getDrawable();
            imageView.onMeasure(MAX_WIDTH, ANY_HEIGHT);
            final int viewWidth = View.MeasureSpec.getSize(MAX_WIDTH);
            assertEquals(viewWidth, imageView.getMeasuredWidthAndState());
            assertEquals(viewWidth * drawable.getIntrinsicHeight() / drawable.getIntrinsicWidth(),
                    imageView.getMeasuredHeightAndState());
        }
    }
}
