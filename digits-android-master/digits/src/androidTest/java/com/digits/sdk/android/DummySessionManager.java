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

import java.util.Map;

public class DummySessionManager implements SessionManager<DigitsSession> {
    DigitsSession activeSession;
    boolean isSet;

    public DummySessionManager(DigitsSession initial){
        activeSession = initial;
        isSet = false;
    }

    @Override
    public DigitsSession getActiveSession() {
        return activeSession;
    }

    @Override
    public void setActiveSession(DigitsSession session) {
        isSet = true;
        activeSession = session;
    }

    @Override
    public void clearActiveSession() {
        activeSession = null;
    }

    @Override
    public DigitsSession getSession(long id) {
        if (activeSession.getId() == id) {
            return activeSession;
        }
        return  null;
    }

    @Override
    public void setSession(long id, DigitsSession session) {
        isSet = true;
        activeSession = session;
    }

    @Override
    public void clearSession(long id) {
        if (activeSession.getId() == id) {
            activeSession = null;
        }
    }

    @Override
    public Map<Long, DigitsSession> getSessionMap() {
        return null;
    }

    boolean isSet(){
        return isSet;
    }
}
