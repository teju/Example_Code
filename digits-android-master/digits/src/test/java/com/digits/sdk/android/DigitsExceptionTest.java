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

import com.twitter.sdk.android.core.MockDigitsApiException;
import com.twitter.sdk.android.core.TwitterApiErrorConstants;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.models.ApiError;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.Mockito;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import retrofit.RetrofitError;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertTrue;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class DigitsExceptionTest {

    private static final String RANDOM_ERROR = "Random error";
    private static final String KNOWN_ERROR_MESSAGE = "Something bad happened call batman";
    private static final String NETWORK_ERROR_MESSAGE = "network wonky";
    private static final String DEFAULT_ERROR_MESSAGE = "try again";
    private static final int SOME_ERROR_CODE = 0;
    private RetrofitError retrofitError;
    private ErrorCodes errorCodes;

    @Before
    public void setUp() throws Exception {

        retrofitError = mock(RetrofitError.class);
        when(retrofitError.isNetworkError()).thenReturn(false);
        errorCodes = Mockito.mock(ErrorCodes.class);
        when(errorCodes.getMessage(TwitterApiErrorConstants.REGISTRATION_GENERAL_ERROR))
                .thenReturn(KNOWN_ERROR_MESSAGE);
        when(errorCodes.getMessage(TwitterApiErrorConstants.COULD_NOT_AUTHENTICATE))
                .thenReturn(KNOWN_ERROR_MESSAGE);
        when(errorCodes.getMessage(TwitterApiErrorConstants.OPERATOR_UNSUPPORTED))
                .thenReturn(KNOWN_ERROR_MESSAGE);
        when(errorCodes.getNetworkError()).thenReturn(NETWORK_ERROR_MESSAGE);
        when(errorCodes.getDefaultMessage()).thenReturn(DEFAULT_ERROR_MESSAGE);
        when(errorCodes.getMessage(SOME_ERROR_CODE)).thenReturn(DEFAULT_ERROR_MESSAGE);
    }

    @Test
    public void testCreate_unknownError() throws Exception {
        final TwitterException exception = new TwitterException(RANDOM_ERROR);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertEquals(DEFAULT_ERROR_MESSAGE, digitsException.getLocalizedMessage());
        assertEquals(TwitterApiErrorConstants.UNKNOWN_ERROR, digitsException.getErrorCode());
    }


    @Test
    public void testCreate_unknownErrorTwitterApiException() throws Exception {
        final TwitterException exception = new MockDigitsApiException(new ApiError("",
                SOME_ERROR_CODE), null, retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(false);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertEquals(DEFAULT_ERROR_MESSAGE, digitsException.getLocalizedMessage());
        assertEquals(SOME_ERROR_CODE, digitsException.getErrorCode());
    }

    @Test
    public void testCreate_networkError() throws Exception {
        final TwitterException exception = new MockDigitsApiException(new ApiError("",
                SOME_ERROR_CODE), null, retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(true);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertEquals(NETWORK_ERROR_MESSAGE, digitsException.getLocalizedMessage());
        assertEquals(SOME_ERROR_CODE, digitsException.getErrorCode());
    }

    @Test
    public void testCreate_knownError() throws Exception {
        final TwitterException exception = new MockDigitsApiException
                (new ApiError("", TwitterApiErrorConstants.REGISTRATION_GENERAL_ERROR), null,
                        retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(false);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertEquals(KNOWN_ERROR_MESSAGE, digitsException.getLocalizedMessage());
        assertTrue(digitsException instanceof UnrecoverableException);
        assertEquals(TwitterApiErrorConstants.REGISTRATION_GENERAL_ERROR,
                digitsException.getErrorCode());
    }

    @Test
    public void testCreate_couldNotAuthenticate() throws Exception {
        final TwitterException exception = new MockDigitsApiException
                (new ApiError("", TwitterApiErrorConstants.COULD_NOT_AUTHENTICATE), null,
                        retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(false);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertEquals(KNOWN_ERROR_MESSAGE, digitsException.getLocalizedMessage());
        assertTrue(digitsException instanceof CouldNotAuthenticateException);
        assertEquals(TwitterApiErrorConstants.COULD_NOT_AUTHENTICATE,
                digitsException.getErrorCode());
    }

    @Test
    public void testCreate_operatorUnsupported() throws Exception {
        final TwitterException exception = new MockDigitsApiException
                (new ApiError("", TwitterApiErrorConstants.OPERATOR_UNSUPPORTED), null,
                        retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(false);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertEquals(KNOWN_ERROR_MESSAGE, digitsException.getLocalizedMessage());
        assertTrue(digitsException instanceof OperatorUnsupportedException);
        assertEquals(TwitterApiErrorConstants.OPERATOR_UNSUPPORTED,
                digitsException.getErrorCode());
    }

    @Test
    public void testCreate_notEligibleForOneFactor() throws Exception {
        verifyUnrecoverableException(TwitterApiErrorConstants.USER_IS_NOT_SDK_USER);
    }

    @Test
    public void testCreate_expiredLoginVerification() throws Exception {
        verifyUnrecoverableException(TwitterApiErrorConstants.EXPIRED_LOGIN_VERIFICATION_REQUEST);
    }

    @Test
    public void testCreate_missingLoginVerification() throws Exception {
        verifyUnrecoverableException(TwitterApiErrorConstants.MISSING_LOGIN_VERIFICATION_REQUEST);
    }

    @Test
    public void testCreate_deviceRegistrationRate() throws Exception {
        verifyUnrecoverableException(TwitterApiErrorConstants.DEVICE_REGISTRATION_RATE_EXCEEDED);
    }

    public void verifyUnrecoverableException(int error) {
        final TwitterException exception = new MockDigitsApiException(new ApiError("", error),
                null, retrofitError);
        when(retrofitError.isNetworkError()).thenReturn(false);
        final DigitsException digitsException = DigitsException.create(errorCodes, exception);
        assertTrue(digitsException instanceof UnrecoverableException);
    }
}
