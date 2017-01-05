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

import com.twitter.sdk.android.core.Session;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import static org.mockito.Mockito.doReturn;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.spy;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyZeroInteractions;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsSessionVerifierTest {
    private DigitsSessionVerifier.VerificationCallback verificationCallback;
    private DigitsSessionVerifier verifier;
    private ApiInterface accountService;
    private SessionListener listener;

    @Before
    public void setUp() throws Exception {
        verificationCallback = mock(DigitsSessionVerifier.VerificationCallback.class);
        verifier = spy(new DigitsSessionVerifier(verificationCallback));
        accountService = mock(ApiInterface.class);
        listener = mock(SessionListener.class);
    }

    @Test
    public void testVerifySession_digitsSession() throws Exception {
        final DigitsSession session = mock(DigitsSession.class);
        doReturn(accountService).when(verifier).getAccountService();
        when(session.isLoggedOutUser()).thenReturn(false);
        verifier.verifySession(session);
        verify(accountService).verifyAccount(verificationCallback);
    }

    @Test
    public void testVerifySession_digitsSessionLoggedOut() throws Exception {
        final DigitsSession session = mock(DigitsSession.class);
        doReturn(accountService).when(verifier).getAccountService();
        when(session.isLoggedOutUser()).thenReturn(true);
        verifier.verifySession(session);
        verifyZeroInteractions(accountService);
        verifyZeroInteractions(verificationCallback);
    }

    @Test
    public void testVerifySession_nonDigitsSession() throws Exception {
        final Session session = mock(Session.class);
        doReturn(accountService).when(verifier).getAccountService();
        verifier.verifySession(session);
        verifyZeroInteractions(accountService);
        verifyZeroInteractions(verificationCallback);
    }

    @Test
    public void testAddSessionListener() throws Exception {
        verifier.addSessionListener(listener);
        verify(verificationCallback).addSessionListener(listener);
    }

    @Test
    public void testRemoveSessionListener() throws Exception {
        verifier.removeSessionListener(listener);
        verify(verificationCallback).removeSession(listener);
    }

    @Test
    public void testAddSessionListener_nullObject() throws Exception {
        verifier.addSessionListener(null);
        verify(verificationCallback).addSessionListener(null);
    }

    @Test
    public void testRemoveSessionListener_nullObject() throws Exception {
        verifier.removeSessionListener(null);
        verify(verificationCallback).removeSession(null);
    }
}
