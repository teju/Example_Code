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

import android.text.Editable;
import android.text.TextUtils;
import android.text.TextWatcher;
import android.widget.EditText;

import java.util.Collections;

class BucketedTextChangeListener implements TextWatcher {
    private final EditText editText;
    private final ContentChangeCallback callback;
    private final String[] postFixes;
    private final String placeHolder;
    private final int expectedContentLength;

    public BucketedTextChangeListener(EditText editText, int expectedContentLength,
                                      String placeHolder, ContentChangeCallback callback) {
        this.editText = editText;
        this.expectedContentLength = expectedContentLength;
        this.postFixes = generatePostfixArray(placeHolder, expectedContentLength);
        this.callback = callback;
        this.placeHolder = placeHolder;
    }

    @Override
    public void beforeTextChanged(CharSequence s, int start, int count, int after) {
    }

    @Override
    public void onTextChanged(CharSequence s, int ignoredParam1, int ignoredParam2,
                              int ignoredParam3) {
        final String contents = s.toString().replaceAll(" ", "");

        final int placeHolderStartingIndex = contents.indexOf(placeHolder);
        final int enteredContentLength = (placeHolderStartingIndex == -1) ?
                Math.min(contents.length(), expectedContentLength) : placeHolderStartingIndex;

        final String enteredContent = contents.substring(0, enteredContentLength);

        editText.removeTextChangedListener(this);
        editText.setText(enteredContent + postFixes[expectedContentLength - enteredContentLength]);
        editText.setSelection(enteredContentLength);
        editText.addTextChangedListener(this);

        if (enteredContentLength == expectedContentLength && callback != null){
            callback.whileComplete();
        } else if (callback != null) {
            callback.whileIncomplete();
        }
    }

    @Override
    public void afterTextChanged(Editable s) {
    }

    /**
     * {@link #generatePostfixArray(CharSequence, int)} with params ("-", 6) returns
     * {"", "-", "--", "---", "----", "-----", "------"}
     * @param repeatableChar
     * @param length
     * @return
     */
    private String[] generatePostfixArray(CharSequence repeatableChar, int length){
        final String[] ret = new String[length + 1];

        for (int i = 0; i <= length; i++) {
            ret[i] = TextUtils.join("", Collections.nCopies(i, repeatableChar));
        }

        return ret;
    }

    interface ContentChangeCallback {
        /**
         * Idempotent function invoked by the listener when the edit text changes and is of expected length
         */
        void whileComplete();

        /**
         * Idempotent function invoked by the listener when the edit text changes and is not of expected length
         */
        void whileIncomplete();
    }
}
