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

class DigitsScribeConstants {
    enum Element {
        COUNTRY_CODE("country_code"), SUBMIT("submit"), RETRY("retry"), BACK ("back"),
        CALL("call"), CANCEL ("cancel"), RESEND("resend"), DISMISS("dismiss"), EMPTY("");

        private final String element;

        Element(String element) {
            this.element = element;
        }

        public String getElement() {
            return element;
        }
    }

    enum Component {
        AUTH("auth"), LOGIN("login"), SIGNUP("signup"), PIN("pin"), EMAIL("email"),
        CONTACTS("contacts"), FAILURE("failure"), EMPTY("");

        private final String component;

        Component(String component) {
            this.component = component;
        }

        public String getComponent() {
            return component;
        }
    }

    enum Action{
        IMPRESSION("impression"), FAILURE("failure"), SUCCESS("success"), CLICK("click"),
        ERROR("error");

        private final String action;

        Action(String action) {
            this.action = action;
        }

        public String getAction() {
            return action;
        }
    }
}
