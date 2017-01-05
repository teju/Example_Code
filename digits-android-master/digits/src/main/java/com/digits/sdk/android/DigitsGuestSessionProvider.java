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
import com.twitter.sdk.android.core.TwitterCore;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.internal.SessionProvider;
import com.twitter.sdk.android.core.internal.oauth.OAuth2Service;
import com.twitter.sdk.android.core.internal.oauth.OAuth2Token;

import java.util.List;

class DigitsGuestSessionProvider extends SessionProvider {
    final SessionManager<DigitsSession> defaultSessionManager;
    final OAuth2Service oAuth2Service;

    DigitsGuestSessionProvider(SessionManager<DigitsSession> defaultSessionManager,
            List<SessionManager<? extends Session>> sessionManagers) {
        this(defaultSessionManager, sessionManagers,
                new OAuth2Service(TwitterCore.getInstance(),
                        TwitterCore.getInstance().getSSLSocketFactory(), new DigitsApi()));
    }

    DigitsGuestSessionProvider(SessionManager<DigitsSession> defaultSessionManager,
            List<SessionManager<? extends Session>> sessionManagers, OAuth2Service oAuth2Service) {
        super(sessionManagers);

        this.defaultSessionManager = defaultSessionManager;
        this.oAuth2Service = oAuth2Service;
    }

    @Override
    public void requestAuth(Callback<Session> cb) {
        oAuth2Service.requestGuestAuthToken(new GuestAuthCallback(defaultSessionManager, cb));
    }

    /**
     * Callback to OAuth2Service wrapping a developer's requestGuestAuthToken callback
     */
    static class GuestAuthCallback extends Callback<OAuth2Token> {
        final SessionManager<DigitsSession> sessionManager;
        final Callback<Session> callback;

        GuestAuthCallback(SessionManager<DigitsSession> sessionManager,
                Callback<Session> callback) {
            this.sessionManager = sessionManager;
            this.callback = callback;
        }

        @Override
        public void success(Result<OAuth2Token> result) {
            final DigitsSession session = new DigitsSession(result.data);
            // set session in manager, manager makes session active if there is no active session
            sessionManager.setSession(session.getId(), session);
            if (callback != null) {
                callback.success(new Result<Session>(session, result.response));
            }
        }

        @Override
        public void failure(TwitterException exception) {
            if (callback != null) {
                callback.failure(exception);
            }
        }
    }
}
