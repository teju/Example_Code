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

import android.os.Bundle;

import com.twitter.sdk.android.core.SessionManager;
import com.twitter.sdk.android.core.TwitterAuthToken;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.mockito.Mockito;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotNull;
import static org.mockito.Matchers.eq;
import static org.mockito.Mockito.*;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class LoginResultReceiverTests {
    private static final String ERROR = "Big Error on login";
    static final String PHONE = "+17071234567";
    private WeakAuthCallback callback;
    private Bundle bundle;

    private ArgumentCaptor<DigitsException> digitsErrorCaptor;
    private ArgumentCaptor<DigitsSession> sessionCaptor;
    private SessionManager<DigitsSession> mockSessionManager;
    private DigitsSession session;
    private LoginResultReceiver receiver;
    private ArgumentCaptor<DigitsEventDetails> detailsArgumentCaptor;
    private DigitsEventDetailsBuilder details;
    private DigitsEventCollector collector;

    @Before
    public void setUp() throws Exception {
        session = new DigitsSession(new TwitterAuthToken(TestConstants.TOKEN,
                TestConstants.SECRET), TestConstants.USER_ID, TestConstants.PHONE,
                TestConstants.EMAIL);
        mockSessionManager = mock(SessionManager.class);
        details = new DigitsEventDetailsBuilder()
                .withAuthStartTime(1L)
                .withLanguage("en")
                .withCountry("US");

        when(mockSessionManager.getActiveSession()).thenReturn(session);
        callback = mock(WeakAuthCallback.class);
        bundle = new Bundle();
        bundle.putString(LoginResultReceiver.KEY_ERROR, ERROR);
        bundle.putParcelable(DigitsClient.EXTRA_EVENT_DETAILS_BUILDER, details);
        digitsErrorCaptor = ArgumentCaptor.forClass(DigitsException.class);
        sessionCaptor = ArgumentCaptor.forClass(DigitsSession.class);
        detailsArgumentCaptor = ArgumentCaptor.forClass(DigitsEventDetails.class);
        collector = mock(DigitsEventCollector.class);
    }

    @Test
    public void testOnReceiveResult_nullListener() throws Exception {
        final LoginResultReceiver receiver = new LoginResultReceiver((WeakAuthCallback) null,
                mockSessionManager, collector);
        receiver.onReceiveResult(LoginResultReceiver.RESULT_OK, bundle);
        receiver.onReceiveResult(LoginResultReceiver.RESULT_ERROR, bundle);
    }

    @Test
    public void testOnReceiveResult_errorResultCode() throws Exception {
        receiver = new LoginResultReceiver(callback, mockSessionManager, collector);
        receiver.onReceiveResult(LoginResultReceiver.RESULT_ERROR, bundle);

        Mockito.verify(callback).failure(digitsErrorCaptor.capture());
        digitsErrorCaptor.getValue().getMessage().equals(ERROR);

        Mockito.verify(collector).authFailure(detailsArgumentCaptor.capture());
        final DigitsEventDetails actualDetails = detailsArgumentCaptor.getValue();

        assertEquals(details.country, actualDetails.country);
        assertEquals(details.language, actualDetails.language);
        assertNotNull(actualDetails.elapsedTimeInMillis);
    }

    @Test
    public void testOnReceiveResult_successResultCode() throws Exception {
        receiver = new LoginResultReceiver(callback, mockSessionManager, collector);
        bundle.putString(DigitsClient.EXTRA_PHONE, PHONE);
        receiver.onReceiveResult(LoginResultReceiver.RESULT_OK, bundle);
        Mockito.verify(callback).success(sessionCaptor.capture(), eq(PHONE));
        assertEquals(session, sessionCaptor.getValue());

        verify(collector).authSuccess(detailsArgumentCaptor.capture());
        final DigitsEventDetails actualDetails = detailsArgumentCaptor.getValue();

        assertEquals(details.country, actualDetails.country);
        assertEquals(details.language, actualDetails.language);
        assertNotNull(actualDetails.elapsedTimeInMillis);
    }

    @Test
    public void testOnReceiveResult_randomResultCode() throws Exception {
        receiver = new LoginResultReceiver(callback, mockSessionManager, collector);
        receiver.onReceiveResult(-1, bundle);
        verifyZeroInteractions(callback);
    }
}

