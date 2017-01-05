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

import com.google.gson.annotations.SerializedName;

import java.io.Serializable;

class AuthConfig implements Parcelable, Serializable {

    private static final long serialVersionUID = 5677912742763353323L;

    @SerializedName("tos_update")
    public boolean tosUpdate;
    @SerializedName("voice_enabled")
    public boolean isVoiceEnabled;
    @SerializedName("email_enabled")
    public boolean isEmailEnabled;

    public AuthConfig() {
    }

    protected AuthConfig(Parcel in) {
        tosUpdate = in.readInt() == 1;
        isVoiceEnabled = in.readInt() == 1;
        isEmailEnabled = in.readInt() == 1;
    }

    public static final Creator<AuthConfig> CREATOR = new Creator<AuthConfig>() {
        @Override
        public AuthConfig createFromParcel(Parcel in) {
            return new AuthConfig(in);
        }

        @Override
        public AuthConfig[] newArray(int size) {
            return new AuthConfig[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(tosUpdate ? 1 : 0);
        dest.writeInt(isVoiceEnabled ? 1 : 0);
        dest.writeInt(isEmailEnabled ? 1 : 0);
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        final AuthConfig that = (AuthConfig) o;

        return tosUpdate == that.tosUpdate && isVoiceEnabled == that.isVoiceEnabled &&
                isEmailEnabled == that.isEmailEnabled;
    }

    @Override
    public int hashCode() {
        int result = (tosUpdate ? 1 : 0);
        result = 31 * result + (isVoiceEnabled ? 1 : 0);
        result = 31 * result + (isEmailEnabled ? 1 : 0);
        return result;
    }
}
