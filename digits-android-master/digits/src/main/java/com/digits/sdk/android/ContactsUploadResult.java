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

import android.os.Parcel;
import android.os.Parcelable;

/**
 * Result of contacts upload service
 */
public class ContactsUploadResult implements Parcelable {
    public final int successCount;
    public final int totalCount;

    ContactsUploadResult(int successCount, int totalCount) {
        this.successCount = successCount;
        this.totalCount = totalCount;
    }

    ContactsUploadResult(Parcel parcel) {
        this.successCount = parcel.readInt();
        this.totalCount = parcel.readInt();
    }

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel parcel, int flags) {
        parcel.writeInt(successCount);
        parcel.writeInt(totalCount);
    }

    public static final Parcelable.Creator<ContactsUploadResult> CREATOR
            = new Parcelable.Creator<ContactsUploadResult>() {
        public ContactsUploadResult createFromParcel(Parcel in) {
            return new ContactsUploadResult(in);
        }

        public ContactsUploadResult[] newArray(int size) {
            return new ContactsUploadResult[size];
        }
    };
}
