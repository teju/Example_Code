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

package com.example.app.digits;

import android.app.ListActivity;
import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;
import android.widget.Toast;

import com.digits.sdk.android.Contacts;
import com.digits.sdk.android.Digits;
import com.digits.sdk.android.DigitsAuthConfig;
import com.digits.sdk.android.DigitsUser;
import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.TwitterException;

import com.example.app.R;

public class FoundFriendsActivity extends ListActivity {

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.digits_activity_found_friends);

        final FriendAdapter adapter = new FriendAdapter(this);
        getListView().setAdapter(adapter);

        final int MAX_BATCH_SIZE = 50;
        Digits.getInstance().getContactsClient().lookupContactMatchesStart(new Callback<Contacts>() {

            @Override
            public void success(Result<Contacts> result) {
                if (result.data.users != null) {
                    adapter.setNotifyOnChange(false);
                    for (DigitsUser user : result.data.users) {
                        adapter.add(user);
                    }
                    if(result.data.users.size() > 0){
                        adapter.notifyDataSetChanged();
                    }
                }
                // Make subsequent calls until all friend matches are retrieved
                if (result.data.nextCursor != null) {
                   Digits.getInstance().getContactsClient()
                           .lookupContactMatches(result.data.nextCursor, MAX_BATCH_SIZE, this);
                }
            }

            @Override
            public void failure(TwitterException exception) {
                Toast.makeText(FoundFriendsActivity.this, exception.getMessage(),
                        Toast.LENGTH_SHORT).show();
            }
        });
    }

    static class FriendAdapter extends ArrayAdapter<DigitsUser> {
        private final LayoutInflater inflater;

        public FriendAdapter(Context context) {
            super(context, 0);

            inflater = (LayoutInflater) context.getSystemService(Context
                    .LAYOUT_INFLATER_SERVICE);
        }

        public View getView(int position, View convertView, ViewGroup parent) {
            ViewHolder holder;
            if (convertView == null) {
                convertView = inflater.inflate(android.R.layout.simple_list_item_1, null);
                holder = new ViewHolder();
                holder.text1 = (TextView) convertView.findViewById(android.R.id.text1);
                convertView.setTag(holder);
            } else {
                holder = (ViewHolder) convertView.getTag();
            }

            final DigitsUser user = getItem(position);
            holder.text1.setText(getContext().getString(R.string.id_format, user.id));

            return convertView;
        }

        static class ViewHolder {
            TextView text1;
        }
    }
}
