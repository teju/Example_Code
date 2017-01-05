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

import android.app.IntentService;
import android.content.Intent;
import android.database.Cursor;

import com.twitter.sdk.android.core.TwitterApiException;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Locale;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.atomic.AtomicInteger;

import io.fabric.sdk.android.Fabric;
import io.fabric.sdk.android.Logger;
import io.fabric.sdk.android.services.concurrency.internal.DefaultRetryPolicy;
import io.fabric.sdk.android.services.concurrency.internal.ExponentialBackoff;
import io.fabric.sdk.android.services.concurrency.internal.RetryThreadPoolExecutor;
import retrofit.RetrofitError;
import retrofit.client.Response;

public class ContactsUploadService extends IntentService {
    private static final String THREAD_NAME = "UPLOAD_WORKER";
    private static final int TIMEOUT_IN_SECONDS = 300;
    public static final String UPLOAD_COMPLETE = "com.digits.sdk.android.UPLOAD_COMPLETE";
    public static final String UPLOAD_COMPLETE_EXTRA = "com.digits.sdk.android.UPLOAD_COMPLETE_EXTRA";
    public static final String UPLOAD_FAILED = "com.digits.sdk.android.UPLOAD_FAILED";
    public static final String UPLOAD_FAILED_EXTRA = "com.digits.sdk.android.UPLOAD_FAILED_EXTRA";
    public static final String EXCEPTION_LOG_FORMAT =
            "contact upload error, exception=%s";
    public static final String RETROFIT_ERROR_LOG_FORMAT =
            "contact upload error, status=%d, errorCode=%d, errorMessage=%s";
    private static final int MAX_RETRIES = 1;
    private static final int CORE_THREAD_POOL_SIZE = 2;
    private static final int INITIAL_BACKOFF_MS = 1000;
    private static final int MAX_PAGE_SIZE = 100;
    private DigitsApiClientManager clientManager;
    private DigitsEventCollector digitsEventCollector;
    private ContactsHelper helper;
    private ContactsPreferenceManager prefManager;
    private RetryThreadPoolExecutor executor;
    private Logger logger;
    private Locale locale;

    public ContactsUploadService() {
        super(THREAD_NAME);

        init(Digits.getInstance().getApiClientManager(),
                new ContactsHelper(this),
                new ContactsPreferenceManager(),
                new RetryThreadPoolExecutor(CORE_THREAD_POOL_SIZE,
                        new DefaultRetryPolicy(MAX_RETRIES),
                        new ExponentialBackoff(INITIAL_BACKOFF_MS)),
                Fabric.getLogger(), Locale.getDefault(),
                Digits.getInstance().getDigitsEventCollector());
    }

    /*
     * Testing only
     */
    ContactsUploadService(DigitsApiClientManager clientManager, ContactsHelper helper,
                          ContactsPreferenceManager prefManager, RetryThreadPoolExecutor executor,
                          Logger logger, Locale locale,
                          DigitsEventCollector digitsEventCollector) {
        super(THREAD_NAME);

        init(clientManager, helper, prefManager, executor, logger, locale, digitsEventCollector);
    }

