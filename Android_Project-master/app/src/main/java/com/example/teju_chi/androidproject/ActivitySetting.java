package com.example.teju_chi.androidproject;

import android.app.Activity;
import android.app.FragmentManager;
import android.app.FragmentTransaction;
import android.os.Bundle;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.TextView;

/**
 * Created by Teju on 1/21/2016.
 */
public class ActivitySetting extends BaseClass {
    private Toolbar toolbar;
    SettingListAdapter settingadapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_setting);
        toolbar = (Toolbar) findViewById(R.id.app_login_bar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setDisplayShowTitleEnabled(false);
        getSupportActionBar().setDisplayShowHomeEnabled(true);


        ListView listview=(ListView)findViewById(R.id.setting_list);
        settingadapter=new SettingListAdapter(this);
        listview.setAdapter(settingadapter);

        TextView toolbarText=(TextView)findViewById(R.id.toolbar_text);
        toolbarText.setText("Settings");
        ImageView imageView=(ImageView)findViewById(R.id.toolbar_image);
        imageView.setImageResource(R.drawable.settin);

        NavigationBarFragment navdrawer=(NavigationBarFragment)getSupportFragmentManager().
                findFragmentById(R.id.navdrawer);
        navdrawer.setUp((DrawerLayout)findViewById( R.id.mainactivitydrawer),toolbar);

    }
}
