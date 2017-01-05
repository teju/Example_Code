/*
 * Copyright (C) 2009 The Android Open Source Project
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
 */
package com.digits.sdk.vcard;

import android.provider.ContactsContract.CommonDataKinds.Im;
import android.provider.ContactsContract.CommonDataKinds.Phone;
import android.telephony.PhoneNumberUtils;
import android.text.SpannableStringBuilder;
import android.text.TextUtils;
import android.util.SparseArray;

import java.util.Arrays;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

/**
 * Utilities for VCard handling codes.
 */
@SuppressWarnings("PMD")
public class VCardUtils {
    private static final String LOG_TAG = VCardConstants.LOG_TAG;

    /**
     * Ported methods which are hidden in {@link PhoneNumberUtils}.
     */
    public static class PhoneNumberUtilsPort {
        public static String formatNumber(String source, int defaultFormattingType) {
            final SpannableStringBuilder text = new SpannableStringBuilder(source);
            PhoneNumberUtils.formatNumber(text, defaultFormattingType);
            return text.toString();
        }
    }

    /**
     * Ported methods which are hidden in {@link TextUtils}.
     */
    public static class TextUtilsPort {
        public static boolean isPrintableAscii(final char c) {
            final int asciiFirst = 0x20;
            final int asciiLast = 0x7E;  // included
            return (asciiFirst <= c && c <= asciiLast) || c == '\r' || c == '\n';
        }

        public static boolean isPrintableAsciiOnly(final CharSequence str) {
            final int len = str.length();
            for (int i = 0; i < len; i++) {
                if (!isPrintableAscii(str.charAt(i))) {
                    return false;
                }
            }
            return true;
        }
    }

    // Note that not all types are included in this map/set, since, for example, TYPE_HOME_FAX is
    // converted to two parameter Strings. These only contain some minor fields valid in both
    // vCard and current (as of 2009-08-07) Contacts structure.
    private static final SparseArray<String> sKnownPhoneTypesMap_ItoS;
    private static final Set<String> sPhoneTypesUnknownToContactsSet;
    private static final Map<String, Integer> sKnownPhoneTypeMap_StoI;
    private static final SparseArray<String> sKnownImPropNameMap_ItoS;
    private static final Set<String> sMobilePhoneLabelSet;

