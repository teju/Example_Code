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

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.ActionBarActivity;

public abstract class DigitsActionBarActivity extends ActionBarActivity {

    static final int RESULT_FINISH_DIGITS = 200;
    static final int REQUEST_CODE = 140;

    DigitsActivityDelegate delegate;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        setTheme(Digits.getInstance().getTheme());
        super.onCreate(savedInstanceState);

        delegate = getActivityDelegate();
        final Bundle bundle = getIntent().getExtras();
        if (delegate.isValid(bundle)) {
            setContentView(delegate.getLayoutId());
            delegate.init(this, bundle);
        } else {
            finish();
            throw new IllegalAccessError("This activity can only be started from Digits");
        }
    }

    @Override
    public void onResume(){
        super.onResume();
        delegate.onResume();
    }

    @Override
    public void onDestroy(){
        delegate.onDestroy();
        super.onDestroy();
    }

    abstract DigitsActivityDelegate getActivityDelegate();

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        delegate.onActivityResult(requestCode, resultCode, this);
        if (resultCode == RESULT_FINISH_DIGITS && requestCode == REQUEST_CODE) {
            finish();
        }
    }
}
