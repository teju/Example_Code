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

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import io.fabric.sdk.android.Fabric;
import io.fabric.sdk.android.FabricTestUtils;

import org.mockito.ArgumentCaptor;

import static android.view.View.OnClickListener;

import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;

public class DigitsAuthButtonTests extends DigitsAndroidTestCase {
    private static final int ANY_THEME = 80884;
    private DigitsAuthButton button;
    private DigitsClient client;
    private AuthCallback callback;
    private OnClickListener clickListener;
    private Digits digits;
    private DigitsAuthConfig digitsAuthConfig;

    @Override
    public void setUp() throws Exception {
        super.setUp();
        client = mock(DigitsClient.class);
        digits = mock(Digits.class);
        button = new DigitsAuthMock(getContext());
        callback = mock(AuthCallback.class);
        clickListener = mock(OnClickListener.class);
        digitsAuthConfig = new DigitsAuthConfig.Builder().withAuthCallBack(callback).build();
    }

    public void testOnClick() throws Exception {
        final ArgumentCaptor<DigitsAuthConfig> digitsAuthConfigArg =
            ArgumentCaptor.forClass(DigitsAuthConfig.class);
        button.setCallback(callback);
        button.setOnClickListener(clickListener);
        button.onClick(button);
        verify(client).startSignUp(digitsAuthConfigArg.capture());
        assetDigitsAuthConfigEquals(digitsAuthConfig, digitsAuthConfigArg.getValue());
        verify(clickListener).onClick(button);
    }

    public void testOnClick_noOnClickListener() throws Exception {
        final ArgumentCaptor<DigitsAuthConfig> digitsAuthConfigArg =
            ArgumentCaptor.forClass(DigitsAuthConfig.class);
        button.setCallback(callback);
        button.onClick(button);
        verify(client).startSignUp(digitsAuthConfigArg.capture());
        assetDigitsAuthConfigEquals(digitsAuthConfig, digitsAuthConfigArg.getValue());
        verifyNoInteractions(clickListener);
    }

    public void testOnClick_noLoginListener() throws Exception {
        try {
            button.setOnClickListener(clickListener);
            button.onClick(button);
            fail("Should throw IllegalArgumentException");
        } catch (IllegalArgumentException ex) {
            assertEquals("AuthCallback must not be null",
                    ex.getMessage());
            verify(client, never()).startSignUp(null);
            verifyNoInteractions(client, callback);
        }
    }

    public void testOnClick_nullOnClickListener() throws Exception {
        button.setCallback(callback);
        button.setOnClickListener(null);
        button.onClick(button);
        verifyNoInteractions(clickListener);
    }

    public void testOnClick_nullLoginListener() throws Exception {
        try {
            button.setCallback(null);
            button.setOnClickListener(clickListener);
            button.onClick(button);
            fail("Should throw IllegalArgumentException");
        } catch (IllegalArgumentException ex) {
            assertEquals("AuthCallback must not be null", ex.getMessage());
            verifyNoInteractions(callback, client, clickListener);
        }
    }

    public void testOnClick_getDigitsClientCalled() throws Exception {
        final ArgumentCaptor<DigitsAuthConfig> digitsAuthConfigArg =
            ArgumentCaptor.forClass(DigitsAuthConfig.class);
        button.setCallback(callback);
        button.onClick(button);
        verify(client).startSignUp(digitsAuthConfigArg.capture());
        assetDigitsAuthConfigEquals(digitsAuthConfig, digitsAuthConfigArg.getValue());
    }

    public void testGetDigitsClient() throws Exception {
        try {
            final Fabric fabric = new Fabric.Builder(getContext())
                    .kits(new Digits(), new TwitterCore(new TwitterAuthConfig("", "")))
                    .build();
            FabricTestUtils.with(fabric);

            final DigitsAuthButton authButton = new DigitsAuthButton(getContext());
            assertNull(authButton.digitsClient);
            authButton.setCallback(callback);
            authButton.getDigitsClient();
            assertNotNull(authButton.digitsClient);
        } finally {
            FabricTestUtils.resetFabric();
        }
    }

    public void testAuthTheme() throws Exception {
        button.setAuthTheme(ANY_THEME);
        verify(digits).setTheme(ANY_THEME);
    }

    private void assetDigitsAuthConfigEquals(DigitsAuthConfig expected, DigitsAuthConfig actual) {
        assertEquals(expected.themeResId, actual.themeResId);
        assertEquals(expected.isEmailRequired, actual.isEmailRequired);
        assertEquals(expected.phoneNumber, actual.phoneNumber);
        assertEquals(expected.authCallback, actual.authCallback);
    }

    class DigitsAuthMock extends DigitsAuthButton {

        public DigitsAuthMock(Context context) {
            super(context);
        }

        @Override
        protected DigitsClient getDigitsClient() {
            return client;
        }

        @Override
        protected Digits getDigits() {
            return digits;
        }
    }
}
