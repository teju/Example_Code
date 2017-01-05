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

import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit.RetrofitError;
import retrofit.client.Header;
import retrofit.client.Response;
import retrofit.mime.TypedByteArray;

import static org.junit.Assert.assertEquals;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class ContactsUploadFailureResultTests {
    @Test
    public void testGroupByType() throws Exception {
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(new NullPointerException());
        exceptions.add(new RuntimeException());
        exceptions.add(new SecurityException());
        exceptions.add(new SecurityException());
        final Map<String, List<Exception>> map =
                ContactsUploadFailureResult.groupByType(exceptions);
        assertEquals(1, map.get(NullPointerException.class.getName()).size());
        assertEquals(1, map.get(RuntimeException.class.getName()).size());
        assertEquals(2, map.get(SecurityException.class.getName()).size());
    }

    @Test
    public void testPopMostCommon() throws Exception {
        final List<Exception> exceptions = new ArrayList<>();
        // Exercise comparator logic by defining many and diverse instances
        exceptions.add(new NullPointerException());
        exceptions.add(new RuntimeException());
        exceptions.add(new SecurityException());
        exceptions.add(new SecurityException());
        exceptions.add(new SecurityException());
        exceptions.add(new SecurityException());
        exceptions.add(new SecurityException());
        exceptions.add(new SecurityException());
        final Map<String, List<Exception>> map =
                ContactsUploadFailureResult.groupByType(exceptions);
        final Exception exception = ContactsUploadFailureResult.popMostCommon(map);
        assertEquals(true, exception instanceof SecurityException);
    }

    @Test
    public void testPopMostCommon_empty_map() throws Exception {
        final Map<String, List<Exception>> map = new HashMap<>();
        final Exception exception = ContactsUploadFailureResult.popMostCommon(map);
        assertEquals(null, exception);
    }

    @Test
    public void testPopMostCommon_null_list() throws Exception {
        final Map<String, List<Exception>> map = new HashMap<>();
        map.put(NullPointerException.class.getName(), null);
        final Exception exception = ContactsUploadFailureResult.popMostCommon(map);
        assertEquals(null, exception);
    }

    @Test
    public void testPopMostCommon_empty_list() throws Exception {
        final Map<String, List<Exception>> map = new HashMap<>();
        map.put(NullPointerException.class.getName(), new ArrayList<Exception>());
        final Exception exception = ContactsUploadFailureResult.popMostCommon(map);
        assertEquals(null, exception);
    }

    @Test
    public void testPopMostCommon_null_exception() throws Exception {
        final Map<String, List<Exception>> map = new HashMap<>();
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(null);
        map.put(NullPointerException.class.getName(), exceptions);
        final Exception exception = ContactsUploadFailureResult.popMostCommon(map);
        assertEquals(null, exception);
    }

    @Test
    public void testSummarize_empty() throws Exception {
        final List<Exception> exceptions = new ArrayList<>();
        assertEquals(ContactsUploadFailureResult.Summary.UNEXPECTED,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_permission() throws Exception {
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(new SecurityException());
        assertEquals(ContactsUploadFailureResult.Summary.PERMISSION,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_bad_request() throws Exception {
        final RetrofitError retrofitError = createError(400, 214, ":(");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.BAD_REQUEST,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_bad_auth() throws Exception {
        final RetrofitError retrofitError = createError(400, 215, "");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.BAD_AUTHENTICATION,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_timestamp() throws Exception {
        final RetrofitError retrofitError = createError(401, 135, "");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.TIMESTAMP_OUT_OF_BOUNDS,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_entity() throws Exception {
        final RetrofitError retrofitError = createError(413, 0, "");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.ENTITY_TOO_LARGE,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_rate() throws Exception {
        final RetrofitError retrofitError = createError(429, 88, "");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.RATE_LIMIT,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_internal() throws Exception {
        final RetrofitError retrofitError = createError(500, 131, "");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.INTERNAL_SERVER,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_unavailable() throws Exception {
        final RetrofitError retrofitError = createError(503, 67, "");
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.SERVER_UNAVAILABLE,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_network() throws Exception {
        final RetrofitError retrofitError = mock(RetrofitError.class);
        when(retrofitError.getKind()).thenReturn(RetrofitError.Kind.NETWORK);
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.NETWORK,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    @Test
    public void testSummarize_parse() throws Exception {
        final RetrofitError retrofitError = mock(RetrofitError.class);
        when(retrofitError.getKind()).thenReturn(RetrofitError.Kind.CONVERSION);
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(retrofitError);
        assertEquals(ContactsUploadFailureResult.Summary.PARSING,
                ContactsUploadFailureResult.summarize(exceptions));
    }

    RetrofitError createError(int status, int errorCode, String errorMessage)
            throws UnsupportedEncodingException {
        final RetrofitError retrofitError = mock(RetrofitError.class);
        final UploadError uploadError = new UploadError(errorCode, errorMessage, 0);
        final List<UploadError> apiErrors = new ArrayList<>();
        apiErrors.add(uploadError);
        final UploadResponse uploadResponse = new UploadResponse(apiErrors);
        final Response response = createResponse(status, toJson(uploadResponse));
        when(retrofitError.getResponse()).thenReturn(response);
        when(retrofitError.getKind()).thenReturn(RetrofitError.Kind.HTTP);
        when(retrofitError.getStackTrace()).thenReturn(new StackTraceElement[0]);
        return retrofitError;
    }

    // Response is final, which isn't mockable by Mockito, so this fn creates a stub.
    Response createResponse(int status, String body) throws UnsupportedEncodingException {
        final List<Header> headers = new ArrayList<>();
        return new Response("url", status, "reason", headers,
                new TypedByteArray("application/json", body.getBytes("UTF-8")));
    }

    String toJson(UploadResponse response) {
        return new Gson().toJson(response);
    }
}
