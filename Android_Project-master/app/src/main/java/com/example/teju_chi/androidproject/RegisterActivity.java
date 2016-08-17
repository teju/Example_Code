package com.example.teju_chi.androidproject;

import android.content.Intent;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v7.app.AppCompatActivity;
import android.view.View;
import android.widget.EditText;

/**
 * Created by Teju-Chi on 12/27/2015.
 */
public class RegisterActivity extends AppCompatActivity {
    private Config config;
    private EditText remai;
    private EditText rname;
    private EditText rpswd;
    private String name="null";
    private String email="null";
    private String pswd="null";
    private DatabaseHelper db;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);
        db=new DatabaseHelper(this);
        config=new Config(this);
        remai=(EditText)findViewById(R.id.register_email_id);
        rname=(EditText)findViewById(R.id.register_name);
        rpswd=(EditText)findViewById(R.id.register_password);
    }
    public void register(View view){
        name=rname.getText().toString();
        email=remai.getText().toString();
        pswd=rpswd.getText().toString();
        System.out.println("Email id is "+email+" name "+name+" pswd "+pswd);
        if(name.equals("")){
            config.ToastMsg("Name cannot be empty");
        } else if(email.equals("")) {
            config.ToastMsg("Email cannot be empty");
        } else if(pswd.equals("")){
            config.ToastMsg("Password cannot be empty");
        } else {
            db.putUserInformation(db,name,email,pswd);
            Intent i=new Intent(this,MainActivity.class);
            startActivity(i);
        }
    }
    public void onBackPressed() {
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);

    }
}
