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

import android.text.TextUtils;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.annotations.SerializedName;
import com.twitter.sdk.android.core.AuthToken;
import com.twitter.sdk.android.core.AuthTokenAdapter;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.Session;
import com.twitter.sdk.android.core.TwitterAuthToken;
import com.twitter.sdk.android.core.internal.oauth.OAuth2Token;

import java.util.List;

import io.fabric.sdk.android.Fabric;
import io.fabric.sdk.android.services.persistence.SerializationStrategy;
import retrofit.client.Header;

/**
 * Defines class for digits user session
 * has an {@link com.twitter.sdk.android.core.AuthToken} and user id
 */
public class DigitsSession extends Session<AuthToken> {
    public static final long UNKNOWN_USER_ID = -1L;
    public static final long LOGGED_OUT_USER_ID = 0L;
    public static final String DEFAULT_PHONE_NUMBER = "";
    public static final Email DEFAULT_EMAIL = new Email("", false);

    static final String TOKEN_HEADER = "x-twitter-new-account-oauth-access-token";
    static final String SECRET_HEADER = "x-twitter-new-account-oauth-secret";

    @SerializedName("phone_number")
    private final String phoneNumber;
    @SerializedName("email")
    private final Email email;

    public DigitsSession(AuthToken authToken, long id) {
        this(authToken, id, DEFAULT_PHONE_NUMBER, DEFAULT_EMAIL);
    }

    public DigitsSession(AuthToken authToken, long id, String phoneNumber, Email email) {
        super(authToken, id);
        this.phoneNumber = phoneNumber;
        this.email = email;
    }

    public DigitsSession(OAuth2Token token) {
        this(token, DigitsSession.LOGGED_OUT_USER_ID, DEFAULT_PHONE_NUMBER, DEFAULT_EMAIL);
    }

    public boolean isLoggedOutUser() {
        return getId() == DigitsSession.LOGGED_OUT_USER_ID;
    }

    public boolean isValidUser() {
        return isValidUserId(getId()) && isValidUserToken(getAuthToken());
    }

    private boolean isValidUserId(long id) {
        return !isLoggedOutUser() && id != UNKNOWN_USER_ID;
    }

    private boolean isValidUserToken(AuthToken token) {
        return (token instanceof TwitterAuthToken) && (((TwitterAuthToken) token).secret != null)
                && (((TwitterAuthToken) token).token != null);
    }

    static DigitsSession create(Result<DigitsUser> result, String phoneNumber) {
        if (result == null) {
            throw new NullPointerException("result must not be null");
        }
        if (result.data == null) {
            throw new NullPointerException("result.data must not be null");
        }
        if (result.response == null) {
            throw new NullPointerException("result.response must not be null");
        }
        if (phoneNumber == null) {
            throw new NullPointerException("phoneNumber must not be null");
        }

        final List<Header> headers = result.response.getHeaders();
        String token = "";
        String secret = "";
        for (Header header : headers) {
            if (TOKEN_HEADER.equals(header.getName())) {
                token = header.getValue();
            } else if (SECRET_HEADER.equals(header.getName())) {
                secret = header.getValue();
            }
            if (!TextUtils.isEmpty(token) && !TextUtils.isEmpty(secret)) {
                break;
            }
        }

        return new DigitsSession(new TwitterAuthToken(token, secret), result.data.id,
                phoneNumber, DEFAULT_EMAIL);
    }

    static DigitsSession create(DigitsSessionResponse result, String phoneNumber) {
        if (result == null) {
            throw new NullPointerException("result must not be null");
        }
        if (phoneNumber == null) {
            throw new NullPointerException("phoneNumber must not be null");
        }

        return new DigitsSession(new TwitterAuthToken(result.token, result.secret), result
                .userId, phoneNumber, DEFAULT_EMAIL);
    }

    public static DigitsSession create(VerifyAccountResponse verifyAccountResponse) {
        if (verifyAccountResponse == null) {
            throw new NullPointerException("verifyAccountResponse must not be null");
        }

        return new DigitsSession(verifyAccountResponse.token, verifyAccountResponse.userId,
                verifyAccountResponse.phoneNumber, verifyAccountResponse.email != null ?
                verifyAccountResponse.email : DEFAULT_EMAIL);
    }

    /**
     * Returns the instance of {@link Email} for this session
     *
     * @return null or user email
     */
    public Email getEmail() {
        return email;
    }

    /**
     * Returns the phone number tied to this session
     *
     * @return null or the user phone number
     */
    public String getPhoneNumber() {
        return phoneNumber;
    }


    public static class Serializer implements SerializationStrategy<DigitsSession> {

        private static final String TAG = "Digits";
        private final Gson gson;

        public Serializer() {
            this.gson = new GsonBuilder()
                    .registerTypeAdapter(AuthToken.class, new AuthTokenAdapter())
                    .create();
        }

        @Override
        public String serialize(DigitsSession session) {
            if (session != null && session.getAuthToken() != null) {
                try {
                    return gson.toJson(session);
                } catch (Exception e) {
                    Fabric.getLogger().d(TAG, e.getMessage());
                }
            }
            return "";
        }

        @Override
        public DigitsSession deserialize(String serializedSession) {
            if (!TextUtils.isEmpty(serializedSession)) {
                try {
                    final DigitsSession deserializeSession = gson.fromJson(serializedSession,
                            DigitsSession.class);
                    return new DigitsSession(deserializeSession.getAuthToken(),
                            deserializeSession.getId(),
                            deserializeSession.phoneNumber == null ? DEFAULT_PHONE_NUMBER :
                                    deserializeSession.phoneNumber,
                            deserializeSession.email == null ? DEFAULT_EMAIL :
                                    deserializeSession.email);
                } catch (Exception e) {
                    Fabric.getLogger().d(TAG, e.getMessage());
                }
            }
            return null;
        }

    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        if (!super.equals(o)) return false;

        final DigitsSession session = (DigitsSession) o;

        if (phoneNumber != null ? !phoneNumber.equals(session.phoneNumber) :
                session.phoneNumber != null)
            return false;
        return !(email != null ? !email.equals(session.email) : session.email != null);
    }

    @Override
    public int hashCode() {
        int result = super.hashCode();
        result = 31 * result + (phoneNumber != null ? phoneNumber.hashCode() : 0);
        result = 31 * result + (email != null ? email.hashCode() : 0);
        return result;
    }
}