    static {
        sKnownPhoneTypesMap_ItoS = new SparseArray<>();
        sKnownPhoneTypeMap_StoI = new HashMap<>();

        sKnownPhoneTypesMap_ItoS.put(Phone.TYPE_CAR, VCardConstants.PARAM_TYPE_CAR);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_CAR, Phone.TYPE_CAR);
        sKnownPhoneTypesMap_ItoS.put(Phone.TYPE_PAGER, VCardConstants.PARAM_TYPE_PAGER);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_PAGER, Phone.TYPE_PAGER);
        sKnownPhoneTypesMap_ItoS.put(Phone.TYPE_ISDN, VCardConstants.PARAM_TYPE_ISDN);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_ISDN, Phone.TYPE_ISDN);

        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_HOME, Phone.TYPE_HOME);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_WORK, Phone.TYPE_WORK);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_CELL, Phone.TYPE_MOBILE);

        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_PHONE_EXTRA_TYPE_OTHER, Phone.TYPE_OTHER);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_PHONE_EXTRA_TYPE_CALLBACK,
                Phone.TYPE_CALLBACK);
        sKnownPhoneTypeMap_StoI.put(
                VCardConstants.PARAM_PHONE_EXTRA_TYPE_COMPANY_MAIN, Phone.TYPE_COMPANY_MAIN);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_PHONE_EXTRA_TYPE_RADIO, Phone.TYPE_RADIO);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_PHONE_EXTRA_TYPE_TTY_TDD,
                Phone.TYPE_TTY_TDD);
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_PHONE_EXTRA_TYPE_ASSISTANT,
                Phone.TYPE_ASSISTANT);
        // OTHER (default in Android) should correspond to VOICE (default in vCard).
        sKnownPhoneTypeMap_StoI.put(VCardConstants.PARAM_TYPE_VOICE, Phone.TYPE_OTHER);

        sPhoneTypesUnknownToContactsSet = new HashSet<>();
        sPhoneTypesUnknownToContactsSet.add(VCardConstants.PARAM_TYPE_MODEM);
        sPhoneTypesUnknownToContactsSet.add(VCardConstants.PARAM_TYPE_MSG);
        sPhoneTypesUnknownToContactsSet.add(VCardConstants.PARAM_TYPE_BBS);
        sPhoneTypesUnknownToContactsSet.add(VCardConstants.PARAM_TYPE_VIDEO);

        sKnownImPropNameMap_ItoS = new SparseArray<>();
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_AIM, VCardConstants.PROPERTY_X_AIM);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_MSN, VCardConstants.PROPERTY_X_MSN);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_YAHOO, VCardConstants.PROPERTY_X_YAHOO);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_SKYPE, VCardConstants.PROPERTY_X_SKYPE_USERNAME);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_GOOGLE_TALK,
                VCardConstants.PROPERTY_X_GOOGLE_TALK);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_ICQ, VCardConstants.PROPERTY_X_ICQ);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_JABBER, VCardConstants.PROPERTY_X_JABBER);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_QQ, VCardConstants.PROPERTY_X_QQ);
        sKnownImPropNameMap_ItoS.put(Im.PROTOCOL_NETMEETING, VCardConstants.PROPERTY_X_NETMEETING);

        // \u643A\u5E2F\u96FB\u8A71 = Full-width Hiragana "Keitai-Denwa" (mobile phone)
        // \u643A\u5E2F = Full-width Hiragana "Keitai" (mobile phone)
        // \u30B1\u30A4\u30BF\u30A4 = Full-width Katakana "Keitai" (mobile phone)
        // \uFF79\uFF72\uFF80\uFF72 = Half-width Katakana "Keitai" (mobile phone)
        sMobilePhoneLabelSet = new HashSet<>(Arrays.asList(
                "MOBILE", "\u643A\u5E2F\u96FB\u8A71", "\u643A\u5E2F", "\u30B1\u30A4\u30BF\u30A4",
                "\uFF79\uFF72\uFF80\uFF72"));
    }

    public static String getPhoneTypeString(Integer type) {
        return sKnownPhoneTypesMap_ItoS.get(type);
    }

    @SuppressWarnings("deprecation")
    public static boolean isMobilePhoneLabel(final String label) {
        // For backward compatibility.
        // Detail: Until Donut, there isn't TYPE_MOBILE for email while there is now.
        //         To support mobile type at that time, this custom label had been used.
        return ("_AUTO_CELL".equals(label) || sMobilePhoneLabelSet.contains(label));
    }

    public static boolean isValidInV21ButUnknownToContactsPhoteType(final String label) {
        return sPhoneTypesUnknownToContactsSet.contains(label);
    }

    public static String getPropertyNameForIm(final int protocol) {
        return sKnownImPropNameMap_ItoS.get(protocol);
    }

    public static String[] sortNameElements(final int nameOrder,
            final String familyName, final String middleName, final String givenName) {
        final String[] list = new String[3];
        final int nameOrderType = VCardConfig.getNameOrderType(nameOrder);
        switch (nameOrderType) {
            case VCardConfig.NAME_ORDER_JAPANESE: {
                if (containsOnlyPrintableAscii(familyName) &&
                        containsOnlyPrintableAscii(givenName)) {
                    list[0] = givenName;
                    list[1] = middleName;
                    list[2] = familyName;
                } else {
                    list[0] = familyName;
                    list[1] = middleName;
                    list[2] = givenName;
                }
                break;
            }
            case VCardConfig.NAME_ORDER_EUROPE: {
                list[0] = middleName;
                list[1] = givenName;
                list[2] = familyName;
                break;
            }
            default: {
                list[0] = givenName;
                list[1] = middleName;
                list[2] = familyName;
                break;
            }
        }
        return list;
    }

    public static int getPhoneNumberFormat(final int vcardType) {
        if (VCardConfig.isJapaneseDevice(vcardType)) {
            return PhoneNumberUtils.FORMAT_JAPAN;
        } else {
            return PhoneNumberUtils.FORMAT_NANP;
        }
    }

    public static String constructNameFromElements(final int nameOrder,
            final String familyName, final String middleName, final String givenName) {
        return constructNameFromElements(nameOrder, familyName, middleName, givenName,
                null, null);
    }

    public static String constructNameFromElements(final int nameOrder,
            final String familyName, final String middleName, final String givenName,
            final String prefix, final String suffix) {
        final StringBuilder builder = new StringBuilder();
        final String[] nameList = sortNameElements(nameOrder, familyName, middleName, givenName);
        boolean first = true;
        if (!TextUtils.isEmpty(prefix)) {
            first = false;
            builder.append(prefix);
        }
        for (final String namePart : nameList) {
            if (!TextUtils.isEmpty(namePart)) {
                if (first) {
                    first = false;
                } else {
                    builder.append(' ');
                }
                builder.append(namePart);
            }
        }
        if (!TextUtils.isEmpty(suffix)) {
            if (!first) {
                builder.append(' ');
            }
            builder.append(suffix);
        }
        return builder.toString();
    }

    public static boolean containsOnlyPrintableAscii(final String...values) {
        if (values == null) {
            return true;
        }
        return containsOnlyPrintableAscii(Arrays.asList(values));
    }

    public static boolean containsOnlyPrintableAscii(final Collection<String> values) {
        if (values == null) {
            return true;
        }
        for (final String value : values) {
            if (TextUtils.isEmpty(value)) {
                continue;
            }
            if (!TextUtilsPort.isPrintableAsciiOnly(value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * <p>
     * This is useful when checking the string should be encoded into quoted-printable
     * or not, which is required by vCard 2.1.
     * </p>
     * <p>
     * See the definition of "7bit" in vCard 2.1 spec for more information.
     * </p>
     */
    public static boolean containsOnlyNonCrLfPrintableAscii(final String...values) {
        if (values == null) {
            return true;
        }
        return containsOnlyNonCrLfPrintableAscii(Arrays.asList(values));
    }

    public static boolean containsOnlyNonCrLfPrintableAscii(final Collection<String> values) {
        if (values == null) {
            return true;
        }
        final int asciiFirst = 0x20;
        final int asciiLast = 0x7E;  // included
        for (final String value : values) {
            if (TextUtils.isEmpty(value)) {
                continue;
            }
            final int length = value.length();
            for (int i = 0; i < length; i = value.offsetByCodePoints(i, 1)) {
                final int c = value.codePointAt(i);
                if (!(asciiFirst <= c && c <= asciiLast)) {
                    return false;
                }
            }
        }
        return true;
    }

    private static final Set<Character> sUnAcceptableAsciiInV21WordSet =
        new HashSet<>(Arrays.asList('[', ']', '=', ':', '.', ',', ' '));

    /**
     * <p>
     * This is useful since vCard 3.0 often requires the ("X-") properties and groups
     * should contain only alphabets, digits, and hyphen.
     * </p>
     * <p>
     * Note: It is already known some devices (wrongly) outputs properties with characters
     *       which should not be in the field. One example is "X-GOOGLE TALK". We accept
     *       such kind of input but must never output it unless the target is very specific
     *       to the device which is able to parse the malformed input.
     * </p>
     */
    public static boolean containsOnlyAlphaDigitHyphen(final String...values) {
        if (values == null) {
            return true;
        }
        return containsOnlyAlphaDigitHyphen(Arrays.asList(values));
    }

    public static boolean containsOnlyAlphaDigitHyphen(final Collection<String> values) {
        if (values == null) {
            return true;
        }
        final int upperAlphabetFirst = 0x41;  // A
        final int upperAlphabetAfterLast = 0x5b;  // [
        final int lowerAlphabetFirst = 0x61;  // a
        final int lowerAlphabetAfterLast = 0x7b;  // {
        final int digitFirst = 0x30;  // 0
        final int digitAfterLast = 0x3A;  // :
        final int hyphen = '-';
        for (final String str : values) {
            if (TextUtils.isEmpty(str)) {
                continue;
            }
            final int length = str.length();
            for (int i = 0; i < length; i = str.offsetByCodePoints(i, 1)) {
                int codepoint = str.codePointAt(i);
                if (!((lowerAlphabetFirst <= codepoint && codepoint < lowerAlphabetAfterLast) ||
                    (upperAlphabetFirst <= codepoint && codepoint < upperAlphabetAfterLast) ||
                    (digitFirst <= codepoint && codepoint < digitAfterLast) ||
                    (codepoint == hyphen))) {
                    return false;
                }
            }
        }
        return true;
    }

    public static boolean containsOnlyWhiteSpaces(final String...values) {
        if (values == null) {
            return true;
        }
        return containsOnlyWhiteSpaces(Arrays.asList(values));
    }

    public static boolean containsOnlyWhiteSpaces(final Collection<String> values) {
        if (values == null) {
            return true;
        }
        for (final String str : values) {
            if (TextUtils.isEmpty(str)) {
                continue;
            }
            final int length = str.length();
            for (int i = 0; i < length; i = str.offsetByCodePoints(i, 1)) {
                if (!Character.isWhitespace(str.codePointAt(i))) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * <p>
     * Returns true when the given String is categorized as "word" specified in vCard spec 2.1.
     * </p>
     * <p>
     * vCard 2.1 specifies:<br />
     * word = &lt;any printable 7bit us-ascii except []=:., &gt;
     * </p>
     */
    public static boolean isV21Word(final String value) {
        if (TextUtils.isEmpty(value)) {
            return true;
        }
        final int asciiFirst = 0x20;
        final int asciiLast = 0x7E;  // included
        final int length = value.length();
        for (int i = 0; i < length; i = value.offsetByCodePoints(i, 1)) {
            final int c = value.codePointAt(i);
            if (!(asciiFirst <= c && c <= asciiLast) ||
                    sUnAcceptableAsciiInV21WordSet.contains((char)c)) {
                return false;
            }
        }
        return true;
    }

    private static final int[] sEscapeIndicatorsV30 = new int[]{
        ':', ';', ',', ' '
    };

    private static final int[] sEscapeIndicatorsV40 = new int[]{
        ';', ':'
    };

    /**
     * <P>
     * Returns String available as parameter value in vCard 3.0.
     * </P>
     * <P>
     * RFC 2426 requires vCard composer to quote parameter values when it contains
     * semi-colon, for example (See RFC 2426 for more information).
     * This method checks whether the given String can be used without quotes.
     * </P>
     * <P>
     * Note: We remove DQUOTE inside the given value silently for now.
     * </P>
     */
    public static String toStringAsV30ParamValue(String value) {
        return toStringAsParamValue(value, sEscapeIndicatorsV30);
    }

    public static String toStringAsV40ParamValue(String value) {
        return toStringAsParamValue(value, sEscapeIndicatorsV40);
    }

    private static String toStringAsParamValue(String value, final int[] escapeIndicators) {
        if (TextUtils.isEmpty(value)) {
            value = "";
        }
        final int asciiFirst = 0x20;
        final int asciiLast = 0x7E;  // included
        final StringBuilder builder = new StringBuilder();
        final int length = value.length();
        boolean needQuote = false;
        for (int i = 0; i < length; i = value.offsetByCodePoints(i, 1)) {
            final int codePoint = value.codePointAt(i);
            if (codePoint < asciiFirst || codePoint == '"') {
                // CTL characters and DQUOTE are never accepted. Remove them.
                continue;
            }
            builder.appendCodePoint(codePoint);
            for (int indicator : escapeIndicators) {
                if (codePoint == indicator) {
                    needQuote = true;
                    break;
                }
            }
        }

        final String result = builder.toString();
        return ((result.length() == 0 || VCardUtils.containsOnlyWhiteSpaces(result))
                ? ""
                : (needQuote ? ('"' + result + '"')
                : result));
    }

    public static String toHalfWidthString(final String orgString) {
        if (TextUtils.isEmpty(orgString)) {
            return null;
        }
        final StringBuilder builder = new StringBuilder();
        final int length = orgString.length();
        for (int i = 0; i < length; i = orgString.offsetByCodePoints(i, 1)) {
            // All Japanese character is able to be expressed by char.
            // Do not need to use String#codepPointAt().
            final char ch = orgString.charAt(i);
            final String halfWidthText = JapaneseUtils.tryGetHalfWidthText(ch);
            if (halfWidthText != null) {
                builder.append(halfWidthText);
            } else {
                builder.append(ch);
            }
        }
        return builder.toString();
    }

    // TODO: utilities for vCard 4.0: datetime, timestamp, integer, float, and boolean

    private VCardUtils() {
    }
}
