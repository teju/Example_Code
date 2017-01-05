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

@Beta(Beta.Feature.Sandbox)
public class SandboxConfig {
    private boolean enabled;
    private Mode mode;
    private ApiInterface mock;

    public SandboxConfig() {
        this(false, Mode.DEFAULT, new MockApiInterface());
    }

    public SandboxConfig(boolean enabled,
                         Mode mode,
                         ApiInterface mock) {
        this.enabled = enabled;
        this.mode = mode;
        this.mock = mock;
    }

    protected void enable(){
        this.enabled = true;
    }

    protected void disable(){
        this.enabled = false;
    }

    protected void setMode(Mode sandboxMode){
        this.mode = sandboxMode;
    }

    protected void setMock(ApiInterface mockInterface){
        this.mock = mockInterface;
    }

    protected boolean isEnabled() {
        return enabled;
    }

    protected boolean isMode(Mode sandboxMode) {
        return isEnabled() && this.mode != null && this.mode.equals(sandboxMode);
    }

    protected Mode getMode(){
        return mode;
    }

    protected ApiInterface getMock() {
        return mock;
    }

    public enum Mode {
         DEFAULT, ADVANCED
    }
}
