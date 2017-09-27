package com.example.nz160.chatappapplozic;

import android.Manifest;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.AutoCompleteTextView;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.TextView;

import com.example.nz160.chatappapplozic.Utils.MobiComUserPreference;

/**
 * Created by nz160 on 26-09-2017.
 */
public class LoginActivity extends AppCompatActivity {
    private static final int REQUEST_CONTACTS = 1;
    private static String[] PERMISSIONS_CONTACT = {Manifest.permission.READ_CONTACTS,
            Manifest.permission.WRITE_CONTACTS};
    LinearLayout layout;
    /**
     * Keep track of the login task to ensure we can cancel it if requested.
     */
    private UserLoginTask mAuthTask = null;
    //flag variable for exiting the application
    private boolean exit = false;
    // UI references.
    private AutoCompleteTextView mEmailView;
    private EditText mUserIdView;
    private EditText mPhoneNumberView;
    private EditText mPasswordView;
    private EditText mDisplayName;
    private View mProgressView;
    private View mLoginFormView;
    private Button mEmailSignInButton;
    //CallbackManager callbackManager;
    private TextView mTitleView;
    private Spinner mSpinnerView;
    private int touchCount = 0;
    private MobiComUserPreference mobiComUserPreference;
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
    }
}
