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
import android.graphics.drawable.Drawable;
import android.util.AttributeSet;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.TextView;

public class StateButton extends RelativeLayout {

    TextView textView;
    ProgressBar progressBar;
    ImageView imageView;
    CharSequence progressText;
    CharSequence finishText;
    CharSequence startText;
    ButtonThemer buttonThemer;
    int accentColor;

    public StateButton(Context context) {
        this(context, null);
    }

    public StateButton(Context context, AttributeSet attrs) {
        this(context, attrs, 0);
    }

    public StateButton(Context context, AttributeSet attrs, int defStyle) {
        super(context, attrs, defStyle);
        initAttrs(context, attrs);
        initView(context);
    }

    void initAttrs(Context context, AttributeSet attrs) {
        final TypedArray array = context.obtainStyledAttributes(attrs, R.styleable.StateButton);

        startText = array.getText(R.styleable.StateButton_startStateText);
        progressText = array.getText(R.styleable.StateButton_progressStateText);
        finishText = array.getText(R.styleable.StateButton_finishStateText);
        initView();

        array.recycle();
    }

    void initView(Context context){
        accentColor = ThemeUtils.getAccentColor(getResources(), context.getTheme());
        buttonThemer = new ButtonThemer(getResources());
        buttonThemer.setBackgroundAccentColor(this, accentColor);
        buttonThemer.setTextAccentColor(textView, accentColor);
        setImageAccentColor();
        setSpinnerAccentColor();
    }

    void setImageAccentColor() {
        imageView.setColorFilter(getTextColor(), PorterDuff.Mode.SRC_IN);
    }

    void setSpinnerAccentColor() {
        progressBar.setIndeterminateDrawable(getProgressDrawable());
    }

    int getTextColor() {
        return buttonThemer.getTextColor(accentColor);
    }

    Drawable getProgressDrawable() {
        return ThemeUtils.isLightColor(accentColor) ? getResources().getDrawable(R.drawable
                .progress_dark) : getResources().getDrawable(R.drawable.progress_light);
    }

    public void setStatesText(int startResId, int progressResId, int finishResId) {
        final Context context = getContext();
        startText = context.getString(startResId);
        progressText = context.getString(progressResId);
        finishText = context.getString(finishResId);
    }

    void initView() {
        inflate(getContext(), R.layout.dgts__state_button, this);

        textView = (TextView) this.findViewById(R.id.dgts__state_button);
        progressBar = (ProgressBar) this.findViewById(R.id.dgts__state_progress);
        imageView = (ImageView) this.findViewById(R.id.dgts__state_success);

        showStart();
    }

    public void showProgress() {
        setClickable(false);
        textView.setText(progressText);
        progressBar.setVisibility(View.VISIBLE);
        imageView.setVisibility(View.GONE);
    }

    public void showFinish() {
        setClickable(false);
        textView.setText(finishText);
        progressBar.setVisibility(View.GONE);
        imageView.setVisibility(View.VISIBLE);
    }

    public void showError() {
        showStart();
    }

    public void showStart() {
        setClickable(true);
        textView.setText(startText);
        progressBar.setVisibility(View.GONE);
        imageView.setVisibility(View.GONE);
    }

    @Override
    public void setEnabled(boolean enabled) {
        super.setEnabled(enabled);
        textView.setEnabled(enabled);
        progressBar.setEnabled(enabled);
        imageView.setEnabled(enabled);
    }

}
