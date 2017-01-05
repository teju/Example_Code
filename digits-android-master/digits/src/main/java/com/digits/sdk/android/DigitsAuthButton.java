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
import android.content.res.Resources;
import android.graphics.Typeface;
import android.util.AttributeSet;
import android.util.TypedValue;
import android.view.View;
import android.widget.Button;

/**
 * Digits authentication button
 * <p>
 * An {@link AuthCallback} must be set by calling
 * {@code #setCallback}
 * <p>
 * When the button is clicked the auth flows will start
 */
public class DigitsAuthButton extends Button implements View.OnClickListener {
    volatile DigitsClient digitsClient;
    private OnClickListener onClickListener;
    private DigitsAuthConfig.Builder digitsAuthConfigBuilder;

    public DigitsAuthButton(Context context) {
        this(context, null);
    }

    public DigitsAuthButton(Context context, AttributeSet attrs) {
        this(context, attrs, android.R.attr.buttonStyle);
    }

    public DigitsAuthButton(Context context, AttributeSet attrs, int defStyle) {
        super(context, attrs, defStyle);
        setUpButton();
        digitsAuthConfigBuilder = new DigitsAuthConfig.Builder();
        super.setOnClickListener(this);
    }

    private void setUpButton() {
        final Resources res = getResources();
        setCompoundDrawablePadding(
                res.getDimensionPixelSize(R.dimen.tw__login_btn_drawable_padding));
        setText(R.string.dgts__login_digits_text);
        setTextColor(res.getColor(R.color.tw__solid_white));
        setTextSize(TypedValue.COMPLEX_UNIT_PX,
                res.getDimensionPixelSize(R.dimen.tw__login_btn_text_size));
        setTypeface(Typeface.DEFAULT_BOLD);
        setPadding(res.getDimensionPixelSize(R.dimen.tw__login_btn_right_padding), 0,
                res.getDimensionPixelSize(R.dimen.tw__login_btn_right_padding), 0);
        setBackgroundResource(R.drawable.dgts__digits_btn);
    }

    @Override
    public void onClick(View v) {
        final DigitsAuthConfig digitsAuthConfig = digitsAuthConfigBuilder.build();
        getDigitsClient().startSignUp(digitsAuthConfig);

        if (onClickListener != null) {
            onClickListener.onClick(v);
        }
    }

    /**
     * Sets the AuthCallback that will receive the result of the authentication process
     */
    public void setCallback(AuthCallback callback) {
        digitsAuthConfigBuilder.withAuthCallBack(callback);
    }

    /**
     * Sets the theme for the authentication flow
     * @param themeResId resource id for the theme to set
     */
    public void setAuthTheme(int themeResId) {
        getDigits().setTheme(themeResId);
    }

    @Override
    public void setOnClickListener(OnClickListener l) {
        onClickListener = l;
    }

    protected DigitsClient getDigitsClient() {
        if (digitsClient == null) {
            synchronized (DigitsClient.class) {
                if (digitsClient == null) {
                    digitsClient = getDigits().getDigitsClient();
                }
            }
        }
        return digitsClient;
    }

    protected Digits getDigits() {
        return Digits.getInstance();
    }
}

