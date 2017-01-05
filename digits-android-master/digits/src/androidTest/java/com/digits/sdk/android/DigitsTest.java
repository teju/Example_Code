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

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import java.util.Arrays;
import java.util.List;
import java.util.concurrent.Callable;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;

import io.fabric.sdk.android.Fabric;
import io.fabric.sdk.android.FabricTestUtils;
import io.fabric.sdk.android.Kit;

public class DigitsTest extends DigitsAndroidTestCase {

    public void testGetIdentifier() {
        final Kit kit = new Digits();
        final String identifier = BuildConfig.GROUP + ":" + BuildConfig.ARTIFACT_ID;
        assertEquals(identifier, kit.getIdentifier());
    }

    public void testGetDigitClients_multipleThreads() throws Exception {
        try {
            Fabric.with(getContext(), new TwitterCore(new TwitterAuthConfig("", "")), new Digits());
            final List<DigitsClientCallable> callables = Arrays.asList(
                    new DigitsClientCallable(),
                    new DigitsClientCallable());
            final ExecutorService executorService = Executors.newFixedThreadPool(callables.size());
            final List<Future<DigitsClient>> digitsClients = executorService.invokeAll(callables);

            assertNotNull(digitsClients.get(0).get());
            assertNotNull(digitsClients.get(1).get());
            assertSame(digitsClients.get(0).get(), digitsClients.get(1).get());
        } finally {
            FabricTestUtils.resetFabric();
        }
    }

    private static class DigitsClientCallable implements Callable<DigitsClient> {
        @Override
        public DigitsClient call() {
            final Digits digits = Fabric.getKit(Digits.class);
            return digits.getDigitsClient();
        }
    }

    public void testDigits_constructor() throws Exception {
        final Digits digits = new Digits();
        assertNotNull(digits.getDigitsEventCollector());
    }
}
