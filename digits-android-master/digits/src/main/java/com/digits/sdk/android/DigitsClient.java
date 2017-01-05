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
import android.util.Base64;

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.Session;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterException;

import java.nio.charset.Charset;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

public class DigitsClient {
    public static final String EXTRA_PHONE = "phone_number";
    public static final String EXTRA_RESULT_RECEIVER = "receiver";
    public static final String EXTRA_REQUEST_ID = "request_id";
    public static final String EXTRA_USER_ID = "user_id";
    public static final String THIRD_PARTY_CONFIRMATION_CODE = "third_party_confirmation_code";
    public static final String EXTRA_FALLBACK_REASON = "fallback_reason";
    public static final String EXTRA_AUTH_CONFIG = "auth_config";
    public static final String EXTRA_EMAIL = "email_enabled";
    public static final String EXTRA_EVENT_DETAILS_BUILDER = "digits_event_details_builder";
    public static final String CLIENT_IDENTIFIER = "digits_sdk";

    private final DigitsAuthRequestQueue authRequestQueue;
    private final DigitsEventCollector digitsEventCollector;
    private final DigitsApiClientManager apiClientManager;
    private SandboxConfig sandboxConfig;
    private final Digits digits;
    private final SessionManager<DigitsSession> sessionManager;

    DigitsClient(DigitsApiClientManager apiClientManager) {
        this(Digits.getInstance(), Digits.getSessionManager(), apiClientManager,
                null, Digits.getInstance().getDigitsEventCollector(),
                Digits.getInstance().getSandboxConfig());
    }

    DigitsClient(Digits digits, SessionManager<DigitsSession> sessionManager,
                 DigitsApiClientManager apiClientManager,
                 DigitsAuthRequestQueue authRequestQueue,
                 DigitsEventCollector digitsEventCollector,
                 SandboxConfig sandboxConfig) {

        this.apiClientManager = apiClientManager;
        this.digits = digits;
        this.sessionManager = sessionManager;
        this.sandboxConfig = sandboxConfig;

        if (authRequestQueue == null) {
            this.authRequestQueue = createAuthRequestQueue(sessionManager);
            this.authRequestQueue.sessionRestored(null);
        } else {
            this.authRequestQueue = authRequestQueue;
        }
        this.digitsEventCollector = digitsEventCollector;
    }

    protected DigitsAuthRequestQueue createAuthRequestQueue(SessionManager sessionManager) {
        final List<SessionManager<? extends Session>> sessionManagers = new ArrayList<>(1);
        sessionManagers.add(sessionManager);
        final DigitsGuestSessionProvider sessionProvider =
                new DigitsGuestSessionProvider(sessionManager, sessionManagers);
        return new DigitsAuthRequestQueue(apiClientManager, sessionProvider);
    }

    protected void startSignUp(DigitsAuthConfig digitsAuthConfig) {
        final DigitsSession session = sessionManager.getActiveSession();
        final boolean isCustomPhoneUI = (digitsAuthConfig.confirmationCodeCallback != null);
        final boolean isAuthorizedPartner = isAuthorizedPartner(digitsAuthConfig);
        final DigitsEventDetailsBuilder details = new DigitsEventDetailsBuilder()
                .withAuthStartTime(System.currentTimeMillis())
                .withLanguage(Locale.getDefault().getLanguage())
                .withCurrentTime(System.currentTimeMillis());

        digitsEventCollector.authImpression(details.build());

        if (session != null && !session.isLoggedOutUser()) {
            final PhoneNumber phoneNumber = PhoneNumberUtils
                    .getPhoneNumber(digitsAuthConfig.phoneNumber);
            digitsEventCollector.authSuccess(details.withCountry(phoneNumber.getCountryIso())
                    .build());
            digitsAuthConfig.authCallback.success(session, session.getPhoneNumber());
        } else if (sandboxConfig.isMode(SandboxConfig.Mode.DEFAULT)) {
            final DigitsSession blackboxSession = MockApiInterface.createDigitsSession();
            digitsAuthConfig.authCallback.success(blackboxSession,
                    blackboxSession.getPhoneNumber());
        } else if (isCustomPhoneUI && isAuthorizedPartner) {
            sendConfirmationCode(digitsAuthConfig, details);
        } else if (isCustomPhoneUI) {
            throw new IllegalArgumentException("Invalid partner key");
        } else {
            startPhoneNumberActivity(createBundleForAuthFlow(digitsAuthConfig,
                    details));
        }
    }

    private boolean isAuthorizedPartner(DigitsAuthConfig digitsAuthConfig) {
        final String partnerKey = digitsAuthConfig.partnerKey;
        final String consumerKey =
                digits.getAuthConfig().getConsumerKey();
        final String expectedPartnerKey = getPartnerKeyByConsumerKey(consumerKey);
        return expectedPartnerKey.equals(partnerKey);
    }

    private String getPartnerKeyByConsumerKey(String consumerKey) {
        final String toEncode = "__Digits@P@rtner__" + consumerKey;
        return Base64.encodeToString(toEncode.getBytes(Charset.forName("UTF-8")), Base64.NO_WRAP);
    }

