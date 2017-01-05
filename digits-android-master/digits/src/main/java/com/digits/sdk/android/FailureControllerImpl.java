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
import android.app.Activity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.os.ResultReceiver;

class FailureControllerImpl implements FailureController {
    final ActivityClassManager classManager;

    public FailureControllerImpl() {
        this(Digits.getInstance().getActivityClassManager());
    }

    public FailureControllerImpl(ActivityClassManager classManager) {
        this.classManager = classManager;
    }

    public void tryAnotherNumber(Activity activity, ResultReceiver resultReceiver) {
        activity.finish();
    }

    public void sendFailure(ResultReceiver resultReceiver, Exception exception,
                            DigitsEventDetailsBuilder detailsBuilder) {
        final Bundle bundle = new Bundle();
        bundle.putString(LoginResultReceiver.KEY_ERROR, exception.getLocalizedMessage());
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, detailsBuilder);
        resultReceiver.send(LoginResultReceiver.RESULT_ERROR, bundle);
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    int getFlags() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
            return Intent.FLAG_ACTIVITY_CLEAR_TASK | Intent.FLAG_ACTIVITY_NEW_TASK;
        } else {
            return Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK;
        }
    }
}
