package com.example.teju_chi.androidproject;

import android.content.Intent;
import android.os.CountDownTimer;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import java.util.List;


public class LoginActivity extends AppCompatActivity {

    DatabaseHelper db;
    EditText email_id;
    private String mpassword="null";
    private EditText editPassword;
    Config config;
    private String memailId="null";
    private String mname;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash_page);
        CountDownTimer c = new CountDownTimer(2000, 1000) {
            public void onFinish() {

                setContentView(R.layout.activity_login);
                Toolbar toolbar=(Toolbar)findViewById(R.id.app_login_bar);
                setSupportActionBar(toolbar);
                getSupportActionBar().setDisplayShowTitleEnabled(false);
                getSupportActionBar().setDisplayShowHomeEnabled(true);
                email_id=(EditText)findViewById(R.id.email_id);
                editPassword=(EditText)findViewById(R.id.password);
            }
            public void onTick(long millisUntilFinished) {
            }

        }.start();
        config=new Config(this);
        db=new DatabaseHelper(this);
    }

    public void login(View view){
        String edit_email=email_id.getText().toString();
        String edit_pswd=editPassword.getText().toString();
        System.out.println("EMAIL  is "+edit_email+" password "+edit_pswd);
        if( edit_email.equals("")){
            config.ToastMsg("Please enter your email ID");
        }
        else {
            List<DataBase> userpassword = db.getdbPassword(edit_email);
            for(DataBase password :userpassword){
                mpassword=password.getpassword();
                mname=password.getName();
                memailId=password.getEmailId();
                System.out.println("PAssword is "+mpassword);
            }
            if(memailId.equals("null")) {
                Intent i=new Intent(this,RegisterActivity.class);
                startActivity(i);            }
            else if( edit_pswd.equals("")) {
                config.ToastMsg("Please enter your password");
            }
            else if(!mpassword.equals(edit_pswd) && mpassword.equals("null") ) {
                config.ToastMsg("Email id password did not match");
            } else {
                Intent i=new Intent(this,MainActivity.class);
                i.putExtra("name ",mname);
                System.out.println("INTENTMAINACTIVITY NAME "+mname);
                startActivity(i);

            }
        }
    }
    public void onBackPressed() {
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);

    }
}