    private void init(DigitsApiClientManager clientManager, ContactsHelper helper,
              ContactsPreferenceManager prefManager, RetryThreadPoolExecutor executor,
                      Logger logger, Locale locale, DigitsEventCollector digitsEventCollector) {
        this.clientManager = clientManager;
        this.helper = helper;
        this.prefManager = prefManager;
        this.executor = executor;
        this.logger = logger;
        this.locale = locale;
        this.digitsEventCollector = digitsEventCollector;

        setIntentRedelivery(true);
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        prefManager.setContactImportPermissionGranted();
        int totalCount = 0;
        final AtomicInteger successCount = new AtomicInteger(0);

        //noinspection TryWithIdenticalCatches
        try {
            final List<String> allCards = getAllCards();
            totalCount = allCards.size();
            final int pages = getNumberOfPages(totalCount);
            final List<Exception> retrofitErrors = Collections.synchronizedList(
                    new ArrayList<Exception>());
            for (int i = 0; i < pages; i++) {
                final int startIndex = i * MAX_PAGE_SIZE;
                final int endIndex = Math.min(totalCount, startIndex +
                        MAX_PAGE_SIZE);

                final List<String> subList = allCards.subList(startIndex, endIndex);
                final Vcards vCards = new Vcards(subList);
                executor.scheduleWithRetry(new Runnable() {
                    @Override
                    public void run() {
                        try {
                            clientManager.getApiClient().getService().upload(vCards);
                            successCount.addAndGet(vCards.vcards.size());
                        } catch (RetrofitError retrofitError) {
                            log(retrofitError);
                            retrofitErrors.add(retrofitError);
                        }
                    }
                });
            }

            executor.shutdown();
            final boolean success = executor.awaitTermination(TIMEOUT_IN_SECONDS, TimeUnit.SECONDS);

            if (success && successCount.get() > 0) {
                prefManager.setContactsReadTimestamp(System.currentTimeMillis());
                prefManager.setContactsUploaded(successCount.get());
                digitsEventCollector.succeedContactsUpload(
                        new ContactsUploadSuccessDetails(totalCount, successCount.get()));
                sendSuccessBroadcast(new ContactsUploadResult(successCount.get(), totalCount));
            } else {
                final int failedCount = totalCount - successCount.get();
                digitsEventCollector.failedContactsUpload(
                        new ContactsUploadFailureDetails(totalCount, failedCount));
                if (totalCount == 0) {
                    sendFailureBroadcast(new ContactsUploadFailureResult(
                            ContactsUploadFailureResult.Summary.NO_CONTACTS_FOUND));
                } else if (!success) {
                    executor.shutdownNow();
                    sendFailureBroadcast(ContactsUploadFailureResult.create(retrofitErrors));
                } else if (successCount.get() == 0) {
                    sendFailureBroadcast(ContactsUploadFailureResult.create(retrofitErrors));
                }
            }
        } catch (Exception ex) {
            log(ex);
            final int failedCount = totalCount - successCount.get();
            digitsEventCollector.failedContactsUpload(
                    new ContactsUploadFailureDetails(totalCount, failedCount));
            sendFailureBroadcast(ContactsUploadFailureResult.create(ex));
        }
    }

    int getNumberOfPages(int numCards) {
        return (numCards + MAX_PAGE_SIZE - 1) / MAX_PAGE_SIZE;
    }

    private List<String> getAllCards() {
        Cursor cursor = null;
        List<String> allCards = Collections.<String>emptyList();

        try {
            cursor = helper.getContactsCursor();
            allCards = helper.createContactList(cursor);
        } finally {
            if (cursor != null) {
                cursor.close();
            }
        }

        return allCards;
    }

    void sendFailureBroadcast(ContactsUploadFailureResult extra) {
        final Intent intent = new Intent(UPLOAD_FAILED);
        intent.putExtra(UPLOAD_FAILED_EXTRA, extra);
        sendBroadcast(intent);
    }

    void sendSuccessBroadcast(ContactsUploadResult extra) {
        final Intent intent = new Intent(UPLOAD_COMPLETE);
        intent.putExtra(UPLOAD_COMPLETE_EXTRA, extra);
        sendBroadcast(intent);
    }

    private void log(RetrofitError retrofitError) {
        if (retrofitError.getKind().equals(RetrofitError.Kind.HTTP)) {
            final Response response = retrofitError.getResponse();
            final int status = response == null ? 0 : response.getStatus();
            final TwitterApiException twitterApiException =
                    TwitterApiException.convert(retrofitError);
            logger.e(Digits.TAG, String.format(locale, RETROFIT_ERROR_LOG_FORMAT, status,
                    twitterApiException.getErrorCode(), twitterApiException.getErrorMessage()));
        } else {
            log((Exception) retrofitError);
        }
    }

    private void log(Exception e) {
        logger.e(Digits.TAG, String.format(locale, EXCEPTION_LOG_FORMAT, e.toString()));
    }
}
