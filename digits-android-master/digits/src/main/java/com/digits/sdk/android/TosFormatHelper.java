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

import android.content.Context;
import android.support.annotation.StringRes;
import android.text.Html;
import android.text.Spanned;

public class TosFormatHelper {
    private final Context context;

    TosFormatHelper(Context context) {
        this.context = context;
    }

    protected Spanned getFormattedTerms(@StringRes int termsResId) {
        final String appName = getApplicationName(context);
        return Html.fromHtml(context.getString(
                termsResId, "\"", "<u>", "</u>", appName, "</a>",
                createAnchorTag(R.string.dgts__digits_com_url),
                createAnchorTag(R.string.dgts__digits_com_settings_url),
                createAnchorTag(R.string.dgts__twitter_tos_url),
                createAnchorTag(R.string.dgts__twitter_privacy_url),
                createAnchorTag(R.string.dgts__twitter_cookies_policy_url)));
    }

    private String getApplicationName(Context context) {
        return context.getApplicationInfo().loadLabel(context.getPackageManager()).toString();
    }

    private String createAnchorTag(@StringRes int termsResId) {
        return String.format("<a href=%1$s>", context.getResources().getString(termsResId));
    }
}
