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

import android.support.annotation.NonNull;

import com.twitter.sdk.android.core.TwitterApiErrorConstants;
import com.twitter.sdk.android.core.TwitterApiException;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.internal.TwitterApiConstants;

import retrofit.RetrofitError;

/**
 * Indicates that authentication was unable to complete successfully
 */
public class DigitsException extends RuntimeException {
    private final int errorCode;
    private final AuthConfig config;

    DigitsException(String message) {
        this(message, TwitterApiErrorConstants.UNKNOWN_ERROR, new AuthConfig());
    }

    DigitsException(String message, int errorCode, @NonNull AuthConfig config) {
        super(message);
        this.errorCode = errorCode;
        this.config = config;
    }

    static DigitsException create(ErrorCodes errors, TwitterException exception) {
        String message;
        if (exception instanceof TwitterApiException) {
            final TwitterApiException apiException = (TwitterApiException) exception;
            message = getMessageForApiError(errors, apiException);
            return createException(apiException.getErrorCode(), message,
                    (AuthConfig) apiException.getRetrofitError().getBodyAs(AuthConfig.class));
        } else {
            message = errors.getDefaultMessage();
            return new DigitsException(message);
        }
    }

    private static DigitsException createException(int error, String message, AuthConfig config) {
        if (error == TwitterApiErrorConstants.COULD_NOT_AUTHENTICATE) {
            return new CouldNotAuthenticateException(message, error, config);
        } else if (error == TwitterApiErrorConstants.OPERATOR_UNSUPPORTED) {
            return new OperatorUnsupportedException(message, error, config);
        } else if (error == TwitterApiConstants.Errors.APP_AUTH_ERROR_CODE) {
            return new AppAuthErrorException(message, error, config);
        } else if (error == TwitterApiConstants.Errors.GUEST_AUTH_ERROR_CODE) {
            return new GuestAuthErrorException(message, error, config);
        } else if (isUnrecoverable(error)) {
            return new UnrecoverableException(message, error, config);
        } else {
            return new DigitsException(message, error, config);
        }
    }

    private static boolean isUnrecoverable(int error) {
        return error == TwitterApiErrorConstants.USER_IS_NOT_SDK_USER ||
                error == TwitterApiErrorConstants.EXPIRED_LOGIN_VERIFICATION_REQUEST ||
                error == TwitterApiErrorConstants.MISSING_LOGIN_VERIFICATION_REQUEST ||
                error == TwitterApiErrorConstants.DEVICE_REGISTRATION_RATE_EXCEEDED ||
                error == TwitterApiErrorConstants.REGISTRATION_GENERAL_ERROR;
    }

    private static String getMessageForApiError(ErrorCodes errors,
                                                TwitterApiException apiException) {
        String errorCodeMessage;
        final RetrofitError error = apiException.getRetrofitError();
        if (error.isNetworkError()) {
            errorCodeMessage = errors.getNetworkError();
        } else {
            errorCodeMessage = errors.getMessage(apiException.getErrorCode());
        }
        return errorCodeMessage;
    }

    public int getErrorCode() {
        return errorCode;
    }

    @NonNull
    public AuthConfig getConfig() {
        return config;
    }
}
