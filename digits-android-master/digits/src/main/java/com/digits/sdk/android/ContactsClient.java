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

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.TwitterException;

import retrofit.client.Response;

public class ContactsClient {
    private final ContactsPreferenceManager prefManager;
    private ActivityClassManagerFactory activityClassManagerFactory;
    private final DigitsApiClientManager apiClientManager;
    private final Digits digits;
    private SandboxConfig sandboxConfig;
    private final DigitsEventCollector digitsEventCollector;

    ContactsClient(DigitsApiClientManager apiManager) {
        this(Digits.getInstance(),
                apiManager,
                new ContactsPreferenceManager(),
                new ActivityClassManagerFactory(),
                Digits.getInstance().getSandboxConfig(),
                Digits.getInstance().getDigitsEventCollector());
    }

    ContactsClient(Digits digits, DigitsApiClientManager apiManager,
                   ContactsPreferenceManager prefManager,
                   ActivityClassManagerFactory activityClassManagerFactory,
                   SandboxConfig sandboxConfig,
                   DigitsEventCollector digitsEventCollector) {
        this.digits = digits;
        this.apiClientManager = apiManager;
        this.prefManager = prefManager;
        this.activityClassManagerFactory = activityClassManagerFactory;
        this.sandboxConfig = sandboxConfig;
        this.digitsEventCollector = digitsEventCollector;
    }

    /**
     * First checks if user previously gave permission to upload contacts. If not, shows
     * dialog requesting permission to upload users contacts. If permission granted start
     * background service to upload contacts. Otherwise, do nothing.
     */
    public void startContactsUpload() {
        startContactsUpload(R.style.Digits_default);
    }

    /**
     * Like {@link #startContactsUpload}, but enables theming.
     *
     * @param themeResId Resource id of theme
     */
    public void startContactsUpload(int themeResId) {
        startContactsUpload(themeResId, null);
    }

    /**
     * Like {@link #startContactsUpload(int themeResId)}, but defines request code passed to
     * {@link android.app.Activity#startActivityForResult(android.content.Intent, int)}.
     *
     * @param themeResId Resource id of theme
     * @param requestCode Request code
     */
    public void startContactsUpload(int themeResId, Integer requestCode) {
        digitsEventCollector.startContactsUpload(new ContactsUploadStartDetails());
        if (sandboxConfig.isMode(SandboxConfig.Mode.DEFAULT)) {
            sandboxedContactUpload(themeResId);
        } else if (hasUserGrantedPermission()) {
            startContactsService(digits.getContext());
        } else {
            startContactsActivity(digits.getContext(), themeResId, requestCode);
        }
    }

    /**
     * Returns true if user has previously granted contacts upload permission. Otherwise, returns
     * false.
     */
    public boolean hasUserGrantedPermission() {
        return prefManager.hasContactImportPermissionGranted();
    }

    protected void sandboxedContactUpload(int themeResId){
        final Intent intent = new Intent(ContactsUploadService.UPLOAD_COMPLETE);
        intent.putExtra(ContactsUploadService.UPLOAD_COMPLETE_EXTRA,
                new ContactsUploadResult(2, 2));
        intent.putExtra(ThemeUtils.THEME_RESOURCE_ID, themeResId);
        digits.getContext().sendBroadcast(intent);
    }

    protected void startContactsActivity(Context context, int themeResId, Integer requestCode) {
        final ActivityClassManager activityClassManager =
                activityClassManagerFactory.createActivityClassManager(context, themeResId);
        final Activity activity = digits.getFabric().getCurrentActivity();
        final boolean isActivityDefined = activity != null && !activity.isFinishing();
        final Intent intent = new Intent(context, activityClassManager.getContactsActivity());
        intent.putExtra(ThemeUtils.THEME_RESOURCE_ID, themeResId);
        if (isActivityDefined) {
            if (requestCode == null) {
                activity.startActivity(intent);
            } else {
                activity.startActivityForResult(intent, requestCode);
            }
        } else {
            context.startActivity(intent.setFlags(
                    Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP));
        }
    }

