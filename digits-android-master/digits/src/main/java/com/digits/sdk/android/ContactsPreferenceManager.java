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

import android.annotation.SuppressLint;

import io.fabric.sdk.android.Fabric;
import io.fabric.sdk.android.services.persistence.PreferenceStore;
import io.fabric.sdk.android.services.persistence.PreferenceStoreImpl;

class ContactsPreferenceManager {
    static final String KEY_CONTACTS_IMPORT_PERMISSION = "CONTACTS_IMPORT_PERMISSION";
    static final String KEY_CONTACTS_READ_TIMESTAMP = "CONTACTS_READ_TIMESTAMP";
    static final String KEY_CONTACTS_UPLOADED = "CONTACTS_CONTACTS_UPLOADED";

    final private PreferenceStore prefStore;

    ContactsPreferenceManager() {
        prefStore = new PreferenceStoreImpl(Fabric.getKit(Digits.class));
    }

    @SuppressLint("CommitPrefEdits")
    protected boolean hasContactImportPermissionGranted() {
        return prefStore.get().getBoolean(KEY_CONTACTS_IMPORT_PERMISSION, false);
    }

    @SuppressLint("CommitPrefEdits")
    protected void setContactImportPermissionGranted() {
        prefStore.save(prefStore.edit().putBoolean(KEY_CONTACTS_IMPORT_PERMISSION, true));
    }

    @SuppressLint("CommitPrefEdits")
    protected void clearContactImportPermissionGranted() {
        prefStore.save(prefStore.edit().remove(KEY_CONTACTS_IMPORT_PERMISSION));
    }

    @SuppressLint("CommitPrefEdits")
    protected void setContactsReadTimestamp(long timestamp) {
        prefStore.save(prefStore.edit().putLong(KEY_CONTACTS_READ_TIMESTAMP, timestamp));
    }

    @SuppressLint("CommitPrefEdits")
    protected void setContactsUploaded(int count) {
        prefStore.save(prefStore.edit().putInt(KEY_CONTACTS_UPLOADED, count));
    }
}
