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
import android.util.Log;

import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterApiException;
import com.twitter.sdk.android.core.TwitterSession;
import com.twitter.sdk.android.core.internal.TwitterApiConstants;

import io.fabric.sdk.android.Fabric;
import io.fabric.sdk.android.FabricTestUtils;
import io.fabric.sdk.android.KitStub;
import io.fabric.sdk.android.Logger;
import retrofit.RetrofitError;

import static org.mockito.Mockito.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

public class DigitsCallbackTests extends DigitsAndroidTestCase {
    private StubDigitsCallback digitsCallback;
    private DigitsController controller;
    private SessionManager<DigitsSession> sessionManager;
    private RetrofitError retrofitError;
    private TwitterApiException twitterApiException;

    @Override
    public void setUp() throws Exception {
        super.setUp();
        controller = mock(DigitsController.class);
        sessionManager = mock(SessionManager.class);
        retrofitError = mock(RetrofitError.class);
        digitsCallback = new StubDigitsCallback(controller, sessionManager);
        twitterApiException = mock(TwitterApiException.class);
        retrofitError = mock(RetrofitError.class);
        when(controller.getErrors()).thenReturn(mock(ErrorCodes.class));
        when(twitterApiException.getRetrofitError()).thenReturn(retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(false);
    }

    public void testFailure() throws Exception {
        try {
            final Logger mockLogger = mock(Logger.class);
            when(mockLogger.isLoggable(Digits.TAG, Log.ERROR)).thenReturn(true);
            when(twitterApiException.getErrorCode()).thenReturn(-1);
            when(twitterApiException.getErrorMessage()).thenReturn("");

            final Fabric fabric = new Fabric.Builder(getContext())
                    .kits(new KitStub())
                    .debuggable(false)
                    .logger(mockLogger)
                    .build();
            FabricTestUtils.with(fabric);

            digitsCallback.failure(twitterApiException);
            verify(controller).handleError(any(Context.class), any(DigitsException.class));
            verify(mockLogger).e(Digits.TAG, "HTTP Error: null, API Error: -1, User Message: null");
        } finally {
            FabricTestUtils.resetFabric();
        }
    }

    public void testFailure_guestAuthFailure() throws Exception {
        try {
            final Logger mockLogger = mock(Logger.class);
            when(mockLogger.isLoggable(Digits.TAG, Log.ERROR)).thenReturn(true);
            when(twitterApiException.getErrorCode())
                    .thenReturn(TwitterApiConstants.Errors.GUEST_AUTH_ERROR_CODE);
            when(twitterApiException.getErrorMessage()).thenReturn("");

            final Fabric fabric = new Fabric.Builder(getContext())
                    .kits(new KitStub())
                    .debuggable(false)
                    .logger(mockLogger)
                    .build();
            FabricTestUtils.with(fabric);

            digitsCallback.failure(twitterApiException);
            verify(controller).handleError(any(Context.class), any(DigitsException.class));
            verify(mockLogger).e(Digits.TAG,
                    DigitsConstants.GUEST_AUTH_REFRESH_LOG_MESSAGE);
            verify(sessionManager).clearSession(TwitterSession.LOGGED_OUT_USER_ID);
        } finally {
            FabricTestUtils.resetFabric();
        }
    }

    public void testSuccess() throws Exception {
        digitsCallback.success(null, null);
        assertTrue(digitsCallback.isCalled);
    }

    private class StubDigitsCallback extends DigitsCallback<String> {
        boolean isCalled = false;

        StubDigitsCallback(DigitsController controller,
                           SessionManager<DigitsSession> sessionManager) {
            super(null, controller, sessionManager);
        }

        @Override
        public void success(Result<String> result) {
            isCalled = true;
        }
    }
}
