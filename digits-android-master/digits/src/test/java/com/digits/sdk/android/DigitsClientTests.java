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
import android.os.Bundle;
import android.os.ResultReceiver;
import android.test.mock.MockContext;

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.mockito.invocation.InvocationOnMock;
import org.mockito.stubbing.Answer;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.Locale;
import java.util.concurrent.ExecutorService;

import javax.net.ssl.SSLSocketFactory;

import io.fabric.sdk.android.Fabric;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotNull;
import static org.junit.Assert.fail;
import static org.mockito.Matchers.eq;
import static org.mockito.Mockito.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoMoreInteractions;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsClientTests {
    private static final String TYPE = "typetoken";
    private static final String ANY_REQUEST_ID = "1";
    private static final String ANY_CODE = "1";
    private static final String ANY_VERSION = "1.1";
    private static int TEST_NEW_TASK_INTENT_FLAGS =
            (Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
    private static int TEST_NO_INTENT_FLAGS = 0;
    private final SandboxConfig sandboxConfig = new SandboxConfig();

    private DigitsClient digitsClient;
    private Intent capturedIntent;
    private MockContext context;
    private Digits digits;
    private DigitsUserAgent digitsUserAgent;
    private TwitterCore twitterCore;
    private TwitterAuthConfig twitterAuthConfig;
    private SessionManager<DigitsSession> sessionManager;
    private ApiInterface service;
    private DigitsApiClientManager apiClientManager;
    private ExecutorService executorService;
    private DigitsController controller;
    private AuthCallback callback;
    private DigitsSession guestSession;
    private DigitsSession userSession;
    private DigitsApiClient digitsApiClient;
    private DigitsAuthRequestQueue authRequestQueue;
    private DigitsEventCollector digitsEventCollector;
    private Fabric fabric;
    private Activity activity;
    private LoginResultReceiver loginResultReceiver;
    private DigitsEventDetailsBuilder digitsEventDetailsBuilder;
    private ArgumentCaptor<DigitsEventDetails> detailsArgumentCaptor;

    @Before
    public void setUp() throws Exception {
        digits = mock(Digits.class);
        digitsUserAgent = new DigitsUserAgent("digitsVersion", "androidVersion", "appName");
        sessionManager = mock(SessionManager.class);
        twitterCore = mock(TwitterCore.class);
        twitterAuthConfig = new TwitterAuthConfig(TestConstants.CONSUMER_KEY,
                TestConstants.CONSUMER_SECRET);
        digitsApiClient = mock(DigitsApiClient.class);
        service = mock(ApiInterface.class);
        executorService = mock(ExecutorService.class);
        context = mock(MockContext.class);
        controller = mock(DigitsController.class);
        callback = mock(AuthCallback.class);
        digitsEventCollector = mock(DigitsEventCollector.class);
        fabric = mock(Fabric.class);
        activity = mock(Activity.class);
        loginResultReceiver = new LoginResultReceiver(new WeakAuthCallback(callback),
                sessionManager, mock(DigitsEventCollector.class));
        authRequestQueue = createRequestQueue();
        userSession = DigitsSession.create(TestConstants.DIGITS_USER, TestConstants.PHONE);
        guestSession = DigitsSession.create(TestConstants.LOGGED_OUT_USER, "");
        digitsEventDetailsBuilder = new DigitsEventDetailsBuilder()
                .withAuthStartTime(System.currentTimeMillis())
                .withCountry("US")
                .withLanguage("en");
        detailsArgumentCaptor = ArgumentCaptor.forClass(DigitsEventDetails.class);

        when(digitsApiClient.getService()).thenReturn(service);
        when(digits.getContext()).thenReturn(context);
        when(digits.getAuthConfig()).thenReturn(twitterAuthConfig);
        when(twitterCore.getSSLSocketFactory()).thenReturn(mock(SSLSocketFactory.class));
        when(digits.getExecutorService()).thenReturn(mock(ExecutorService.class));
        when(digits.getActivityClassManager()).thenReturn(new ActivityClassManagerImp());
        when(digits.getDigitsEventCollector()).thenReturn(digitsEventCollector);
        when(digits.getFabric()).thenReturn(fabric);
        when(digits.getVersion()).thenReturn(ANY_VERSION);
        when(fabric.getCurrentActivity()).thenReturn(activity);
        when(context.getPackageName()).thenReturn(getClass().getPackage().toString());
        when(activity.getPackageName()).thenReturn(getClass().getPackage().toString());
        when(activity.isFinishing()).thenReturn(false);
        when(controller.getErrors()).thenReturn(mock(ErrorCodes.class));
        apiClientManager = mock(DigitsApiClientManager.class);
        when(apiClientManager.getService()).thenReturn(service);
        when(apiClientManager.getApiClient()).thenReturn(digitsApiClient);

        digitsClient = new DigitsClient(digits, sessionManager, apiClientManager,
                authRequestQueue, digitsEventCollector, sandboxConfig) {
            @Override
            LoginResultReceiver createResultReceiver(AuthCallback callback) {
                return loginResultReceiver;
            }
        };
    }


    @Test
    public void testStartSignUp_withActivityContext() throws Exception {
        verifySignUp(activity, callback, TEST_NO_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_withAppContext() throws Exception {
        when(fabric.getCurrentActivity()).thenReturn(null);
        verifySignUp(context, callback, TEST_NEW_TASK_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_whenActivityContextIsFinishing() throws Exception {
        when(activity.isFinishing()).thenReturn(true);
        verifySignUp(context, callback, TEST_NEW_TASK_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_withPhoneAndActivityContext() throws Exception {
        verifySignUpWithProvidedPhone(activity, callback, TestConstants.PHONE,
                TestConstants.ANY_BOOLEAN, TEST_NO_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_withPhoneAndAppContext() throws Exception {
        when(fabric.getCurrentActivity()).thenReturn(null);
        verifySignUpWithProvidedPhone(context, callback, TestConstants.PHONE,
                TestConstants.ANY_BOOLEAN, TEST_NEW_TASK_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_withNullPhone() throws Exception {
        verifySignUpWithProvidedPhone(activity, callback, null,
                TestConstants.ANY_BOOLEAN, TEST_NO_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_nullListener() throws Exception {
        try {
            verifySignUp(activity, null, TEST_NO_INTENT_FLAGS);
            fail("Should throw IllegalArgumentException");
        } catch (IllegalArgumentException ex) {
            assertEquals("AuthCallback must not be null",
                    ex.getMessage());
        }
    }

    @Test
    public void testStartSignUp_nullListenerWithPhone() throws Exception {
        try {
            verifySignUpWithProvidedPhone(activity, null, TestConstants.PHONE,
                    TestConstants.ANY_BOOLEAN, TEST_NO_INTENT_FLAGS);
            fail("Should throw IllegalArgumentException");
        } catch (IllegalArgumentException ex) {
            assertEquals("AuthCallback must not be null",
                    ex.getMessage());
        }
    }

    @Test
    public void testStartSignUp_callbackSuccess() throws Exception {
        final DigitsAuthConfig configWithCallback = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withPhoneNumber(TestConstants.PHONE)
                .build();
        when(sessionManager.getActiveSession()).thenReturn(userSession);
        digitsClient.startSignUp(configWithCallback);
        verify(digitsEventCollector).authImpression(detailsArgumentCaptor.capture());
        DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);

        verify(digitsEventCollector).authSuccess(detailsArgumentCaptor.capture());
        digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(callback).success(userSession, TestConstants.PHONE);
    }

    @Test
    public void testStartSignUp_callbackSuccessWithPhone() throws Exception {
        final DigitsAuthConfig configWithPhone = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withEmailCollection(TestConstants.ANY_BOOLEAN)
                .build();
        when(sessionManager.getActiveSession()).thenReturn(userSession);
        digitsClient.startSignUp(configWithPhone);
        verify(digitsEventCollector).authImpression(detailsArgumentCaptor.capture());
        DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);

        verify(digitsEventCollector).authSuccess(detailsArgumentCaptor.capture());
        digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.country);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(callback).success(userSession, TestConstants.PHONE);
    }

    @Test
    public void testStartSignUp_loggedOutUser() throws Exception {
        when(sessionManager.getActiveSession()).thenReturn(guestSession);
        verifySignUp(activity, callback, TEST_NO_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_loggedOutUserWithPhone() throws Exception {
        when(sessionManager.getActiveSession()).thenReturn(guestSession);
        verifySignUpWithProvidedPhone(activity, callback, TestConstants.PHONE,
                TestConstants.ANY_BOOLEAN, TEST_NO_INTENT_FLAGS);
        verifyCallbackInReceiver(callback);
    }

    @Test
    public void testStartSignUp_withCustomPhoneUIAndBadPartnerKeythrowsException()
            throws Exception {
        try {
            verifySignUpWithCustomPhoneUIAndBadPartnerKey(activity, callback, TestConstants.PHONE,
                    TestConstants.ANY_BOOLEAN, TEST_NO_INTENT_FLAGS);
            fail("Should throw IllegalArgumentException");
        } catch (IllegalArgumentException ex) {
            assertEquals("Invalid partner key",
                    ex.getMessage());
        }
    }

    @Test
    public void testStartSignUp_withCustomPhoneUIAndPartnerKey()
            throws Exception {
        verifySignUpWithCustomPhoneUIAndPartnerKey(activity, callback, TestConstants.PHONE,
                TestConstants.ANY_BOOLEAN, TEST_NO_INTENT_FLAGS);
    }

    public void testCreateCompositeCallback_success() {
        final ConfirmationCodeCallback confirmationCodeCallback =
                mock(ConfirmationCodeCallback.class);

        final DigitsAuthConfig digitsAuthConfig = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withEmailCollection()
                .withPhoneNumber(TestConstants.PHONE)
                .withCustomPhoneNumberScreen(confirmationCodeCallback).build();

        final LoginOrSignupComposer loginOrSignupComposer =
                digitsClient.createCompositeCallback(digitsAuthConfig, digitsEventDetailsBuilder);
        final Intent intent  = mock(Intent.class);

        loginOrSignupComposer.success(intent);
        verify(digitsEventCollector).submitPhoneSuccess(detailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(confirmationCodeCallback).success(intent);
    }

    public void testCreateCompositeCallback_failure() {
        final ConfirmationCodeCallback confirmationCodeCallback =
                mock(ConfirmationCodeCallback.class);

        final DigitsAuthConfig digitsAuthConfig = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withEmailCollection()
                .withPhoneNumber(TestConstants.PHONE)
                .withCustomPhoneNumberScreen(confirmationCodeCallback).build();

        final LoginOrSignupComposer loginOrSignupComposer =
                digitsClient.createCompositeCallback(digitsAuthConfig, digitsEventDetailsBuilder);
        final Intent intent  = mock(Intent.class);
        loginOrSignupComposer.failure(TestConstants.ANY_EXCEPTION);
        verify(digitsEventCollector).submitPhoneFailure();
        verify(confirmationCodeCallback).failure(TestConstants.ANY_EXCEPTION);
    }


    @Test
    public void testAuthDevice_withSmsVerification() throws Exception {
        final Callback listener = mock(Callback.class);
        digitsClient.authDevice(TestConstants.PHONE, Verification.sms, listener);

        verify(service).auth(eq(TestConstants.PHONE), eq(Verification.sms.name()),
                eq(Locale.getDefault().getLanguage()), eq(listener));
    }

    @Test
    public void testAuthDevice_withVoiceVerification() throws Exception {
        final Callback listener = mock(Callback.class);
        digitsClient.authDevice(TestConstants.PHONE, Verification.voicecall, listener);

        verify(service).auth(eq(TestConstants.PHONE), eq(Verification.voicecall.name()),
                eq(Locale.getDefault().getLanguage()), eq(listener));
    }

    @Test
    public void testRegisterDevice_withSmsVerification() throws Exception {
        final Callback listener = mock(Callback.class);
        digitsClient.registerDevice(TestConstants.PHONE, Verification.sms, listener);
        verify(service).register(TestConstants.PHONE,
                DigitsClient.THIRD_PARTY_CONFIRMATION_CODE, true, Locale.getDefault().getLanguage(),
                DigitsClient.CLIENT_IDENTIFIER, Verification.sms.name(), listener);
    }

    @Test
    public void testRegisterDevice_withVoiceVerification() throws Exception {
        final Callback listener = mock(Callback.class);
        digitsClient.registerDevice(TestConstants.PHONE, Verification.voicecall, listener);
        verify(service).register(TestConstants.PHONE,
                DigitsClient.THIRD_PARTY_CONFIRMATION_CODE, true, Locale.getDefault().getLanguage(),
                DigitsClient.CLIENT_IDENTIFIER, Verification.voicecall.name(), listener);
    }

    @Test
    public void testLoginDevice() throws Exception {
        final Callback listener = mock(Callback.class);
        digitsClient.loginDevice(ANY_REQUEST_ID, TestConstants.USER_ID, ANY_CODE, listener);
        verify(service).login(eq(ANY_REQUEST_ID), eq(TestConstants.USER_ID), eq(ANY_CODE),
                eq(listener));
    }

    @Test
    public void testVerifyPin() throws Exception {
        final Callback listener = mock(Callback.class);
        digitsClient.verifyPin(ANY_REQUEST_ID, TestConstants.USER_ID, ANY_CODE, listener);
        verify(service).verifyPin(eq(ANY_REQUEST_ID), eq(TestConstants.USER_ID), eq(ANY_CODE),
                eq(listener));
    }

    private void verifyCallbackInReceiver(AuthCallback expected) {
        final LoginResultReceiver receiver = capturedIntent.getParcelableExtra(DigitsClient
                .EXTRA_RESULT_RECEIVER);
        assertEquals(expected, receiver.callback.getCallback());
    }

    private void verifySignUp(Context context, AuthCallback callback, int expectedFlags) {
        final DigitsAuthConfig configWithCallback = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .build();
        final Bundle expectedBundle = createBundle(loginResultReceiver, "", false);

        digitsClient.startSignUp(configWithCallback);
        verify(digitsEventCollector).authImpression(detailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verifyNoMoreInteractions(digitsEventCollector);
        final ArgumentCaptor<Intent> argument = ArgumentCaptor.forClass(Intent.class);
        //verify start activity is called, passing an ArgumentCaptor to get the intent and check
        // if it's correctly build
        verify(context).startActivity(argument.capture());
        capturedIntent = argument.getValue();
        assertEquals(expectedFlags, capturedIntent.getFlags());
        assertBundleEquals(expectedBundle, capturedIntent.getExtras());
        final DigitsEventDetails actualDigitsEventDetails =
                ((DigitsEventDetailsBuilder) capturedIntent
                        .getParcelableExtra(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER)).build();
        assertNotNull(actualDigitsEventDetails.elapsedTimeInMillis);
        assertNotNull(actualDigitsEventDetails.language);
    }

    private void verifySignUpWithProvidedPhone(Context context, AuthCallback callback, String phone,
                                               boolean emailCollection, int expectedFlags) {
        final DigitsAuthConfig digitsAuthConfig = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withEmailCollection(emailCollection)
                .withPhoneNumber(phone)
                .build();

        final Bundle expectedBundle = createBundle(loginResultReceiver, phone, emailCollection);

        digitsClient.startSignUp(digitsAuthConfig);
        verify(digitsEventCollector).authImpression(detailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verifyNoMoreInteractions(digitsEventCollector);
        final ArgumentCaptor<Intent> argument = ArgumentCaptor.forClass(Intent.class);
        //verify start activity is called, passing an ArgumentCaptor to get the intent and check
        // if it's correctly build
        verify(context).startActivity(argument.capture());
        capturedIntent = argument.getValue();
        assertEquals(expectedFlags, capturedIntent.getFlags());
        assertBundleEquals(expectedBundle, capturedIntent.getExtras());
        final DigitsEventDetails actualDigitsEventDetails =
                ((DigitsEventDetailsBuilder) capturedIntent
                        .getParcelableExtra(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER)).build();
        assertNotNull(actualDigitsEventDetails.elapsedTimeInMillis);
        assertNotNull(actualDigitsEventDetails.language);
    }

    private void verifySignUpWithCustomPhoneUIAndBadPartnerKey(Context context,
                                                               AuthCallback callback, String phone,
                                                               boolean emailCollection,
                                                               int expectedFlags) {
        final ConfirmationCodeCallback confirmationCodeCallback =
                mock(ConfirmationCodeCallback.class);

        final DigitsAuthConfig digitsAuthConfig = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withEmailCollection(emailCollection)
                .withPhoneNumber(phone)
                .withPartnerKey("bad_key")
                .withCustomPhoneNumberScreen(confirmationCodeCallback).build();

        final MockDigitsClient digitsClient = new MockDigitsClient(digits, sessionManager,
                apiClientManager,
                authRequestQueue,
                digitsEventCollector) {
            @Override
            LoginResultReceiver createResultReceiver(AuthCallback callback) {
                return loginResultReceiver;
            }
        };

        digitsClient.startSignUp(digitsAuthConfig);
        verify(digitsEventCollector).authImpression(detailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verifyNoMoreInteractions(digitsEventCollector);
        verifyNoMoreInteractions(digitsClient.loginOrSignupComposer);
    }

    private void verifySignUpWithCustomPhoneUIAndPartnerKey(Context context, AuthCallback callback,
                                                            String phone, boolean emailCollection,
                                                            int expectedFlags) {
        final ConfirmationCodeCallback confirmationCodeCallback =
                mock(ConfirmationCodeCallback.class);

        final DigitsAuthConfig digitsAuthConfig = new DigitsAuthConfig.Builder()
                .withAuthCallBack(callback)
                .withEmailCollection(emailCollection)
                .withPhoneNumber(phone)
                .withPartnerKey(TestConstants.PARTNER_KEY)
                .withCustomPhoneNumberScreen(confirmationCodeCallback).build();

        final MockDigitsClient digitsClient = new MockDigitsClient(digits, sessionManager,
                apiClientManager,
                authRequestQueue, digitsEventCollector) {
            @Override
            LoginResultReceiver createResultReceiver(AuthCallback callback) {
                return loginResultReceiver;
            }
        };

        digitsClient.startSignUp(digitsAuthConfig);
        verify(digitsEventCollector).authImpression(detailsArgumentCaptor.capture());
        final DigitsEventDetails digitsEventDetails = detailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(digitsEventCollector)
                .submitClickOnPhoneScreen(detailsArgumentCaptor.capture());
        assertNotNull(digitsEventDetails.elapsedTimeInMillis);
        verify(digitsClient.loginOrSignupComposer).start();
    }

    private DigitsAuthRequestQueue createRequestQueue() {
        final DigitsAuthRequestQueue authRequestQueue = mock(DigitsAuthRequestQueue.class);
        when(authRequestQueue.addClientRequest(any(Callback.class))).thenAnswer(
                new Answer<Object>() {
                    @Override
                    public Object answer(InvocationOnMock invocation) throws Throwable {
                        ((Callback<ApiInterface>) invocation.getArguments()[0])
                                .success(service, null);
                        return null;
                    }
                }
        );
        return authRequestQueue;
    }

    private Bundle createBundle(LoginResultReceiver loginResultReceiver,
                                String defaultPhone, Boolean emailRequired){
        final Bundle bundle = new Bundle();

        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER, loginResultReceiver);
        bundle.putString(DigitsClient.EXTRA_PHONE, defaultPhone == null ? "": defaultPhone);
        bundle.putBoolean(DigitsClient.EXTRA_EMAIL, emailRequired);

        return bundle;
    }

    private void assertBundleEquals(Bundle expectedBundle, Bundle actualBundle){
        assertEquals(expectedBundle.get(DigitsClient.EXTRA_RESULT_RECEIVER),
                actualBundle.get(DigitsClient.EXTRA_RESULT_RECEIVER));
        assertEquals(expectedBundle.get(DigitsClient.EXTRA_PHONE),
                actualBundle.get(DigitsClient.EXTRA_PHONE));
        assertEquals(expectedBundle.get(DigitsClient.EXTRA_EMAIL),
                actualBundle.get(DigitsClient.EXTRA_EMAIL));
    }

    public class MockDigitsClient extends DigitsClient{
        LoginOrSignupComposer loginOrSignupComposer;

        public MockDigitsClient(Digits digits, SessionManager<DigitsSession> sessionManager,
                                DigitsApiClientManager apiClientManager,
                                DigitsAuthRequestQueue authRequestQueue,
                                DigitsEventCollector digitsEventCollector) {
            super(digits, sessionManager, apiClientManager,
                    authRequestQueue,
                    digitsEventCollector, sandboxConfig);
            this.loginOrSignupComposer = mock(DummyLoginOrSignupComposer.class);
        }

        @Override
        LoginOrSignupComposer createCompositeCallback(final DigitsAuthConfig digitsAuthConfig,
                                                      DigitsEventDetailsBuilder details
        ) {
            return this.loginOrSignupComposer;
        }

        protected abstract class DummyLoginOrSignupComposer extends LoginOrSignupComposer {
            DummyLoginOrSignupComposer(Context context, DigitsClient digitsClient,
                                       SessionManager<DigitsSession> sessionManager,
                                       String phoneNumber, Verification verificationType,
                                       boolean emailCollection, ResultReceiver resultReceiver,
                                       ActivityClassManager activityClassManager,
                                       DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
                super(context, digitsClient, sessionManager, phoneNumber, verificationType,
                        emailCollection, resultReceiver, activityClassManager,
                        digitsEventDetailsBuilder);
            }
        }
    }
}
