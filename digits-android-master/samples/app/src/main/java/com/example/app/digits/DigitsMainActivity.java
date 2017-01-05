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

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemSelectedListener;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import com.crashlytics.android.answers.Answers;
import com.digits.sdk.android.AuthCallback;
import com.digits.sdk.android.Digits;
import com.digits.sdk.android.DigitsAuthConfig;
import com.digits.sdk.android.DigitsException;
import com.digits.sdk.android.DigitsSession;
import com.digits.sdk.android.MockApiInterface;
import com.digits.sdk.android.SandboxConfig;
import com.digits.sdk.android.SessionListener;
import com.example.app.R;
import com.twitter.sdk.android.core.Callback;
import com.twitter.sdk.android.core.Result;
import com.twitter.sdk.android.core.TwitterAuthToken;
import com.twitter.sdk.android.core.TwitterCore;
import com.twitter.sdk.android.core.TwitterException;
import com.twitter.sdk.android.core.models.User;

public class DigitsMainActivity extends Activity {
    private AuthCallback callback;
    private Button digitsAuthButton;
    private Button clearSessionButton;
    private Button verifyCredentialsButton;
    private Button customPhoneLoginButton;
    private SessionListener sessionListener;
    private Answers answers;

    public static final int CUSTOM_LOGIN_REQUEST = 1;
    public static final int CONTACT_UPLOAD_REQUEST = 2;
    public static final int FIND_FRIENDS_REQUEST = 3;

    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.digits_activity_main);
        final TextView userIdView = (TextView) findViewById(R.id.user_id);
        final TextView tokenView = (TextView) findViewById(R.id.token);
        final TextView secretView = (TextView) findViewById(R.id.secret);

        this.answers = Answers.getInstance();

        clearSessionButton = (Button) findViewById(R.id.clear_session_button);
        clearSessionButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Digits.getSessionManager().clearActiveSession();
                userIdView.setText("");
                tokenView.setText("");
                secretView.setText("");
            }
        });

        callback = new AuthCallback() {
            @Override
            public void success(DigitsSession session, String phoneNumber) {
                Toast.makeText(DigitsMainActivity.this,
                        "Authentication Successful for " + phoneNumber, Toast.LENGTH_SHORT).show();
                userIdView.setText(getString(R.string.user_id, session.getId()));
                if (session.getAuthToken() instanceof TwitterAuthToken) {
                    final TwitterAuthToken authToken = (TwitterAuthToken) session.getAuthToken();
                    tokenView.setText(getString(R.string.token, authToken.token));
                    secretView.setText(getString(R.string.secret, authToken.secret));
                }
            }

            @Override
            public void failure(DigitsException error) {
                Toast.makeText(DigitsMainActivity.this, error.getMessage(),
                        Toast.LENGTH_SHORT).show();
            }
        };


        digitsAuthButton = (Button) findViewById(R.id.signup_button);

        digitsAuthButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                DigitsAuthConfig.Builder digitsAuthConfigBuilder = new DigitsAuthConfig.Builder()
                        .withAuthCallBack(callback)
                        .withPhoneNumber("")
                        .withEmailCollection()
                        .withThemeResId(R.style.LightTheme);

                Digits.authenticate(digitsAuthConfigBuilder.build());
            }
        });

        verifyCredentialsButton = (Button) findViewById(R.id.verify_credentials_button);
        verifyCredentialsButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final DigitsSession session = Digits.getSessionManager().getActiveSession();
                if (session != null) {
                    TwitterCore.getInstance().getApiClient(session).getAccountService()
                            .verifyCredentials(null, null,
                                    new Callback<User>() {

                                        @Override
                                        public void success(Result<User> result) {
                                            Toast.makeText(DigitsMainActivity.this,
                                                    "Successfully verified credentials",
                                                    Toast.LENGTH_LONG).show();
                                        }

                                        @Override
                                        public void failure(TwitterException exception) {
                                            Toast.makeText(DigitsMainActivity.this,
                                                    "Failed to verify credentials",
                                                    Toast.LENGTH_LONG).show();
                                        }
                                    });
                }
            }
        });
        customPhoneLoginButton = (Button) findViewById(R.id.custom_login_button);
        customPhoneLoginButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(DigitsMainActivity.this, CustomPhoneNumberActivity.class);
                startActivityForResult(intent, CUSTOM_LOGIN_REQUEST);
            }
        });

        Button findFriendsButton = (Button) findViewById(R.id.upload_your_friends_button);
        findFriendsButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Digits.getInstance().getContactsClient().startContactsUpload(
                        com.digits.sdk.android.R.style.Digits_default, CONTACT_UPLOAD_REQUEST);
            }
        });

        Button contactLookupButton = (Button) findViewById(R.id.contact_lookup);
        contactLookupButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(DigitsMainActivity.this, FoundFriendsActivity.class);
                startActivityForResult(intent, FIND_FRIENDS_REQUEST);
            }
        });

        final Button toggleSandboxButton = (Button) findViewById(R.id.toggle_sandbox_button);
        toggleSandboxButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Button button = (Button) view;
                Object tag = button.getTag(R.id.is_sandbox_enabled_tag);
                boolean isEnabled = tag != null && (boolean) tag;
                if (isEnabled) {
                    Digits.getInstance().disableSandbox();
                } else {
                    Digits.getInstance().enableSandbox();
                }
                button.setText(isEnabled ? R.string.enable_sandbox : R.string.disable_sandbox);
                button.setTag(R.id.is_sandbox_enabled_tag, !isEnabled);
            }
        });

        Spinner sandboxmodeSpinner = (Spinner) findViewById(R.id.sandboxmode_spinner);

        String[] items = new String[] {"Default", "Advanced"};

        ArrayAdapter<String> adapter = new ArrayAdapter<>(this,
                android.R.layout.simple_spinner_item, items);

        sandboxmodeSpinner.setAdapter(adapter);
        sandboxmodeSpinner.setOnItemSelectedListener(new OnItemSelectedListener() {

            @Override
            public void onItemSelected(AdapterView<?> parent, View view,
                                       int position, long id) {
                Object isEnabledTag = toggleSandboxButton.getTag(R.id.is_sandbox_enabled_tag);
                boolean isDisabled = isEnabledTag == null || (boolean) isEnabledTag;
                Digits.getInstance().setSandboxConfig(
                        new SandboxConfig(!isDisabled,
                                SandboxConfig.Mode.values()[position], new MockApiInterface()));
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {
                // Do Nothing
            }
        });

        sessionListener = new SessionListener() {
            @Override
            public void changed(DigitsSession newSession) {
                Toast.makeText(DigitsMainActivity.this, "Session phone was changed: " + newSession
                        .getPhoneNumber(), Toast.LENGTH_SHORT).show();
            }
        };
        Digits.getInstance().addSessionListener(sessionListener);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == CUSTOM_LOGIN_REQUEST && resultCode == RESULT_OK) {
            Toast.makeText(DigitsMainActivity.this,
                    "Custom login succeeded",
                    Toast.LENGTH_LONG).show();
        } else if (requestCode == CONTACT_UPLOAD_REQUEST && resultCode == RESULT_CANCELED) {
            Toast.makeText(DigitsMainActivity.this,
                    "Contacts permission declined",
                    Toast.LENGTH_LONG).show();
        } else if (requestCode == CONTACT_UPLOAD_REQUEST && resultCode == RESULT_OK) {
            Toast.makeText(DigitsMainActivity.this,
                    "Contacts permission granted",
                    Toast.LENGTH_LONG).show();
        }
    }

    @Override
    protected void onStop() {
        Digits.getInstance().removeSessionListener(sessionListener);
        super.onStop();
    }
}