    protected void startContactsService(Context context) {
        context.startService(new Intent(context, ContactsUploadService.class));
    }

    protected ApiInterface getDigitsApiService() {
        return apiClientManager.getService();
    }

    /**
     * Deletes all uploaded contacts.
     *
     * @param callback to be executed on UI thread with HTTP response.
     */
    public void deleteAllUploadedContacts(final ContactsCallback<Response> callback) {
        digitsEventCollector.startDeleteContacts(new ContactsDeletionStartDetails());
        //We pass an empty String into the body to work around the an error in Okhttp 2.3+
        //See: https://github.com/square/retrofit/issues/854
        getDigitsApiService().deleteAll("",
                new DeleteContactsCallbackWrapper(callback, digitsEventCollector));
    }

    /**
     * Retrieve all matched contacts. Handles paging, and makes callback
     * when all matches are retrieved
     *
     * @param callback   to be executed on UI thread with matched users.
     */
    public void lookupContactMatchesStart(final Callback<Contacts> callback) {
        lookupContactMatches(null, 100, callback);
    }

    /**
     * Lookup matched contacts.
     *
     * @param nextCursor reference to next set of results. If null returns the first 100 users.
     * @param count      number of results to return. Min value is 1. Max value is 100. Default
     *                   value is 50. Values out of range will return default.
     * @param callback   to be executed on UI thread with matched users.
     */
    public void lookupContactMatches(final String nextCursor, final Integer count,
                                        final Callback<Contacts> callback) {
        digitsEventCollector.startFindMatches(new ContactsLookupStartDetails(nextCursor));
        final FoundContactsCallbackWrapper wrappedCallback =
                new FoundContactsCallbackWrapper(callback, digitsEventCollector);

        if (sandboxConfig.isMode(SandboxConfig.Mode.DEFAULT)) {
            MockApiInterface.createAllContacts(wrappedCallback);
        } else if (count == null || count < 1 || count > 100) {
            getDigitsApiService().usersAndUploadedBy(nextCursor, null, wrappedCallback);
        } else {
            getDigitsApiService().usersAndUploadedBy(nextCursor, count, wrappedCallback);
        }
    }

    UploadResponse uploadContacts(Vcards vcards) {
        return getDigitsApiService().upload(vcards);
    }

    class FoundContactsCallbackWrapper extends Callback<Contacts> {
        final Callback<Contacts> callback;
        final DigitsEventCollector digitsEventCollector;

        public FoundContactsCallbackWrapper(Callback<Contacts> callback,
                                            DigitsEventCollector digitsEventCollector) {
            this.callback = callback;
            this.digitsEventCollector = digitsEventCollector;
        }

        @Override
        public void success(Result<Contacts> result) {
            if (result.data != null && result.data.users != null) {
                digitsEventCollector.succeedFindMatches(
                        new ContactsLookupSuccessDetails(result.data.users.size()));
            }
            if (callback != null) {
                callback.success(result);
            }
        }

        @Override
        public void failure(TwitterException exception) {
            digitsEventCollector.failedFindMatches(new ContactsLookupFailureDetails());
            if (callback != null) {
                callback.failure(exception);
            }
        }
    }

    class DeleteContactsCallbackWrapper extends ContactsCallback<Response> {
        final ContactsCallback<Response>  callback;
        final DigitsEventCollector digitsEventCollector;

        public DeleteContactsCallbackWrapper(ContactsCallback<Response>  callback,
                                            DigitsEventCollector digitsEventCollector) {
            this.callback = callback;
            this.digitsEventCollector = digitsEventCollector;
        }

        @Override
        public void success(Result<Response> result) {
            digitsEventCollector.succeedDeleteContacts(new ContactsDeletionSuccessDetails());
            if (callback != null) {
                callback.success(result);
            }
        }

        @Override
        public void failure(TwitterException exception) {
            digitsEventCollector.failedDeleteContacts(new ContactsDeletionFailureDetails());
            if (callback != null) {
                callback.failure(exception);
            }
        }
    }

}
