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

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.HashMap;
import java.util.Map;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotNull;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class LoggingSessionManagerTest {

    SessionManager<DigitsSession> loggingSessionManager;
    DigitsSession mockDigitsSession;
    DigitsEventCollector digitsEventCollector;

    SessionManager<DigitsSession> persistedSessionManager;
    final Map<Long, DigitsSession> sessionMap = new HashMap<>();

    final Long sessionId = 123L;
    @Before
    public void setUp() throws Exception {
        digitsEventCollector = mock(DigitsEventCollector.class);
        mockDigitsSession = mock(DigitsSession.class);
        persistedSessionManager = mock(SessionManager.class);
        loggingSessionManager = new LoggingSessionManager(persistedSessionManager,
                digitsEventCollector);

        when(mockDigitsSession.getId()).thenReturn(1L);
        when(persistedSessionManager.getActiveSession()).thenReturn(mockDigitsSession);
        when(persistedSessionManager.getSession(sessionId)).thenReturn(mockDigitsSession);
        when(persistedSessionManager.getSessionMap()).thenReturn(sessionMap);
        when(mockDigitsSession.getPhoneNumber()).thenReturn("+14349873237");
    }

    @Test
    public void testGetActiveSession() throws Exception {
        loggingSessionManager.getActiveSession();
        verify(persistedSessionManager).getActiveSession();
    }

    @Test
    public void testSetActiveSession() throws Exception {
        loggingSessionManager.setActiveSession(mockDigitsSession);
        verify(persistedSessionManager).setActiveSession(mockDigitsSession);
    }

    @Test
    public void testClearActiveSession() throws Exception {
        final ArgumentCaptor<LogoutEventDetails> sessionLogoutEventDetailsArgumentCaptor =
                ArgumentCaptor.forClass(LogoutEventDetails.class);
        loggingSessionManager.clearActiveSession();
        verify(persistedSessionManager).clearActiveSession();
        verify(digitsEventCollector).authCleared(sessionLogoutEventDetailsArgumentCaptor.capture());
        final LogoutEventDetails digitsEventDetails =
                sessionLogoutEventDetailsArgumentCaptor.getValue();
        assertNotNull(digitsEventDetails.language);
        assertEquals("US", digitsEventDetails.country);
    }

    @Test
    public void testGetSession() throws Exception {
        assertEquals(mockDigitsSession, loggingSessionManager.getSession(sessionId));
    }

    @Test
    public void testSetSession() throws Exception {
        loggingSessionManager.setSession(sessionId, mockDigitsSession);
        verify(persistedSessionManager).setSession(sessionId, mockDigitsSession);
    }

    @Test
    public void testClearSession() throws Exception {
        loggingSessionManager.clearSession(sessionId);
        verify(persistedSessionManager).clearSession(sessionId);
    }

    @Test
    public void testGetSessionMap() throws Exception {
        assertEquals(sessionMap, loggingSessionManager.getSessionMap());
    }
}
