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

import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.Session;
import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.internal.SessionVerifier;

import java.util.concurrent.ConcurrentHashMap;

class DigitsSessionVerifier implements SessionVerifier {
    private final VerificationCallback verificationCallback;

    DigitsSessionVerifier() {
        this(new VerificationCallback(new ConcurrentHashMap<SessionListener, Boolean>(),
                Digits.getSessionManager()));
    }

    DigitsSessionVerifier(VerificationCallback verificationCallback) {
        this.verificationCallback = verificationCallback;
    }

    @Override
    public void verifySession(final Session session) {
        if (session instanceof DigitsSession && !((DigitsSession) session).isLoggedOutUser()) {
            final ApiInterface service = getAccountService();
            service.verifyAccount(verificationCallback);
        }
    }

    ApiInterface getAccountService() {
        return Digits.getInstance().getApiClientManager().getApiClient().getService();
    }

    public void addSessionListener(SessionListener sessionListener) {
        verificationCallback.addSessionListener(sessionListener);
    }

    public void removeSessionListener(SessionListener sessionListener) {
        verificationCallback.removeSession(sessionListener);
    }


    static class VerificationCallback extends Callback<VerifyAccountResponse> {
        private final ConcurrentHashMap<SessionListener, Boolean> sessionListenerMap;
        private final SessionManager<DigitsSession> sessionManager;


        VerificationCallback(ConcurrentHashMap<SessionListener, Boolean>
                                     sessionListenerMap, SessionManager<DigitsSession>
                                     sessionManager) {
            this.sessionListenerMap = sessionListenerMap;
            this.sessionManager = sessionManager;
        }

        @Override
        public void success(Result<VerifyAccountResponse> result) {
            if (result.data != null) {
                final DigitsSession newSession = DigitsSession.create(result.data);
                if (newSession.isValidUser() &&
                        !newSession.equals(sessionManager.getSession(newSession.getId()))) {
                    sessionManager.setSession(newSession.getId(), newSession);
                    for (SessionListener listener : sessionListenerMap.keySet()) {
                        if (listener != null) {
                            listener.changed(newSession);
                        }
                    }
                }
            }
        }

        @Override
        public void failure(TwitterException exception) {
            //Ignore failure
        }

        void addSessionListener(SessionListener sessionListener) {
            if (sessionListener == null) {
                throw new NullPointerException("sessionListener must not be null");
            }
            sessionListenerMap.put(sessionListener, Boolean.TRUE);
        }

        public void removeSession(SessionListener sessionListener) {
            if (sessionListener == null) {
                throw new NullPointerException("sessionListener must not be null");
            }
            sessionListenerMap.remove(sessionListener);
        }
    }

}
