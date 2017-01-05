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
import android.os.Bundle;
import android.test.UiThreadTest;

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import io.fabric.sdk.android.FabricActivityTestCase;
import io.fabric.sdk.android.FabricTestUtils;

import static org.mockito.Matchers.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

public class DigitsActivityTests extends
        FabricActivityTestCase<DigitsActivityTests.DummyDigitsActivity> {

    private static final int ANY_REQUEST = 1010;
    private static final int ANY_RESULT = Activity.RESULT_OK;
    private static final String BUNDLE_VALID_EXTRA = "BUNDLE_VALID_EXTRA";

    Context context;
    DigitsController controller;

    public DigitsActivityTests() {
        super(DummyDigitsActivity.class);
    }

    @Override
    protected void setUp() throws Exception {
        super.setUp();

        context = getInstrumentation().getContext();
        controller = mock(DigitsController.class);
        FabricTestUtils.resetFabric();
        FabricTestUtils.with(context, new TwitterCore(
                new TwitterAuthConfig("", "")), new Digits());
    }

    @Override
    public void tearDown() throws Exception {
        FabricTestUtils.resetFabric();
        super.tearDown();
    }

    public DigitsActivity createDigitsActivityWithValidBundle() {
        final Intent intent = new Intent(context, DummyDigitsActivity.class);
        intent.putExtra(BUNDLE_VALID_EXTRA, true);
        return startActivity(intent, null, null);
    }

    public DigitsActivity createDigitsActivityWithInvalidBundle() {
        final Intent intent = new Intent(context, DummyDigitsActivity.class);
        intent.putExtra(BUNDLE_VALID_EXTRA, false);
        return startActivity(intent, null, null);
    }

    @UiThreadTest
    public void testOnCreate_validBundle() throws Exception {
        final DigitsActivity activity = createDigitsActivityWithValidBundle();
        verify(activity.delegate).isValid(any(Bundle.class));
        verify(activity.delegate).getLayoutId();
        verify(activity.delegate).init(any(Activity.class), any(Bundle.class));
    }

    @UiThreadTest
    public void testOnCreate_invalidBundle() throws Exception {
        try {
            createDigitsActivityWithInvalidBundle();
            fail("Should have thrown IllegalAccessError");
        } catch (IllegalAccessError ex) {
            assertEquals("This activity can only be started from Digits", ex.getMessage());
        }
    }

    @UiThreadTest
    public void testOnResume() throws Exception {
        final DigitsActivity activity = createDigitsActivityWithValidBundle();
        activity.onResume();
        verify(activity.delegate).onResume();
    }

    @UiThreadTest
    public void testOnDestroy() throws Exception {
        final DigitsActivity activity = createDigitsActivityWithValidBundle();
        activity.onDestroy();
        verify(activity.delegate).onDestroy();
    }

    @UiThreadTest
    public void testOnActivityResult() throws Exception {
        final DigitsActivity activity = createDigitsActivityWithValidBundle();
        activity.onActivityResult(DigitsActivity.REQUEST_CODE,
                DigitsActivity.RESULT_FINISH_DIGITS, null);
        assertTrue(isFinishCalled());
        verify(activity.delegate).onActivityResult(DigitsActivity.REQUEST_CODE,
                DigitsActivity.RESULT_FINISH_DIGITS, activity);
    }

    @UiThreadTest
    public void testOnActivityResult_notFinishedWithAnyResult() throws Exception {
        final DigitsActivity activity = createDigitsActivityWithValidBundle();
        activity.onActivityResult(DigitsActivity.REQUEST_CODE, ANY_RESULT, null);
        assertFalse(isFinishCalled());
        verify(activity.delegate).onActivityResult(DigitsActivity.REQUEST_CODE, ANY_RESULT,
                activity);
    }

    @UiThreadTest
    public void testOnActivityResult_notFinishedWithAnyRequest() throws Exception {
        final DigitsActivity activity = createDigitsActivityWithValidBundle();
        activity.onActivityResult(ANY_REQUEST, ANY_RESULT, null);
        assertFalse(isFinishCalled());
        verify(activity.delegate).onActivityResult(ANY_REQUEST, ANY_RESULT, activity);
    }

    public static class DummyDigitsActivity extends DigitsActivity {
        @Override
        DigitsActivityDelegate getActivityDelegate() {
            final DigitsActivityDelegate delegate = mock(DigitsActivityDelegate.class);
            if (getIntent().getBooleanExtra(BUNDLE_VALID_EXTRA, true)) {
                when(delegate.isValid(any(Bundle.class))).thenReturn(Boolean.TRUE);
            } else {
                when(delegate.isValid(any(Bundle.class))).thenReturn(Boolean.FALSE);
            }
            when(delegate.getLayoutId()).thenReturn(R.layout.dgts__activity_confirmation);

            return delegate;
        }
    }
}
