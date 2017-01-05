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
import android.content.pm.PackageManager;
import android.telephony.TelephonyManager;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import static org.junit.Assert.assertEquals;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class SimManagerTest {
    private TelephonyManager telephonyManager;
    private SimManager simManager;
    private Context context;

    @Before
    public void setUp() throws Exception {

        context = mock(Context.class);
        telephonyManager = mock(TelephonyManager.class);
        when(context.getSystemService(Context.TELEPHONY_SERVICE)).thenReturn(telephonyManager);
        simManager = SimManager.createSimManager(context);
    }

    @Test
    public void testConstructor_nullTelephonyManager() throws Exception {
        when(context.getSystemService(Context.TELEPHONY_SERVICE)).thenReturn(null);
        simManager = SimManager.createSimManager(context);
        assertEquals("", simManager.getRawPhoneNumber());
        assertEquals("", simManager.getCountryIso());
    }

    @Test
    public void testGetCountryIso_iso2() throws Exception {
        when(telephonyManager.getSimCountryIso()).thenReturn(TestConstants.US_ISO2);
        assertEquals(TestConstants.US_ISO2.toUpperCase(), simManager.getCountryIso());
    }

    @Test
    public void testGetCountryIso_iso3NoNetworkInfo() throws Exception {
        when(telephonyManager.getSimCountryIso()).thenReturn(TestConstants.US_ISO3);
        assertEquals("", simManager.getCountryIso());
    }

    @Test
    public void testGetCountryIso_iso3WithNetworkCountryIso2() throws Exception {
        when(telephonyManager.getSimCountryIso()).thenReturn(TestConstants.US_ISO3);
        when(telephonyManager.getNetworkCountryIso()).thenReturn(TestConstants.US_ISO2);
        assertEquals(TestConstants.US_ISO2.toUpperCase(), simManager.getCountryIso());
    }

    @Test
    public void testGetCountryIso_iso3WithNetworkCountryIso3() throws Exception {
        when(telephonyManager.getSimCountryIso()).thenReturn(TestConstants.US_ISO3);
        when(telephonyManager.getNetworkCountryIso()).thenReturn(TestConstants.US_ISO3);
        assertEquals("", simManager.getCountryIso());
    }

    @Test
    public void testGetCountryIso_noPermission() throws Exception {
        when(context.checkCallingOrSelfPermission(android.Manifest.permission.READ_PHONE_STATE))
                .thenReturn(PackageManager.PERMISSION_DENIED);
        simManager = SimManager.createSimManager(context);
        testGetCountryIso_iso2();
    }

    @Test
    public void testGetPhoneNumber_noPermission() throws Exception {
        when(context.checkCallingOrSelfPermission(android.Manifest.permission.READ_PHONE_STATE))
                .thenReturn(PackageManager.PERMISSION_DENIED);
        simManager = SimManager.createSimManager(context);
        when(telephonyManager.getLine1Number()).thenReturn(TestConstants.PHONE);
        assertEquals("", simManager.getRawPhoneNumber());
    }

    @Test
    public void testGetCountryIso_iso3isCdma() throws Exception {
        when(telephonyManager.getSimCountryIso()).thenReturn(TestConstants.US_ISO3);
        when(telephonyManager.getPhoneType()).thenReturn(TelephonyManager.PHONE_TYPE_CDMA);
        assertEquals("", simManager.getCountryIso());
    }

    /**
     * Test extracted from Android
     * http://androidxref.com/5.0.0_r2/xref/frameworks/opt/telephony/tests/telephonytests/src/com
     * * /android/internal/telephony/PhoneNumberUtilsTest.java
     */
    @Test
    public void testGetPhoneNumber() throws Exception {
        when(telephonyManager.getLine1Number()).thenReturn("650 2910000");
        assertEquals("6502910000", simManager.getRawPhoneNumber());
        when(telephonyManager.getLine1Number()).thenReturn("12,3#4*567");
        assertEquals("1234567", simManager.getRawPhoneNumber());
        when(telephonyManager.getLine1Number()).thenReturn("800-GOOG-114");
        assertEquals("8004664114", simManager.getRawPhoneNumber());
        when(telephonyManager.getLine1Number()).thenReturn("+1 650 2910000");
        assertEquals("+16502910000", simManager.getRawPhoneNumber());
    }
}
