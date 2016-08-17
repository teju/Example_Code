package com.example.teju_chi.androidproject;

import android.os.Bundle;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.widget.ImageView;
import android.widget.TextView;

/**
 * Created by Teju on 1/31/2016.
 */
public class HelpMenue extends BaseClass
{

    private Toolbar toolbar;

    @Override
    protected void onCreate( Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_help);
        toolbar = (Toolbar) findViewById(R.id.app_login_bar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setDisplayShowTitleEnabled(false);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
        TextView toolbarText=(TextView)findViewById(R.id.toolbar_text);
        toolbarText.setText("Help");
        ImageView imageView=(ImageView)findViewById(R.id.toolbar_image);
        imageView.setImageResource(R.drawable.help);

        NavigationBarFragment navdrawer=(NavigationBarFragment)getSupportFragmentManager().
                findFragmentById(R.id.navdrawer);
        navdrawer.setUp((DrawerLayout)findViewById( R.id.mainactivitydrawer),toolbar);
    }
}
