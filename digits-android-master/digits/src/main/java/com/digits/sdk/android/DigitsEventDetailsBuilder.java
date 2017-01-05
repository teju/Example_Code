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

import java.util.Locale;

@Beta(Beta.Feature.Analytics)
class DigitsEventDetailsBuilder implements Parcelable {
    final String language;
    final String country;
    final Long authStartTime;
    final Long currentTime;

    public DigitsEventDetailsBuilder(){
        this.language = null;
        this.country = null;
        this.authStartTime = null;
        this.currentTime = null;
    }

    public DigitsEventDetailsBuilder(String language, String country, Long authStartTime,
                                     Long currentTime) {
        this.language = language;
        this.country = country;
        this.authStartTime = authStartTime;
        this.currentTime = currentTime;
    }

    DigitsEventDetailsBuilder withLanguage(String language){
        return new DigitsEventDetailsBuilder(language, this.country, this.authStartTime,
                this.currentTime);
    }

    DigitsEventDetailsBuilder withCountry(String country){
        return new DigitsEventDetailsBuilder(this.language, country, this.authStartTime,
                this.currentTime);
    }

    DigitsEventDetailsBuilder withAuthStartTime(Long authStartTime){
        return new DigitsEventDetailsBuilder(this.language, this.country, authStartTime,
                this.currentTime);
    }

    DigitsEventDetailsBuilder withCurrentTime(Long currentTime){
        return new DigitsEventDetailsBuilder(this.language, this.country, this.authStartTime,
                currentTime);
    }

    DigitsEventDetails build(){
        final Long elapsedTime = currentTime - authStartTime;
        return new DigitsEventDetails(language, country, elapsedTime);
    }

    static String getLanguage() {
        return Locale.getDefault().getLanguage();
    }

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(this.language);
        dest.writeString(this.country);
        dest.writeValue(this.authStartTime);
        dest.writeValue(this.currentTime);
    }

    protected DigitsEventDetailsBuilder(Parcel in) {
        this.language = in.readString();
        this.country = in.readString();
        this.authStartTime = (Long) in.readValue(Long.class.getClassLoader());
        this.currentTime = (Long) in.readValue(Long.class.getClassLoader());
    }

    public static final Creator<DigitsEventDetailsBuilder> CREATOR =
            new Creator<DigitsEventDetailsBuilder>() {
                @Override
                public DigitsEventDetailsBuilder createFromParcel(Parcel source) {
                    return new DigitsEventDetailsBuilder(source);
                }

                @Override
                public DigitsEventDetailsBuilder[] newArray(int size) {
                    return new DigitsEventDetailsBuilder[size];
                }
            };
}
