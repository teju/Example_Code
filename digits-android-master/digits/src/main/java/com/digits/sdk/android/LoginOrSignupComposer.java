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

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Parcelable;
import android.os.ResultReceiver;

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.TwitterSession;

import io.fabric.sdk.android.Fabric;

abstract class LoginOrSignupComposer {
    final private Context context;
    protected final Callback<DeviceRegistrationResponse> deviceRegCallback;
    protected final Callback<AuthResponse> loginCallback;
    final DigitsClient digitsClient;
    private final SessionManager<DigitsSession> sessionManager;
    final String phoneNumber;
    final Verification verificationType;
    final boolean emailCollection;
    final ResultReceiver resultReceiver;
    final ActivityClassManager activityClassManager;
    final DigitsEventDetailsBuilder eventDetailsBuilder;

    LoginOrSignupComposer(final Context context, final DigitsClient digitsClient,
                          final SessionManager<DigitsSession> sessionManager,
                          final String phoneNumber, final Verification verificationType,
                          boolean emailCollection, ResultReceiver resultReceiver,
                          ActivityClassManager activityClassManager,
                          DigitsEventDetailsBuilder eventDetailsBuilder) {
        this.context = context;
        this.digitsClient = digitsClient;
        this.phoneNumber = phoneNumber;
        this.verificationType = verificationType;
        this.emailCollection = emailCollection;
        this.resultReceiver = resultReceiver;
        this.activityClassManager = activityClassManager;
        this.eventDetailsBuilder = eventDetailsBuilder;
        this.sessionManager = sessionManager;

        loginCallback = new Callback<AuthResponse>() {
            @Override
            public void success(Result<AuthResponse> result) {
                LoginOrSignupComposer.this.success(createIntentToLogin(result.data));
            }

            @Override
            public void failure(TwitterException twitterException) {
                final DigitsException digitsException = createDigitsException(twitterException);
                Fabric.getLogger().e(Digits.TAG, "HTTP Error: " + twitterException.getMessage() +
                        ", API Error: " + "" + digitsException.getErrorCode() + ", User Message: "
                        + digitsException.getMessage());
                if (digitsException instanceof CouldNotAuthenticateException) {
                    beginRegistration();
                } else if (digitsException instanceof AppAuthErrorException
                        || digitsException instanceof GuestAuthErrorException) {
                    Fabric.getLogger().e(Digits.TAG,
                            DigitsConstants.GUEST_AUTH_REFRESH_LOG_MESSAGE);
                    clearGuestSession(LoginOrSignupComposer.this.sessionManager);
                    LoginOrSignupComposer.this.failure(digitsException);
                } else {
                    LoginOrSignupComposer.this.failure(digitsException);
                }
            }
        };

        deviceRegCallback = new Callback<DeviceRegistrationResponse>() {
            @Override
            public void success(Result<DeviceRegistrationResponse> result) {
                LoginOrSignupComposer.this.success(createIntentToSignup(result.data));
            }

            @Override
            public void failure(TwitterException twitterException) {
                final DigitsException digitsException = createDigitsException(twitterException);
                if (digitsException instanceof AppAuthErrorException
                        || digitsException instanceof GuestAuthErrorException){
                    Fabric.getLogger().e(Digits.TAG, "Refreshing guest auth token");
                    clearGuestSession(LoginOrSignupComposer.this.sessionManager);
                }
                Fabric.getLogger().e(Digits.TAG, "HTTP Error: " + twitterException.getMessage() +
                        ", API Error: " + "" + digitsException.getErrorCode() + ", User Message: "
                        + digitsException.getMessage());
                LoginOrSignupComposer.this.failure(digitsException);
            }
        };
    }

    private void clearGuestSession(SessionManager<DigitsSession> sessionManager) {
        if (sessionManager != null) {
            Fabric.getLogger().e(Digits.TAG, DigitsConstants.GUEST_AUTH_REFRESH_LOG_MESSAGE);
            sessionManager.clearSession(TwitterSession.LOGGED_OUT_USER_ID);
        }
    }

    public abstract void success(final Intent intent);

    public abstract void failure(final DigitsException exception);

    public void start() {
        beginLogin();
    }

    private void beginRegistration() {
        digitsClient.registerDevice(phoneNumber, verificationType, this.deviceRegCallback);
    }

    private void beginLogin() {
        digitsClient.authDevice(phoneNumber, verificationType, this.loginCallback);
    }

    private Intent createIntentToSignup(DeviceRegistrationResponse response) {
        //signup requires only the default bundle
        return createBundle(response.authConfig, response.normalizedPhoneNumber,
                activityClassManager.getConfirmationActivity());
    }

    private Intent createIntentToLogin(AuthResponse response) {
        final Intent intent = createBundle(response.authConfig, response.normalizedPhoneNumber,
                activityClassManager.getLoginCodeActivity());
        intent.putExtra(DigitsClient.EXTRA_REQUEST_ID, response.requestId);
        intent.putExtra(DigitsClient.EXTRA_USER_ID, response.userId);

        return intent;
    }

    private Intent createBundle(AuthConfig config, String normalizedPhoneNumber,
                                Class<? extends Activity> activityClass) {
        final boolean emailCollection = config == null ? this.emailCollection :
                config.isEmailEnabled && this.emailCollection;
        final String phoneNumber = normalizedPhoneNumber == null ? this.phoneNumber :
                normalizedPhoneNumber;
        final Intent intent = new Intent(context, activityClass);

        intent.putExtra(DigitsClient.EXTRA_RESULT_RECEIVER, resultReceiver);
        intent.putExtra(DigitsClient.EXTRA_PHONE, phoneNumber);
        intent.putExtra(DigitsClient.EXTRA_AUTH_CONFIG, (Parcelable) config);
        intent.putExtra(DigitsClient.EXTRA_EMAIL, emailCollection);
        intent.putExtra(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, eventDetailsBuilder);

        return intent;
    }

    private DigitsException createDigitsException(TwitterException exception) {
        final PhoneNumberErrorCodes confirmationErrorCodes =
                new PhoneNumberErrorCodes(LoginOrSignupComposer.this.context.getResources());

        return DigitsException.create(
                confirmationErrorCodes, exception);
    }
}
