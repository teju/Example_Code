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

import com.crashlytics.android.answers.shim.AnswersOptionalLogger;
import com.crashlytics.android.answers.shim.KitEvent;

@Beta(Beta.Feature.Analytics)
class DefaultAnswersLogger extends DigitsEventLogger {
    final static DefaultAnswersLogger instance;

    static{
        instance = new DefaultAnswersLogger();
    }

    private DefaultAnswersLogger() { }

    @Override
    public void loginBegin(DigitsEventDetails details) {
        AnswersOptionalLogger.get().logKitEvent(
                new KitEvent("Digits Login Start")
                        .putAttribute("Language", details.language)
        );
    }

    @Override
    public void loginSuccess(DigitsEventDetails details) {
        AnswersOptionalLogger.get().logKitEvent(
                new KitEvent("Digits Login Success")
                        .putAttribute("Language", details.language)
                        .putAttribute("Country", details.country)
        );
    }

    @Override
    public void logout(LogoutEventDetails details) {
        AnswersOptionalLogger.get().logKitEvent(
                new KitEvent("Digits Logout")
                        .putAttribute("Language", details.language)
                        .putAttribute("Country", details.country)
        );
    }

    @Override
    public void contactsUploadSuccess(ContactsUploadSuccessDetails details) {
        AnswersOptionalLogger.get().logKitEvent(
                new KitEvent("Digits Contact Uploads")
                        .putAttribute("Number of Contacts", details.successContacts)
        );
    }

    @Override
    public void contactsLookupSuccess(ContactsLookupSuccessDetails details) {
        // We emit this event for every match retrieved during the lookup.
        for (int i = 0; i < details.matchCount; i++) {
            AnswersOptionalLogger.get().logKitEvent(
                    new KitEvent("Digits Contact Found")
            );
        }
    }
}

