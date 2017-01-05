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

import com.google.gson.Gson;
import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.TwitterAuthToken;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

import retrofit.client.Header;
import retrofit.client.Response;
import retrofit.http.Body;
import retrofit.http.Field;
import retrofit.http.Query;
import retrofit.mime.TypedByteArray;

public class MockApiInterface implements ApiInterface {

    static final long USER_ID = 1;
    static final String STATE = "state";
    static final String TOKEN = "token";
    static final String DEVICE_ID = "device_id";
    static final String SECRET = "secret";
    static final String EMAIL_ADDRESS = "mock@digits.com";
    static final boolean IS_VERIFIED = true;
    static final String PHONE_NUMBER = "+15556787676";
    static final TwitterAuthToken AUTH_TOKEN = new TwitterAuthToken(TOKEN, SECRET);
    static final Email EMAIL = new Email(EMAIL_ADDRESS, IS_VERIFIED);


    @Override
    public void account(@Field("phone_number") String phoneNumber,
                        @Field("numeric_pin") String numericPin, Callback<DigitsUser> cb) {
        final DigitsUser user = new DigitsUser(1, "1");
        final Response response = new Response("/1/sdk/account", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(user).getBytes()));
        cb.success(user, response);
    }

    @Override
    public void auth(@Field("x_auth_phone_number") String phoneNumber,
                     @Field("verification_type") String verificationType,
                     @Field("lang") String lang, Callback<AuthResponse> cb) {
        final AuthResponse data = new AuthResponse();
        data.authConfig = new AuthConfig();
        data.authConfig.isVoiceEnabled = true;
        final Response response = new Response("/1/sdk/login", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);
    }

    @Override
    public void login(@Field("login_verification_request_id") String requestId,
                      @Field("login_verification_user_id") long userId,
                      @Field("login_verification_challenge_response") String code,
                      Callback<DigitsSessionResponse> cb) {
        final DigitsSessionResponse data = createSessionResponse();
        final Response response = new Response("/auth/1/xauth_challenge.json", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);
    }

    @Override
    public void verifyPin(@Field("login_verification_request_id") String requestId,
                          @Field("login_verification_user_id") long userId,
                          @Field("pin") String pin, Callback<DigitsSessionResponse> cb) {
        final DigitsSessionResponse data = createSessionResponse();
        final Response response = new Response("/auth/1/xauth_pin.json", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);
    }

    @Override
    public void email(@Field("email_address") String email, Callback<DigitsSessionResponse> cb) {
        final DigitsSessionResponse data = createSessionResponse();
        final Response response = new Response("/1.1/sdk/account.json", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);
    }

    @Override
    public void verifyAccount(Callback<VerifyAccountResponse> cb) {
        final VerifyAccountResponse data = createVerifyAccountResponse();
        final Response response = new Response("/1.1/sdk/account.json", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);
        }

    @Override
    public void register(@Field("raw_phone_number") String rawPhoneNumber,
                         @Field("text_key") String textKey,
                         @Field("send_numeric_pin") Boolean sendNumericPin,
                         @Field("lang") String lang, @Field("client_identifier_string") String id,
                         @Field("verification_type") String verificationType,
                         Callback<DeviceRegistrationResponse> cb) {
        final DeviceRegistrationResponse data = createDeviceRegistrationResponse();
        final Response response = new Response("/1.1/device/register.json", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);
    }

    @Override
    public UploadResponse upload(@Body Vcards vcards) {
        return new UploadResponse(new ArrayList<UploadError>());
    }

    @Override
    public void deleteAll(@Body String body, Callback<Response> cb) {
        final Response response = new Response("/1.1/contacts/destroy/all.json", 200, "ok",
                Collections.<Header>emptyList(), new TypedByteArray("application/json",
        new Gson().toJson("response").getBytes()));
        final Result data = new Result(null, response);
        cb.success(data);
    }

    @Override
    public void usersAndUploadedBy(@Query("cursor") String cursor,
                                   @Query("count") Integer count, Callback<Contacts> cb) {
        final Contacts data;

        if (cursor == null) {
            // First page:
            data = getContactsPages().get("");
        } else {
            // Subsequent pages
            data = getContactsPages().get(cursor);
        }

        final Response response = new Response("/1.1/contacts/users_and_uploaded_by.json", 200,
                "ok", Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(data).getBytes()));
        cb.success(data, response);

    }

    static DigitsSessionResponse createSessionResponse(){
        final DigitsSessionResponse data = new DigitsSessionResponse();
        data.secret = TOKEN;
        data.token = SECRET;
        data.userId = USER_ID;
        return data;
    }

    static VerifyAccountResponse createVerifyAccountResponse(){
        final VerifyAccountResponse data = new VerifyAccountResponse();
        data.token = AUTH_TOKEN;
        data.userId = USER_ID;
        data.email = EMAIL;
        data.phoneNumber = PHONE_NUMBER;
        return data;
    }

    static DeviceRegistrationResponse createDeviceRegistrationResponse(){
        final DeviceRegistrationResponse data = new DeviceRegistrationResponse();
        data.deviceId = DEVICE_ID;
        data.state = STATE;
        data.authConfig = new AuthConfig();
        data.authConfig.isEmailEnabled = true;
        data.authConfig.isVoiceEnabled = true;
        data.authConfig.tosUpdate = false;
        data.normalizedPhoneNumber = PHONE_NUMBER;
        return data;
    }

    static Contacts createContacts(String cursor, long friendId){
        final Contacts contacts = new Contacts();
        contacts.nextCursor = cursor;
        contacts.users = new ArrayList<>();
        contacts.users.add(new DigitsUser(friendId, String.valueOf(friendId)));
        return contacts;
    }

    static void createAllContacts(Callback<Contacts> cb){
        final Contacts contacts = new Contacts();
        contacts.nextCursor = null;
        contacts.users = new ArrayList<>();
        contacts.users.add(new DigitsUser(2L, String.valueOf(2L)));
        contacts.users.add(new DigitsUser(3L, String.valueOf(3L)));

        final Response response = new Response("/1.1/contacts/users_and_uploaded_by.json", 200,
                "ok", Collections.<Header>emptyList(), new TypedByteArray("application/json",
                new Gson().toJson(contacts).getBytes()));
        cb.success(contacts, response);
    }

    static Map<String, Contacts> getContactsPages(){
        final Map<String, Contacts> map = new HashMap<>();
        map.put("", createContacts("cursor", 2L));
        map.put("cursor", createContacts(null, 3L));
        return map;
    }

    static DigitsSession createDigitsSession(){
        return new DigitsSession(AUTH_TOKEN, USER_ID, PHONE_NUMBER, EMAIL);
    }
}
