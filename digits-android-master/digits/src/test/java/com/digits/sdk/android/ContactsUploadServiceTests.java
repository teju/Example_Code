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

import android.content.Intent;
import android.database.Cursor;

import com.google.gson.Gson;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.ArgumentCaptor;
import org.mockito.invocation.InvocationOnMock;
import org.mockito.stubbing.Answer;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.io.UnsupportedEncodingException;
import java.util.Collections;
import java.util.HashSet;
import java.util.Set;
import java.util.List;
import java.util.ArrayList;
import java.util.Locale;
import java.util.concurrent.TimeUnit;

import io.fabric.sdk.android.Logger;
import io.fabric.sdk.android.services.concurrency.internal.RetryThreadPoolExecutor;
import retrofit.RetrofitError;
import retrofit.client.Header;
import retrofit.client.Response;
import retrofit.mime.TypedByteArray;

import static org.junit.Assert.assertEquals;
import static org.mockito.Matchers.any;
import static org.mockito.Matchers.anyLong;
import static org.mockito.Matchers.eq;
import static org.mockito.Mockito.doAnswer;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.spy;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.verifyNoMoreInteractions;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class ContactsUploadServiceTests {
    private Cursor cursor;
    private ContactsHelper helper;
    private RetryThreadPoolExecutor executor;
    private ApiInterface sdkService;
    private DigitsApiClientManager clientManager;
    private DigitsApiClient apiClient;
    private ContactsPreferenceManager perfManager;
    private ArrayList<String> cradList;
    private ContactsUploadService service;
    private Logger logger;
    private ArgumentCaptor<Intent> intentCaptor;
    private DigitsEventCollector digitsEventCollector;


    @Before
    public void setUp() throws Exception {
        executor = mock(RetryThreadPoolExecutor.class);
        perfManager = mock(MockContactsPreferenceManager.class);
        sdkService = mock(ApiInterface.class);
        digitsEventCollector = mock(DigitsEventCollector.class);
        apiClient = mock(DigitsApiClient.class);
        clientManager = mock(DigitsApiClientManager.class);
        when(clientManager.getApiClient()).thenReturn(apiClient);
        when(apiClient.getService()).thenReturn(sdkService);
        logger = mock(Logger.class);
        cursor = ContactsHelperTests.createCursor();
        cradList = ContactsHelperTests.createCardList();
        intentCaptor = ArgumentCaptor.forClass(Intent.class);
        helper = mock(ContactsHelper.class);
        when(helper.getContactsCursor()).thenReturn(cursor);
        when(helper.createContactList(cursor)).thenReturn(cradList);

        service = spy(new ContactsUploadService(clientManager, helper, perfManager, executor,
                logger, Locale.JAPANESE, digitsEventCollector));
    }

    @Test
    public void testOnHandleIntent() throws Exception {
        when(executor.awaitTermination(anyLong(), any(TimeUnit.class))).thenReturn(true);
        doAnswer(new Answer() {
            @Override
            public Object answer(InvocationOnMock invocationOnMock) throws Throwable {
                ((Runnable) invocationOnMock.getArguments()[0]).run();
                return null;
            }
        }).when(executor).scheduleWithRetry(any(Runnable.class));

        service.onHandleIntent(null);

        verify(helper).getContactsCursor();
        verify(helper).createContactList(cursor);
        verify(executor).scheduleWithRetry(any(Runnable.class));
        verify(executor).shutdown();
        verify(executor).awaitTermination(anyLong(), any(TimeUnit.class));

        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_COMPLETE, intentCaptor.getValue().getAction());

        verify(perfManager).setContactImportPermissionGranted();
        verify(perfManager).setContactsUploaded(cradList.size());
        verify(perfManager).setContactsReadTimestamp(anyLong());

        final ContactsUploadResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_COMPLETE_EXTRA);
        assertEquals(cradList.size(), result.successCount);
        assertEquals(cradList.size(), result.totalCount);

        verify(digitsEventCollector).succeedContactsUpload(any(ContactsUploadSuccessDetails.class));

    }

    @Test
    public void testOnHandleIntent_rateLimit() throws Exception {
        when(executor.awaitTermination(anyLong(), any(TimeUnit.class))).thenReturn(true);
        doAnswer(new Answer() {
            @Override
            public Object answer(InvocationOnMock invocationOnMock) throws Throwable {
                ((Runnable) invocationOnMock.getArguments()[0]).run();
                return null;
            }
        }).when(executor).scheduleWithRetry(any(Runnable.class));
        final RetrofitError retrofitError = mock(RetrofitError.class);
        final int errorCode = 88;
        final int httpStatus = 429;
        final String errorMessage = "Rate limit";
        final UploadError apiError = new UploadError(errorCode, errorMessage, 0);
        final List<UploadError> apiErrors = new ArrayList<>();
        apiErrors.add(apiError);
        final UploadResponse uploadResponse = new UploadResponse(apiErrors);
        final Response response = createResponse(httpStatus, toJson(uploadResponse));
        when(retrofitError.getResponse()).thenReturn(response);
        when(retrofitError.getKind()).thenReturn(RetrofitError.Kind.HTTP);
        when(retrofitError.getStackTrace()).thenReturn(new StackTraceElement[0]);
        when(sdkService.upload(any(Vcards.class))).thenThrow(retrofitError);

        service.onHandleIntent(null);

        final ArgumentCaptor<String> captor = ArgumentCaptor.forClass(String.class);
        verify(logger).e(eq(Digits.TAG), captor.capture());
        assertEquals(captor.getValue(), String.format(Locale.JAPANESE,
                ContactsUploadService.RETROFIT_ERROR_LOG_FORMAT, httpStatus, apiError.code,
                apiError.message));

        verify(service).sendBroadcast(intentCaptor.capture());
        final ContactsUploadFailureResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_FAILED_EXTRA);
        assertEquals(ContactsUploadFailureResult.Summary.RATE_LIMIT, result.summary);
        verify(digitsEventCollector).failedContactsUpload(any(ContactsUploadFailureDetails.class));

    }

    @Test
    public void testOnHandleIntent_nullApiError() throws Exception {
        when(executor.awaitTermination(anyLong(), any(TimeUnit.class))).thenReturn(true);
        doAnswer(new Answer() {
            @Override
            public Object answer(InvocationOnMock invocationOnMock) throws Throwable {
                ((Runnable) invocationOnMock.getArguments()[0]).run();
                return null;
            }
        }).when(executor).scheduleWithRetry(any(Runnable.class));
        final RetrofitError retrofitError = mock(RetrofitError.class);
        final int status = 401;
        final String body = "{}";
        final Response response = createResponse(status, body);
        final String exceptionString = "exceptional!";
        when(retrofitError.getResponse()).thenReturn(response);
        when(retrofitError.getKind()).thenReturn(RetrofitError.Kind.CONVERSION);
        when(retrofitError.getStackTrace()).thenReturn(new StackTraceElement[0]);
        when(retrofitError.toString()).thenReturn(exceptionString);
        when(sdkService.upload(any(Vcards.class))).thenThrow(retrofitError);

        service.onHandleIntent(null);

        final ArgumentCaptor<String> captor = ArgumentCaptor.forClass(String.class);
        verify(logger).e(eq(Digits.TAG), captor.capture());
        assertEquals(captor.getValue(), String.format(Locale.JAPANESE,
                ContactsUploadService.EXCEPTION_LOG_FORMAT, exceptionString));

        verify(service).sendBroadcast(intentCaptor.capture());
        final ContactsUploadFailureResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_FAILED_EXTRA);
        assertEquals(ContactsUploadFailureResult.Summary.PARSING, result.summary);
        verify(digitsEventCollector).failedContactsUpload(any(ContactsUploadFailureDetails.class));

    }

    @Test
    public void testOnHandleIntent_exception() throws Exception {
        final Exception exception = new NullPointerException("trolololo");
        when(helper.getContactsCursor()).thenThrow(exception);

        service.onHandleIntent(null);

        final ArgumentCaptor<String> captor = ArgumentCaptor.forClass(String.class);
        verify(logger).e(eq(Digits.TAG), captor.capture());
        assertEquals(captor.getValue(), String.format(Locale.JAPANESE,
                ContactsUploadService.EXCEPTION_LOG_FORMAT, exception.toString()));

        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_FAILED, intentCaptor.getValue().getAction());
        final ContactsUploadFailureResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_FAILED_EXTRA);
        assertEquals(ContactsUploadFailureResult.Summary.UNEXPECTED, result.summary);
        verify(digitsEventCollector).failedContactsUpload(any(ContactsUploadFailureDetails.class));
    }

    @Test
    public void testOnHandleIntent_securityException() throws Exception {
        final SecurityException exception = new SecurityException("Permission Denial...");
        when(helper.getContactsCursor()).thenThrow(exception);

        service.onHandleIntent(null);

        final ArgumentCaptor<String> captor = ArgumentCaptor.forClass(String.class);
        verify(logger).e(eq(Digits.TAG), captor.capture());
        assertEquals(captor.getValue(), String.format(Locale.JAPANESE,
                ContactsUploadService.EXCEPTION_LOG_FORMAT, exception.toString()));

        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_FAILED, intentCaptor.getValue().getAction());
        final ContactsUploadFailureResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_FAILED_EXTRA);
        assertEquals(ContactsUploadFailureResult.Summary.PERMISSION, result.summary);
        verify(digitsEventCollector).failedContactsUpload(any(ContactsUploadFailureDetails.class));
    }

    @Test
    public void testOnHandleIntent_uploadTimeout() throws Exception {
        when(executor.awaitTermination(anyLong(), any(TimeUnit.class))).thenReturn(false);

        service.onHandleIntent(null);

        verify(helper).getContactsCursor();
        verify(helper).createContactList(cursor);
        verify(executor).scheduleWithRetry(any(Runnable.class));
        verify(executor).shutdown();
        verify(executor).awaitTermination(anyLong(), any(TimeUnit.class));
        verify(executor).shutdownNow();

        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_FAILED, intentCaptor.getValue().getAction());
        final ContactsUploadFailureResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_FAILED_EXTRA);
        assertEquals(ContactsUploadFailureResult.Summary.UNEXPECTED, result.summary);

        verify(perfManager).setContactImportPermissionGranted();
        verifyNoMoreInteractions(perfManager);
        verify(digitsEventCollector).failedContactsUpload(any(ContactsUploadFailureDetails.class));
    }

    @Test
    public void testGetNumberOfPages() {
        assertEquals(1, service.getNumberOfPages(100));
        assertEquals(1, service.getNumberOfPages(50));
        assertEquals(2, service.getNumberOfPages(101));
        assertEquals(2, service.getNumberOfPages(199));
    }

    @Test
    public void testSendFailureBroadcast() {
        service.sendFailureBroadcast(ContactsUploadFailureResult.create(
                Collections.<Exception>emptyList()));
        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_FAILED, intentCaptor.getValue().getAction());
        final ContactsUploadFailureResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_FAILED_EXTRA);
        assertEquals(ContactsUploadFailureResult.Summary.UNEXPECTED, result.summary);
    }

    @Test
    public void testSendSuccessBroadcast() {
        service.sendSuccessBroadcast(new ContactsUploadResult(1, 1));

        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_COMPLETE, intentCaptor.getValue().getAction());
        final ContactsUploadResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_COMPLETE_EXTRA);
        assertEquals(1, result.successCount);
        assertEquals(1, result.totalCount);
    }

    // Response is final, which isn't mockable by Mockito, so this fn creates a stub.
    Response createResponse(int status, String body) throws UnsupportedEncodingException {
        final List<Header> headers = new ArrayList<>();
        return new Response("url", status, "reason", headers,
                new TypedByteArray("application/json", body.getBytes("UTF-8")));
    }

    @Test
    public void testUploadEventCounts() throws Exception {
        final TestDigitsEventCollector collector = new TestDigitsEventCollector(null, null, null);
        service = spy(new ContactsUploadService(clientManager, helper, perfManager, executor,
                logger, Locale.JAPANESE, collector));
        when(executor.awaitTermination(anyLong(), any(TimeUnit.class))).thenReturn(true);
        doAnswer(new Answer() {
            @Override
            public Object answer(InvocationOnMock invocationOnMock) throws Throwable {
                ((Runnable) invocationOnMock.getArguments()[0]).run();
                return null;
            }
        }).when(executor).scheduleWithRetry(any(Runnable.class));

        service.onHandleIntent(null);

        verify(helper).getContactsCursor();
        verify(helper).createContactList(cursor);
        verify(executor).scheduleWithRetry(any(Runnable.class));
        verify(executor).shutdown();
        verify(executor).awaitTermination(anyLong(), any(TimeUnit.class));

        verify(service).sendBroadcast(intentCaptor.capture());
        assertEquals(ContactsUploadService.UPLOAD_COMPLETE, intentCaptor.getValue().getAction());

        verify(perfManager).setContactImportPermissionGranted();
        verify(perfManager).setContactsUploaded(cradList.size());
        verify(perfManager).setContactsReadTimestamp(anyLong());

        final ContactsUploadResult result = intentCaptor.getValue()
                .getParcelableExtra(ContactsUploadService.UPLOAD_COMPLETE_EXTRA);
        assertEquals(cradList.size(), result.successCount);
        assertEquals(cradList.size(), result.totalCount);

        assertEquals(collector.events.size(), 1);
        final ContactsUploadSuccessDetails details =
                (ContactsUploadSuccessDetails) collector.events.get(0);
        assertEquals(details.successContacts, 1);
        assertEquals(details.totalContacts, 1);
    }

    String toJson(UploadResponse response) {
        return new Gson().toJson(response);
    }

    class TestDigitsEventCollector extends DigitsEventCollector {
        List<Object> events;

        TestDigitsEventCollector(DigitsScribeClient client,
                                 FailFastEventDetailsChecker checker,
                                 Set<DigitsEventLogger> loggers){
            super(mock(DigitsScribeClient.class), mock(FailFastEventDetailsChecker.class),
                    new HashSet<DigitsEventLogger>());
            this.events = new ArrayList<Object>();
        }

        @Override
        public void startContactsUpload(ContactsUploadStartDetails details) {
            events.add(details);

        }

        @Override
        public void succeedContactsUpload(ContactsUploadSuccessDetails details) {
            events.add(details);
        }

        @Override
        public void failedContactsUpload(ContactsUploadFailureDetails details) {
            events.add(details);
        }
    }
}
