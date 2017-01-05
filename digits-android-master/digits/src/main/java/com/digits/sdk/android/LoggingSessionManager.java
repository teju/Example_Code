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

import com.twitter.sdk.android.core.SessionManager;

import java.util.Locale;
import java.util.Map;

public class LoggingSessionManager implements SessionManager<DigitsSession> {
    private SessionManager<DigitsSession> sessionManager;
    DigitsEventCollector digitsEventCollector;

    LoggingSessionManager(SessionManager<DigitsSession> sessionManager,
                          DigitsEventCollector digitsEventCollector) {
        this.sessionManager = sessionManager;
        this.digitsEventCollector = digitsEventCollector;
    }

    /**
     * @return the active session, restoring saved session if available
     */
    @Override
    public DigitsSession getActiveSession() {
        return sessionManager.getActiveSession();
    }

    /**
     * Sets the active session.
     *
     * @param session
     */
    @Override
    public void setActiveSession(DigitsSession session) {
        sessionManager.setActiveSession(session);
    }

    /**
     * Clears the active session.
     */
    @Override
    public void clearActiveSession() {
        if (sessionManager.getActiveSession() != null
                && sessionManager.getActiveSession().getPhoneNumber() != null) {
            final PhoneNumber phoneNumber = PhoneNumberUtils
                    .getPhoneNumber(sessionManager.getActiveSession().getPhoneNumber());
            digitsEventCollector.authCleared(new LogoutEventDetails(
                    Locale.getDefault().getLanguage(), phoneNumber.getCountryIso()));
        }
        sessionManager.clearActiveSession();
    }

    /**
     * @param id
     * @return the session associated with the id.
     */
    @Override
    public DigitsSession getSession(long id) {
        return sessionManager.getSession(id);
    }

    /**
     * Sets the session to associate with the id. If there is no active session, this session also
     * becomes the active session.
     *
     * @param id
     * @param session
     */
    @Override
    public void setSession(long id, DigitsSession session) {
        sessionManager.setSession(id, session);
    }

    /**
     * Clears the session associated with the id.
     *
     * @param id
     */
    @Override
    public void clearSession(long id) {
        sessionManager.clearSession(id);
    }

    /**
     * @return the session map containing all managed sessions
     */
    @Override
    public Map<Long, DigitsSession> getSessionMap() {
        return sessionManager.getSessionMap();
    }
}
