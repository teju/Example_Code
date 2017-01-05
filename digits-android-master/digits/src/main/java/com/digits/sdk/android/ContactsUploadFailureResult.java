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

import com.twitter.sdk.android.core.TwitterApiException;
import com.twitter.sdk.android.core.models.ApiError;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit.RetrofitError;
import retrofit.client.Response;

/**
 * Provide concise, but actionable, failure details
 */
public class ContactsUploadFailureResult implements Parcelable {
    public enum Summary {
        /** Unable to access network, eg airplane mode --> Check network connection */
        NETWORK,
        /** Unable to parse response, eg response interrupted */
        PARSING,
        /** Unable to access contacts data --> Check contact permissions */
        PERMISSION,
        /** Incorrect request construction --> Retry */
        BAD_REQUEST,
        /** Unable to authenticate user --> Re-authenticate */
        BAD_AUTHENTICATION,
        /** Timestamp in auth header is too far in past/future to assert uniqueness
         *    --> Check client for clock skew */
        TIMESTAMP_OUT_OF_BOUNDS,
        /** Contact upload batch is too large */
        ENTITY_TOO_LARGE,
        /** No contacts found on device */
        NO_CONTACTS_FOUND,
        /** Too many upload requests --> Back off; If you only need to fetch, call GET endpoint */
        RATE_LIMIT,
        /** Internal server error */
        INTERNAL_SERVER,
        /** Server unavailable */
        SERVER_UNAVAILABLE,
        /** Catch all --> Check logcat when developing locally */
        UNEXPECTED
    }

    /**
     * A single value summarizing the failure
     */
    public final Summary summary;

    public static ContactsUploadFailureResult create(Exception exception) {
        final List<Exception> exceptions = new ArrayList<>();
        exceptions.add(exception);
        return ContactsUploadFailureResult.create(exceptions);
    }

    public static ContactsUploadFailureResult create(List<Exception> exceptions) {
        return new ContactsUploadFailureResult(summarize(exceptions));
    }

    public ContactsUploadFailureResult(Summary summary) {
        this.summary = summary;
    }

    protected ContactsUploadFailureResult(Parcel in) {
        final int ordinal = in.readInt();
        summary = Summary.values()[ordinal];
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(summary.ordinal());
    }

    /**
     * Aggregate exceptions from a variety of sources and return summary value for the most common one
     * Related: DGTErrorCode on iOS error codes
     */
    static Summary summarize(List<Exception> exceptions){
        final Exception exception = popMostCommon(groupByType(exceptions));
        // local exceptions
        if (exception instanceof SecurityException) {
            return Summary.PERMISSION;
        }
        if (exception instanceof RetrofitError) {
            final RetrofitError retrofitError = (RetrofitError) exception;
            // remote api-related errors
            if (retrofitError.getKind().equals(RetrofitError.Kind.HTTP)) {
                final Response response = retrofitError.getResponse();
                final int status = response == null ? 0 : response.getStatus();
                final ApiError apiError = TwitterApiException.readApiError(retrofitError);
                final int errorCode = apiError == null ? 0 : apiError.getCode();
                if (status == 400 && errorCode == 214) {
                    return Summary.BAD_REQUEST;
                }
                if (status == 400 && errorCode == 215) {
                    return Summary.BAD_AUTHENTICATION;
                }
                if (status == 401 && errorCode == 135) {
                    return Summary.TIMESTAMP_OUT_OF_BOUNDS;
                }
                if (status == 413) {
                    return Summary.ENTITY_TOO_LARGE;
                }
                if (status == 429 && errorCode == 88) {
                    return Summary.RATE_LIMIT;
                }
                if (status == 500) {
                    return Summary.INTERNAL_SERVER;
                }
                if (status == 503) {
                    return Summary.SERVER_UNAVAILABLE;
                }
            // local api-related errors
            } else if (retrofitError.getKind().equals(RetrofitError.Kind.NETWORK)) {
                return Summary.NETWORK;
            } else if (retrofitError.getKind().equals(RetrofitError.Kind.CONVERSION)) {
                return Summary.PARSING;
            }
        }
        return Summary.UNEXPECTED;
    }

    static Map<String, List<Exception>> groupByType(List<Exception> exceptions) {
        final Map<String, List<Exception>> map = new HashMap<>();
        for (Exception exception : exceptions) {
            final String key = exception.getClass().getName();
            List<Exception> list = map.get(key);
            if (list == null) {
                list = new ArrayList<>();
            }
            list.add(exception);
            map.put(key, list);
        }
        return map;
    }

    static Exception popMostCommon(Map<String, List<Exception>> map) {
        final List<Map.Entry<String, List<Exception>>> entries = new ArrayList<>(map.entrySet());
        Collections.sort(entries, new Comparator<Map.Entry<String, List<Exception>>>() {
            @Override
            public int compare(Map.Entry<String, List<Exception>> lhs,
                               Map.Entry<String, List<Exception>> rhs) {
                return lhs.getValue().size() == rhs.getValue().size() ? 0 :
                        lhs.getValue().size() < rhs.getValue().size() ? 1 : -1;
            }
        });
        return entries.isEmpty()
                || entries.get(0) == null
                || entries.get(0).getValue() == null
                || entries.get(0).getValue().isEmpty()
                ? null
                : entries.get(0).getValue().get(0);
    }

    public static final Creator<ContactsUploadFailureResult> CREATOR =
            new Creator<ContactsUploadFailureResult>() {
        @Override
        public ContactsUploadFailureResult createFromParcel(Parcel in) {
            return new ContactsUploadFailureResult(in);
        }

        @Override
        public ContactsUploadFailureResult[] newArray(int size) {
            return new ContactsUploadFailureResult[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public String toString() {
        return "ContactsUploadFailureResult{" +
                "summary=" + summary +
                '}';
    }
}
