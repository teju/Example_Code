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

import android.view.View;

import io.fabric.sdk.android.FabricTestUtils;

import com.twitter.sdk.android.core.TwitterAuthConfig;
import com.twitter.sdk.android.core.TwitterCore;

import java.util.Locale;

import static org.mockito.Matchers.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;

public class CountryListSpinnerTests extends DigitsAndroidTestCase {
    static final CountryInfo US_COUNTRY_INFO = new CountryInfo(Locale.US, 1);

    @Override
    public void setUp() throws Exception {
        super.setUp();
        FabricTestUtils.resetFabric();
        FabricTestUtils.with(getContext(), new TwitterCore(new TwitterAuthConfig("", "")),
                new Digits());
    }

    @Override
    public void tearDown() throws Exception {
        super.tearDown();
        FabricTestUtils.resetFabric();
    }

    public void testConstructor_oneParam() {
        final CountryListSpinner spinner = new CountryListSpinner(getContext());

        verifyDefaultSpinnerText(spinner);
    }

    public void testConstructor_twoParam() {
        final CountryListSpinner spinner = new CountryListSpinner(getContext(), null);

        verifyDefaultSpinnerText(spinner);
    }

    public void testConstructor_threeParam() {
        final CountryListSpinner spinner = new CountryListSpinner(getContext(), null,
                android.R.attr.spinnerStyle);

        verifyDefaultSpinnerText(spinner);
    }

    void verifyDefaultSpinnerText(CountryListSpinner spinner) {
        final String spinnerFormat = getContext().getResources().getString(R.string
                .dgts__country_spinner_format);
        final String usTest = Locale.US.getDisplayCountry();
        final String countryInfoText = String.format(spinnerFormat, usTest,
                US_COUNTRY_INFO.countryCode);

        assertEquals(countryInfoText, spinner.getText());
        assertEquals(US_COUNTRY_INFO, spinner.getTag());
    }

    public void testSetOnClickListener() {
        final CountryListSpinner.DialogPopup dialog = mock(CountryListSpinner.DialogPopup.class);
        final CountryListSpinner spinner = new CountryListSpinner(getContext());
        spinner.setDialogPopup(dialog);
        final View.OnClickListener listener = mock(View.OnClickListener.class);
        spinner.setOnClickListener(listener);
        spinner.performClick();
        verify(listener).onClick(any(View.class));
    }
}
