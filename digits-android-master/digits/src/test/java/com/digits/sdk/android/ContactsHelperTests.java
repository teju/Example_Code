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

import android.content.ContentResolver;
import android.content.Context;
import android.database.Cursor;
import android.database.MatrixCursor;
import android.net.Uri;
import android.test.mock.MockContext;

import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.RobolectricGradleTestRunner;
import org.robolectric.annotation.Config;

import java.util.ArrayList;
import java.util.List;

import static org.junit.Assert.assertArrayEquals;
import static org.junit.Assert.assertEquals;
import static org.mockito.Matchers.any;
import static org.mockito.Matchers.isNull;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@RunWith(RobolectricGradleTestRunner.class)
@Config(constants = BuildConfig.class, sdk = 21)
public class ContactsHelperTests {
    // Sample rows for matrix cursor
    private static final String[] COLUMNS = {"data1", "data2", "data3", "lookup", "mimetype",
            "is_primary"};
    private static final String[] PHONE_ROW = {"(555)555-5555", "2", "", "1",
            "vnd.android.cursor.item/phone_v2", "0"};
    private static final String[] NAME_ROW = {"nene goose", "nene", "goose", "1",
            "vnd.android.cursor.item/name", ""};

    // Expected results from sample cursor
    private static final String SAMPLE_CARD = "BEGIN:VCARD\r\nVERSION:3.0\r\nN:goose;nene;;;" +
            "\r\nFN:nene goose\r\nTEL;TYPE=CELL:555-555-5555\r\nEND:VCARD\r\n";

    private Context context;
    private ContentResolver contentResolver;
    private Cursor cursor;

    @Before
    public void setUp() throws Exception {
        context = mock(MockContext.class);
        contentResolver = mock(ContentResolver.class);
        cursor = createCursor();

        when(contentResolver.query(any(Uri.class), any(String[].class), any(String.class), any
                (String[].class), any(String.class))).thenReturn(cursor);
        when(context.getContentResolver()).thenReturn(contentResolver);
    }

    static Cursor createCursor() {
        final MatrixCursor matrixCursor = new MatrixCursor(COLUMNS);
        matrixCursor.addRow(PHONE_ROW);
        matrixCursor.addRow(NAME_ROW);
        return matrixCursor;
    }

    static ArrayList<String> createCardList() {
        final ArrayList<String> vCards = new ArrayList<>();
        vCards.add(SAMPLE_CARD);
        return vCards;
    }

    @Test
    public void testGetContactsCursor() {
        final ContactsHelper contactsHelper = new ContactsHelper(context);
        final Cursor cursor = contactsHelper.getContactsCursor();

        verify(context).getContentResolver();
        verify(contentResolver).query(any(Uri.class), any(String[].class), any(String.class),
                any(String[].class), isNull(String.class));
        assertArrayEquals(COLUMNS, cursor.getColumnNames());
    }

    @Test
    public void testCreateContactList() {
        final ContactsHelper contactsHelper = new ContactsHelper(context);

        final List<String> cards = contactsHelper.createContactList(cursor);

        assertEquals(1, cards.size());
        assertArrayEquals(createCardList().toArray(), cards.toArray());
    }
}
