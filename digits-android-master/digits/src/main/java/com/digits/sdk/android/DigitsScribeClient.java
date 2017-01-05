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

import com.digits.sdk.android.DigitsScribeConstants.Component;
import com.digits.sdk.android.DigitsScribeConstants.Element;
import com.twitter.sdk.android.core.internal.scribe.DefaultScribeClient;
import com.twitter.sdk.android.core.internal.scribe.EventNamespace;

public class DigitsScribeClient {
    static final String SCRIBE_CLIENT = "tfw";
    static final String SCRIBE_PAGE = "android";
    static final String SCRIBE_SECTION = "digits";
    static final String LOGGED_IN_ACTION = "logged_in";

    static final EventNamespace.Builder DIGITS_EVENT_BUILDER = new EventNamespace.Builder()
            .setClient(SCRIBE_CLIENT)
            .setPage(SCRIBE_PAGE)
            .setSection(SCRIBE_SECTION);

    private DefaultScribeClient twitterScribeClient;

    public void impression(Component component) {
        final EventNamespace ns = DIGITS_EVENT_BUILDER
                .setComponent(component.getComponent())
                .setElement(Element.EMPTY.getElement())
                .setAction(DigitsScribeConstants.Action.IMPRESSION.getAction())
                .builder();
        safeScribe(ns);
    }

    public void failure(Component component) {
        final EventNamespace ns = DIGITS_EVENT_BUILDER
                .setComponent(component.getComponent())
                .setElement(Element.EMPTY.getElement())
                .setAction(DigitsScribeConstants.Action.FAILURE.getAction())
                .builder();
        safeScribe(ns);
    }

    public void click(Component component, Element element) {
        final EventNamespace ns = DIGITS_EVENT_BUILDER
                .setComponent(component.getComponent())
                .setElement(element.getElement())
                .setAction(DigitsScribeConstants.Action.CLICK.getAction())
                .builder();
        safeScribe(ns);
    }

    public void success(Component component) {
        final EventNamespace ns = DIGITS_EVENT_BUILDER
                .setComponent(component.getComponent())
                .setElement(Element.EMPTY.getElement())
                .setAction(DigitsScribeConstants.Action.SUCCESS.getAction())
                .builder();
        safeScribe(ns);
    }

    public void loginSuccess() {
        final EventNamespace ns = DIGITS_EVENT_BUILDER
                .setComponent(Component.EMPTY.getComponent())
                .setElement(Element.EMPTY.getElement())
                .setAction(LOGGED_IN_ACTION)
                .builder();
        safeScribe(ns);
    }

    public void error(Component component, DigitsException exception) {
        final EventNamespace ns = DIGITS_EVENT_BUILDER
                .setComponent(component.getComponent())
                .setElement(Element.EMPTY.getElement())
                .setAction(DigitsScribeConstants.Action.ERROR.getAction())
                .builder();

        safeScribe(ns, "error_code:" + exception.getErrorCode());
    }

    public void setTwitterScribeClient(DefaultScribeClient twitterScribeClient){
        if (twitterScribeClient == null) {
            throw new IllegalArgumentException("twitter scribe client must not be null");
        }
        this.twitterScribeClient = twitterScribeClient;
    }

    private void safeScribe(EventNamespace ns){
        if (twitterScribeClient != null) {
            twitterScribeClient.scribe(ns);
        }
    }

    private void safeScribe(EventNamespace ns, String eventInfo){
        if (twitterScribeClient != null) {
            twitterScribeClient.scribe(ns, eventInfo);
        }
    }
}
