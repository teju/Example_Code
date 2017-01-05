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

/**
 * Constants used in both exporter and importer code.
 */
@SuppressWarnings("PMD")
public class VCardConstants {
    /* package */ static final String LOG_TAG = "vCard";
    public static final String VERSION_V21 = "2.1";
    public static final String VERSION_V30 = "3.0";
    public static final String VERSION_V40 = "4.0";

    // Valid and supported property names.
    public static final String PROPERTY_BEGIN = "BEGIN";
    public static final String PROPERTY_VERSION = "VERSION";
    public static final String PROPERTY_N = "N";
    public static final String PROPERTY_FN = "FN";
    public static final String PROPERTY_EMAIL = "EMAIL";
    public static final String PROPERTY_SOUND = "SOUND";  // Not fully supported.
    public static final String PROPERTY_TEL = "TEL";
    public static final String PROPERTY_SORT_STRING = "SORT-STRING";  // (3.0, 4.0)
    public static final String PROPERTY_END = "END";

    // Available in vCard 3.0. Should not use when composing vCard 2.1 file.

    // De-fact property values expressing phonetic names.
    public static final String PROPERTY_X_PHONETIC_FIRST_NAME = "X-PHONETIC-FIRST-NAME";
    public static final String PROPERTY_X_PHONETIC_MIDDLE_NAME = "X-PHONETIC-MIDDLE-NAME";
    public static final String PROPERTY_X_PHONETIC_LAST_NAME = "X-PHONETIC-LAST-NAME";

    // Properties both ContactsStruct and de-fact vCard extensions
    // Shown in http://en.wikipedia.org/wiki/VCard support are defined here.
    public static final String PROPERTY_X_AIM = "X-AIM";
    public static final String PROPERTY_X_MSN = "X-MSN";
    public static final String PROPERTY_X_YAHOO = "X-YAHOO";
    public static final String PROPERTY_X_ICQ = "X-ICQ";
    public static final String PROPERTY_X_JABBER = "X-JABBER";
    public static final String PROPERTY_X_GOOGLE_TALK = "X-GOOGLE-TALK";
    public static final String PROPERTY_X_SKYPE_USERNAME = "X-SKYPE-USERNAME";
    // Properties only ContactsStruct has. We alse use this.
    public static final String PROPERTY_X_QQ = "X-QQ";
    public static final String PROPERTY_X_NETMEETING = "X-NETMEETING";

    // Properties for DoCoMo vCard.
    public static final String PROPERTY_X_CLASS = "X-CLASS";
    public static final String PROPERTY_X_REDUCTION = "X-REDUCTION";
    public static final String PROPERTY_X_NO = "X-NO";
    public static final String PROPERTY_X_DCM_HMN_MODE = "X-DCM-HMN-MODE";

    public static final String PARAM_TYPE = "TYPE";

    public static final String PARAM_TYPE_HOME = "HOME";
    public static final String PARAM_TYPE_WORK = "WORK";
    public static final String PARAM_TYPE_FAX = "FAX";
    public static final String PARAM_TYPE_CELL = "CELL";
    public static final String PARAM_TYPE_VOICE = "VOICE";

    // Abbreviation of "prefered" according to vCard 2.1 specification.
    // We interpret this value as "primary" property during import/export.
    //
    // Note: Both vCard specs does not mention anything about the requirement for this parameter,
    //       but there may be some vCard importer which will get confused with more than
    //       one "PREF"s in one property name, while Android accepts them.
    public static final String PARAM_TYPE_PREF = "PREF";

    // Phone type parameters valid in vCard and known to ContactsContract, but not so common.
    public static final String PARAM_TYPE_CAR = "CAR";
    public static final String PARAM_TYPE_ISDN = "ISDN";
    public static final String PARAM_TYPE_PAGER = "PAGER";
    public static final String PARAM_TYPE_TLX = "TLX";  // Telex

    // Phone types existing in vCard 2.1 but not known to ContactsContract.
    public static final String PARAM_TYPE_MODEM = "MODEM";
    public static final String PARAM_TYPE_MSG = "MSG";
    public static final String PARAM_TYPE_BBS = "BBS";
    public static final String PARAM_TYPE_VIDEO = "VIDEO";

    public static final String PARAM_ENCODING_QP = "QUOTED-PRINTABLE";

    // TYPE parameters for Phones, which are not formally valid in vCard (at least 2.1).
    // These types are basically encoded to "X-" parameters when composing vCard.
    // Parser passes these when "X-" is added to the parameter or not.
    public static final String PARAM_PHONE_EXTRA_TYPE_CALLBACK = "CALLBACK";
    public static final String PARAM_PHONE_EXTRA_TYPE_RADIO = "RADIO";
    public static final String PARAM_PHONE_EXTRA_TYPE_TTY_TDD = "TTY-TDD";
    public static final String PARAM_PHONE_EXTRA_TYPE_ASSISTANT = "ASSISTANT";
    // vCard composer translates this type to "WORK" + "PREF". Just for parsing.
    public static final String PARAM_PHONE_EXTRA_TYPE_COMPANY_MAIN = "COMPANY-MAIN";
    // vCard composer translates this type to "VOICE" Just for parsing.
    public static final String PARAM_PHONE_EXTRA_TYPE_OTHER = "OTHER";

    //// Mainly for package constants.

    // DoCoMo specific type parameter. Used with "SOUND" property, which is alternate of
    // SORT-STRING invCard 3.0.
    /* package */ static final String PARAM_TYPE_X_IRMC_N = "X-IRMC-N";

    private VCardConstants() {
    }
}
