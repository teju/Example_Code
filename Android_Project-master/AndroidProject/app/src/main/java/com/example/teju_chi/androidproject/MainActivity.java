package com.example.teju_chi.androidproject;

import android.content.ContentValues;
import android.content.Intent;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.widget.ListView;
import android.widget.TextView;

/**
 * Created by Teju-Chi on 12/23/2015.
 */
public class MainActivity extends ActionBarActivity {

    private ListView listView;
    private Uri imageUri;
    final static int CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE = 1;
    private String intent_name;

    @Override
    protected void onCreate( Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        Intent i=getIntent();
        intent_name=i.getStringExtra("name");
        System.out.println("MAINACTIVITY NAME "+intent_name);
       /* TextView name=(TextView)findViewById(R.id.name_textview);
        name.setText("Welcome");
        name.setTextColor(Color.BLACK);*/

        Toolbar toolbar=(Toolbar)findViewById(R.id.app_login_bar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setDisplayShowTitleEnabled(false);
        getSupportActionBar().setDisplayShowHomeEnabled(true);

        MainActivityFragment mainActivityFragment=(MainActivityFragment)getFragmentManager().
                findFragmentById(R.id.fragmentmain);
        mainActivityFragment.setup((DrawerLayout)findViewById(R.id.mainactivitydrawer),toolbar);
    }

    public void captureImage(View view){

        String fileName = "Camera_Example.jpg";

        // Create parameters for Intent with filename

        ContentValues values = new ContentValues();

        values.put(MediaStore.Images.Media.TITLE, fileName);

        values.put(MediaStore.Images.Media.DESCRIPTION,"Image capture by camera");

        // imageUri is the current activity attribute, define and save it for later usage

       // imageUri = getContentResolver().insert(
         //       MediaStore.Images.Media.EXTERNAL_CONTENT_URI, values);

        /**** EXTERNAL_CONTENT_URI : style URI for the "primary" external storage volume. ****/

        // Standard Intent action that can be sent to have the camera
        // application capture an image and return it.

        Intent intent = new Intent( MediaStore.ACTION_IMAGE_CAPTURE );

        intent.putExtra(MediaStore.EXTRA_OUTPUT, imageUri);

        intent.putExtra(MediaStore.EXTRA_VIDEO_QUALITY, 1);

        startActivityForResult( intent, CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE);
    }
    public void onBackPressed() {
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);

    }

}
