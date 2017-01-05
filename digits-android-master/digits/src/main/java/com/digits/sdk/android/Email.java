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

import com.google.gson.annotations.SerializedName;

public class Email {
    @SerializedName("address")
    final String address;
    @SerializedName("is_verified")
    final boolean verified;

    Email(String address, boolean verified) {
        this.address = address;
        this.verified = verified;
    }

    public String getAddress() {
        return address;
    }

    public boolean isVerified() {
        return verified;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        final Email email = (Email) o;

        return verified == email.verified && address.equals(email.address);

    }

    @Override
    public int hashCode() {
        int result = address.hashCode();
        result = 31 * result + (verified ? 1 : 0);
        return result;
    }
}
