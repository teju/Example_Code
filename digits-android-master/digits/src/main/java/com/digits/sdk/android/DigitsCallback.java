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

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.TwitterSession;

import java.lang.ref.WeakReference;

import io.fabric.sdk.android.Fabric;

public abstract class DigitsCallback<T> extends Callback<T> {
    final DigitsController controller;
    private final WeakReference<Context> context;
    private final SessionManager<DigitsSession> digitsSessionManager;

    DigitsCallback(Context context, DigitsController controller,
                   SessionManager<DigitsSession> digitsSessionManager) {
        this.context = new WeakReference<>(context);
        this.controller = controller;
        this.digitsSessionManager = digitsSessionManager;
    }

    @Override
    public void failure(TwitterException exception) {
        final DigitsException digitsException = DigitsException.create(controller.getErrors(),
                exception);

        if ((digitsException instanceof AppAuthErrorException
                || digitsException instanceof GuestAuthErrorException)) {
            if (digitsSessionManager != null) {
                Fabric.getLogger().e(Digits.TAG, DigitsConstants.GUEST_AUTH_REFRESH_LOG_MESSAGE);
                digitsSessionManager.clearSession(TwitterSession.LOGGED_OUT_USER_ID);
            }
        }

        Fabric.getLogger().e(Digits.TAG, "HTTP Error: " + exception.getMessage() + ", API Error: " +
                "" + digitsException.getErrorCode() + ", User Message: " + digitsException
                .getMessage());
        controller.handleError(context.get(), digitsException);
    }
}
