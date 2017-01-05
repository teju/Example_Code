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
import android.os.Bundle;

public interface DigitsActivityDelegate extends ActivityLifecycle {
    /**
     * Returns the layout resource id of the subclass. This resource will be used to set the view
     * of the Activity
     */
    int getLayoutId();

    /**
     * Returns true if the bundle param is valid and the activity can be created, otherwise false
     */
    boolean isValid(Bundle bundle);

    /**
     * Initializes the views in the Activity.
     */
    void init(Activity activity, Bundle bundle);
}
