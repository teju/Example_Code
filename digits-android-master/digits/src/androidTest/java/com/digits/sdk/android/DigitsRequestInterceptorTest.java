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


import io.fabric.sdk.android.FabricAndroidTestCase;

import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;
import static retrofit.RequestInterceptor.RequestFacade;

public class DigitsRequestInterceptorTest extends FabricAndroidTestCase {
    private static final String ANY_USER_AGENT = "Digits/Test (AwesomeApp Android Awesome)";

    public void testIntercept() throws Exception {
        final DigitsUserAgent userAgent = mock(MockDigitsUserAgent.class);
        final DigitsRequestInterceptor interceptor = new DigitsRequestInterceptor(userAgent);
        final RequestFacade facade = mock(RequestFacade.class);

        when(userAgent.toString()).thenReturn(ANY_USER_AGENT);

        interceptor.intercept(facade);

        verify(facade).addHeader(DigitsRequestInterceptor.USER_AGENT_KEY, ANY_USER_AGENT);
    }


}
