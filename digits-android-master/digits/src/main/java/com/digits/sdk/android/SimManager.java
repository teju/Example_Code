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
import android.telephony.PhoneNumberUtils;
import android.telephony.TelephonyManager;
import android.text.TextUtils;

import io.fabric.sdk.android.services.common.CommonUtils;

import java.util.Locale;

class SimManager {
    private final TelephonyManager telephonyManager;
    private final boolean canReadPhoneState;

    public static SimManager createSimManager(Context context) {
        final TelephonyManager telephonyManager = (TelephonyManager) context.getSystemService
                (Context.TELEPHONY_SERVICE);
        return new SimManager(telephonyManager, CommonUtils.checkPermission(context,
                android.Manifest.permission.READ_PHONE_STATE));
    }

    protected SimManager(TelephonyManager telephonyManager, boolean canReadPhoneState) {
        this.telephonyManager = telephonyManager;
        this.canReadPhoneState = canReadPhoneState;
    }

    protected String getRawPhoneNumber() {
        if (telephonyManager == null || !canReadPhoneState) {
            return "";
        }
        return normalizeNumber(telephonyManager.getLine1Number());
    }

    /**
     * Normalization extracted from Android
     * https://android.googlesource.com/platform/frameworks/base/+/refs/heads/master/telephony
     * * /java/android/telephony/PhoneNumberUtils.java
     */
    private String normalizeNumber(String phoneNumber) {
        if (TextUtils.isEmpty(phoneNumber)) {
            return "";
        }
        final StringBuilder sb = new StringBuilder();
        final int len = phoneNumber.length();
        for (int i = 0; i < len; i++) {
            final char c = phoneNumber.charAt(i);
            final int digit = Character.digit(c, 10);
            if (digit != -1) {
                sb.append(digit);
            } else if (i == 0 && c == '+') {
                sb.append(c);
            } else if ((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z')) {
                return normalizeNumber(PhoneNumberUtils.convertKeypadLettersToDigits
                        (phoneNumber));
            }
        }
        return sb.toString();
    }

    protected String getCountryIso() {
        if (telephonyManager != null) {
            final String simCountry = telephonyManager.getSimCountryIso();
            if (isIso2(simCountry)) {
                return simCountry.toUpperCase(Locale.getDefault());
            } else if (!isCdma()) {
                final String networkCountry = telephonyManager.getNetworkCountryIso();
                if (isIso2(networkCountry)) {
                    return networkCountry.toUpperCase(Locale.getDefault());
                }
            }
        }
        return "";
    }

    private boolean isIso2(String simCountry) {
        return simCountry != null && simCountry.length() == 2;
    }

    private boolean isCdma() {
        return telephonyManager.getPhoneType() == TelephonyManager.PHONE_TYPE_CDMA;
    }

}
