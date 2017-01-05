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
import android.content.Intent;
import android.content.res.Resources;
import android.os.ResultReceiver;
import android.test.mock.MockContext;

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.MockDigitsApiException;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterApiErrorConstants;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.TwitterSession;
import com.twitter.sdk.android.core.internal.TwitterApiConstants;
import com.twitter.sdk.android.core.models.ApiError;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import retrofit.RetrofitError;

import static junit.framework.Assert.assertEquals;
import static junit.framework.Assert.assertNull;
import static junit.framework.Assert.assertTrue;
import static org.mockito.Matchers.any;
import static org.mockito.Matchers.anyString;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;


@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class LoginOrSignupComposerTest {
    Context context;
    DigitsClient digitsClient;
    SessionManager<DigitsSession> sessionManager;
    ResultReceiver resultReceiver;
    ActivityClassManager activityClassManager;
    ArgumentCaptor<Callback> callbackCaptor;
    AuthResponse authResponse;
    DeviceRegistrationResponse deviceRegistrationResponse;
    AuthConfig authConfig;
    Class loginCodeActivity;
    Class confirmationCodeActivity;
    RetrofitError retrofitError;
    TwitterException couldNotAuthenticateException;
    TwitterException userIsNotSdkUserException;
    TwitterException guestAuthException;
    TwitterException registrationRateExceededException;
    Resources resources;
    DigitsEventDetailsBuilder digitsEventDetailsBuilder;

    static final Integer COUNTRY_CODE = 123;
    static final String PHONE = "123456789";
    static final String PHONE_WITH_COUNTRY_CODE = "+" + COUNTRY_CODE + "123456789";
    static final String FAKE_REQUEST_ID = "sdfa";
    static final long FAKE_USER_ID = 123L;

    @Before
    public void setUp() throws Exception {
        context = mock(MockContext.class);
        digitsClient = mock(DigitsClient.class);
        sessionManager = mock(SessionManager.class);
        resultReceiver = mock(ResultReceiver.class);
        activityClassManager = mock(ActivityClassManager.class);
        callbackCaptor = ArgumentCaptor.forClass(Callback.class);
        loginCodeActivity = LoginCodeActivity.class;
        confirmationCodeActivity = ConfirmationCodeActivity.class;
        retrofitError = mock(RetrofitError.class);
        resources = mock(Resources.class);

        authConfig = new AuthConfig();
        authConfig.isEmailEnabled = true;
        authConfig.isVoiceEnabled = true;

        authResponse = new AuthResponse();
        authResponse.requestId = FAKE_REQUEST_ID;
        authResponse.userId = FAKE_USER_ID;
        authResponse.normalizedPhoneNumber = PHONE_WITH_COUNTRY_CODE;
        authResponse.authConfig = authConfig;

        deviceRegistrationResponse = new DeviceRegistrationResponse();
        deviceRegistrationResponse.authConfig = authConfig;
        deviceRegistrationResponse.normalizedPhoneNumber = PHONE_WITH_COUNTRY_CODE;
        digitsEventDetailsBuilder = new DigitsEventDetailsBuilder()
                .withAuthStartTime(System.currentTimeMillis());

        when(activityClassManager.getLoginCodeActivity()).thenReturn(loginCodeActivity);
        when(activityClassManager.getConfirmationActivity()).thenReturn(confirmationCodeActivity);
        when(context.getPackageName()).thenReturn(getClass().getPackage().toString());
        when(retrofitError.isNetworkError()).thenReturn(false);
        when(context.getResources()).thenReturn(resources);
        when(resources.getString(R.string.dgts__try_again)).thenReturn("try again");

        couldNotAuthenticateException = new MockDigitsApiException
                (new ApiError("Message", TwitterApiErrorConstants.COULD_NOT_AUTHENTICATE), null,
                        retrofitError);

        userIsNotSdkUserException = new MockDigitsApiException
                (new ApiError("Message", TwitterApiErrorConstants.USER_IS_NOT_SDK_USER), null,
                        retrofitError);

        registrationRateExceededException = new MockDigitsApiException
                (new ApiError("Message",
                        TwitterApiErrorConstants.DEVICE_REGISTRATION_RATE_EXCEEDED), null,
                        retrofitError);

        guestAuthException = new MockDigitsApiException
                (new ApiError("Message",
                        TwitterApiConstants.Errors.GUEST_AUTH_ERROR_CODE), null,
                        retrofitError);

    }

    @Test
    public void testLoginSuccess() {
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertExpectedLoginIntent(intent);
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertTrue(false);
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth success
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());
        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.success(new Result(authResponse, null));
    }

    @Test
    public void testSignupSuccess() {
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertExpectedSignupIntent(intent);
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertTrue(false);
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth failure
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());
        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.failure(couldNotAuthenticateException);

        //Simulate Signup success
        verify(digitsClient).registerDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());
        final Callback signupCallback = callbackCaptor.getValue();
        signupCallback.success(new Result(deviceRegistrationResponse, null));
    }

    @Test
    public void testLoginSuccess_authConfigNull() {
        authResponse.authConfig = null;
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertEquals(true, intent.getBooleanExtra(DigitsClient.EXTRA_EMAIL, false));
                assertNull(intent.getParcelableExtra(DigitsClient.EXTRA_AUTH_CONFIG));
                assertEquals(PHONE_WITH_COUNTRY_CODE,
                        intent.getStringExtra(DigitsClient.EXTRA_PHONE));
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertTrue(false);
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth success
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());
        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.success(new Result(authResponse, null));
    }

    @Test
    public void testLoginSuccess_normalizedPhoneNull() {
        authResponse.normalizedPhoneNumber = null;

        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertEquals(PHONE_WITH_COUNTRY_CODE,
                        intent.getStringExtra(DigitsClient.EXTRA_PHONE));
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertTrue(false);
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth success
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());
        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.success(new Result(authResponse, null));
    }


    @Test
    public void testLoginFailure() {
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertTrue(false);
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertEquals(TwitterApiErrorConstants.USER_IS_NOT_SDK_USER,
                        digitsException.getErrorCode());
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth failure
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());

        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.failure(userIsNotSdkUserException);
    }

    @Test
    public void testLoginGuestAuthFailure() {
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertTrue(false);
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertEquals(TwitterApiConstants.Errors.GUEST_AUTH_ERROR_CODE,
                        digitsException.getErrorCode());
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth failure
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());

        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.failure(guestAuthException);
        verify(sessionManager).clearSession(TwitterSession.LOGGED_OUT_USER_ID);
    }

    @Test
    public void testSignupFailure() {
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertTrue(false);
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertEquals(TwitterApiErrorConstants.DEVICE_REGISTRATION_RATE_EXCEEDED,
                        digitsException.getErrorCode());
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth failure
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());

        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.failure(couldNotAuthenticateException);

        //Simulate Signup failure
        verify(digitsClient).registerDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());

        final Callback signupCallback = callbackCaptor.getValue();
        signupCallback.failure(registrationRateExceededException);
    }

    @Test
    public void testSignupGuestAuthFailure() {
        LoginOrSignupComposer loginOrSignupComposer = new LoginOrSignupComposer(context,
                digitsClient, sessionManager, PHONE_WITH_COUNTRY_CODE, Verification.sms, true,
                resultReceiver, activityClassManager, digitsEventDetailsBuilder){
            @Override
            public void success(final Intent intent){
                assertTrue(false);
            }

            @Override
            public void failure(DigitsException digitsException) {
                assertEquals(TwitterApiConstants.Errors.GUEST_AUTH_ERROR_CODE,
                        digitsException.getErrorCode());
            }
        };

        loginOrSignupComposer.start();

        //Simulate Auth failure
        verify(digitsClient).authDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());

        final Callback loginCallback = callbackCaptor.getValue();
        loginCallback.failure(couldNotAuthenticateException);

        //Simulate Signup failure
        verify(digitsClient).registerDevice(anyString(), any(Verification.class),
                callbackCaptor.capture());

        final Callback signupCallback = callbackCaptor.getValue();
        signupCallback.failure(guestAuthException);
        verify(sessionManager).clearSession(TwitterSession.LOGGED_OUT_USER_ID);
    }

    private void assertExpectedLoginIntent(Intent intent){
        assertEquals(FAKE_REQUEST_ID, intent.getStringExtra(DigitsClient.EXTRA_REQUEST_ID));
        assertEquals(FAKE_USER_ID, intent.getLongExtra(DigitsClient.EXTRA_USER_ID, 0));
        assertEquals(resultReceiver,
                intent.getParcelableExtra(DigitsClient.EXTRA_RESULT_RECEIVER));
        assertEquals(PHONE_WITH_COUNTRY_CODE,
                intent.getStringExtra(DigitsClient.EXTRA_PHONE));
        assertEquals(digitsEventDetailsBuilder,
                intent.getParcelableExtra(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER));
        assertEquals(authConfig, intent.getParcelableExtra(DigitsClient.EXTRA_AUTH_CONFIG));
        assertEquals(true, intent.getBooleanExtra(DigitsClient.EXTRA_EMAIL, false));
        assertEquals(loginCodeActivity.getName(), intent.getComponent().getClassName());

    }

    private void assertExpectedSignupIntent(Intent intent){
        assertEquals(resultReceiver,
                intent.getParcelableExtra(DigitsClient.EXTRA_RESULT_RECEIVER));
        assertEquals(PHONE_WITH_COUNTRY_CODE,
                intent.getStringExtra(DigitsClient.EXTRA_PHONE));
        assertEquals(authConfig, intent.getParcelableExtra(DigitsClient.EXTRA_AUTH_CONFIG));
        assertEquals(true, intent.getBooleanExtra(DigitsClient.EXTRA_EMAIL, false));
        assertEquals(confirmationCodeActivity.getName(), intent.getComponent().getClassName());
    }
}