    private Bundle createBundleForAuthFlow(DigitsAuthConfig digitsAuthConfig,
                                           DigitsEventDetailsBuilder digitsEventDetailsBuilder) {
        final Bundle bundle = new Bundle();

        bundle.putParcelable(DigitsClient.EXTRA_RESULT_RECEIVER,
                createResultReceiver(digitsAuthConfig.authCallback));
        bundle.putString(DigitsClient.EXTRA_PHONE, digitsAuthConfig.phoneNumber);
        bundle.putBoolean(DigitsClient.EXTRA_EMAIL, digitsAuthConfig.isEmailRequired);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, digitsEventDetailsBuilder);
        return bundle;
    }

    LoginResultReceiver createResultReceiver(AuthCallback callback) {
        return new LoginResultReceiver(callback, sessionManager);
    }

    private void startPhoneNumberActivity(Bundle bundle) {
        final Context appContext = digits.getContext();
        final Activity currentActivity =
                digits.getFabric().getCurrentActivity();
        final Context selectedContext = (currentActivity != null && !currentActivity.isFinishing())
                        ? currentActivity : appContext;
        final int intentFlags = (currentActivity != null && !currentActivity.isFinishing())
                ? 0 : (Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);

        final Intent intent =
                new Intent(selectedContext, digits.getActivityClassManager()
                .getPhoneNumberActivity());
        intent.putExtras(bundle);
        intent.setFlags(intentFlags);
        selectedContext.startActivity(intent);
    }

    protected void authDevice(final String phoneNumber, final Verification verificationType,
        final Callback<AuthResponse> callback) {
        authRequestQueue.addClientRequest(new CallbackWrapper<AuthResponse>(callback) {

            @Override
            public void success(Result<ApiInterface> result) {
                result.data.auth(phoneNumber, verificationType.name(),
                        Locale.getDefault().getLanguage(), callback);
            }

        });
    }

    protected void createAccount(final String pin, final String phoneNumber,
            final Callback<DigitsUser> callback) {
        authRequestQueue.addClientRequest(new CallbackWrapper<DigitsUser>(callback) {

            @Override
            public void success(Result<ApiInterface> result) {
                result.data.account(phoneNumber, pin, callback);
            }

        });
    }

    protected void sendConfirmationCode(final DigitsAuthConfig digitsAuthConfig,
                                        DigitsEventDetailsBuilder outerDetails) {
        final PhoneNumber phoneNumber = PhoneNumberUtils
                .getPhoneNumber(digitsAuthConfig.phoneNumber);

        final DigitsEventDetailsBuilder details = outerDetails
                .withCountry(phoneNumber.getCountryIso())
                .withCurrentTime(System.currentTimeMillis());

        digitsEventCollector.submitClickOnPhoneScreen(details.build());

        final LoginOrSignupComposer signupAndLoginCombinedCallback =
                createCompositeCallback(digitsAuthConfig, details);

        signupAndLoginCombinedCallback.start();
    }

    LoginOrSignupComposer createCompositeCallback(final DigitsAuthConfig digitsAuthConfig,
                                                  final DigitsEventDetailsBuilder outerDetails) {
        final Context context = digits.getContext();
        final ActivityClassManager activityClassManager =
                Digits.getInstance().getActivityClassManager();

        return new LoginOrSignupComposer(context, this, sessionManager,
                digitsAuthConfig.phoneNumber, Verification.sms, digitsAuthConfig.isEmailRequired,
                createResultReceiver(digitsAuthConfig.authCallback), activityClassManager,
                outerDetails) {

            @Override
            public void success(final Intent intent) {
                final DigitsEventDetailsBuilder details =
                        outerDetails.withCurrentTime(System.currentTimeMillis());
                digitsEventCollector.submitPhoneSuccess(details.build());
                digitsAuthConfig.confirmationCodeCallback.success(intent);
            }

            @Override
            public void failure(DigitsException exception) {
                digitsEventCollector.submitPhoneFailure();
                digitsAuthConfig.confirmationCodeCallback.failure(exception);
            }
        };
    }

    protected void loginDevice(final String requestId, final long userId, final String code,
            final Callback<DigitsSessionResponse> callback) {
        authRequestQueue.addClientRequest(new CallbackWrapper<DigitsSessionResponse>(callback) {

            @Override
            public void success(Result<ApiInterface> result) {
                result.data.login(requestId, userId, code, callback);
            }

        });
    }

    protected void registerDevice(final String phoneNumber, final Verification verificationType,
                                  final Callback<DeviceRegistrationResponse> callback) {
        authRequestQueue.addClientRequest(
                new CallbackWrapper<DeviceRegistrationResponse>(callback) {

                    @Override
                    public void success(Result<ApiInterface> result) {
                        result.data.register(phoneNumber,
                                THIRD_PARTY_CONFIRMATION_CODE,
                                true, Locale.getDefault().getLanguage(), CLIENT_IDENTIFIER,
                                verificationType.name(), callback);
                    }

                });
    }

    protected void verifyPin(final String requestId, final long userId, final String pin,
            final Callback<DigitsSessionResponse> callback) {
        authRequestQueue.addClientRequest(new CallbackWrapper<DigitsSessionResponse>(callback) {

            @Override
            public void success(Result<ApiInterface> result) {
                result.data.verifyPin(requestId, userId, pin, callback);
            }

        });
    }

    static abstract class CallbackWrapper<T> extends Callback<ApiInterface> {
        final Callback<T> callback;

        public CallbackWrapper(Callback<T> callback) {
            this.callback = callback;
        }

        @Override
        public void failure(TwitterException exception) {
            if (callback != null) {
                callback.failure(exception);
            }
        }
    }
}
