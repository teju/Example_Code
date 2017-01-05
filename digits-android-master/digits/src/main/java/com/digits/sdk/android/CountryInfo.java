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

import java.text.Collator;
import java.util.Locale;

class CountryInfo implements Comparable<CountryInfo> {
    private final Collator collator;
    public final Locale locale;
    public final int countryCode;

    public CountryInfo(Locale locale, int countryCode) {
        collator = Collator.getInstance(Locale.getDefault());
        collator.setStrength(Collator.PRIMARY);

        this.locale = locale;
        this.countryCode = countryCode;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        final CountryInfo that = (CountryInfo) o;

        if (countryCode != that.countryCode) return false;
        return !(locale != null ? !locale.equals(that.locale) : that.locale != null);

    }

    @Override
    public int hashCode() {
        int result = locale != null ? locale.hashCode() : 0;
        result = 31 * result + countryCode;
        return result;
    }

    @Override
    public String toString() {
        return this.locale.getDisplayCountry() + " +" + countryCode;
    }

    @Override
    public int compareTo(CountryInfo info) {
        return collator.compare(this.locale.getDisplayCountry(), info.locale.getDisplayCountry());
    }
}
