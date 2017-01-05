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


import android.content.res.Resources;
import android.util.SparseIntArray;

import com.twitter.sdk.android.core.TwitterApiErrorConstants;

/**
 * Common error messages used in digits SDK.
 */
class DigitsErrorCodes implements ErrorCodes {
    private static final int INITIAL_CAPACITY = 10;
    protected final SparseIntArray codeIdMap = new SparseIntArray(INITIAL_CAPACITY);

    {
        codeIdMap.put(TwitterApiErrorConstants.RATE_LIMIT_EXCEEDED,
                R.string.dgts__confirmation_error_alternative);
        codeIdMap.put(TwitterApiErrorConstants.REGISTRATION_GENERAL_ERROR,
                R.string.dgts__network_error);
        codeIdMap.put(TwitterApiErrorConstants.REGISTRATION_OPERATION_FAILED,
                R.string.dgts__network_error);
        codeIdMap.put(TwitterApiErrorConstants.SPAMMER, R.string.dgts__network_error);
        codeIdMap.put(TwitterApiErrorConstants.CLIENT_NOT_PRIVILEGED, R.string.dgts__network_error);

    }

    private final Resources resources;


    DigitsErrorCodes(Resources resources) {
        this.resources = resources;
    }


    @Override
    public String getMessage(int code) {
        final int idx = codeIdMap.indexOfKey(code);
        return idx < 0 ? getDefaultMessage() : resources.getString(codeIdMap.valueAt(idx));
    }

    @Override
    public String getDefaultMessage() {
        return resources.getString(R.string.dgts__try_again);
    }

    @Override
    public String getNetworkError() {
        return resources.getString(R.string.dgts__network_error);
    }
}
