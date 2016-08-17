package com.example.teju_chi.androidproject;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.ImageView;
import android.widget.TextView;

/**
 * Created by Teju on 1/31/2016.
 */
public class BaseClass extends AppCompatActivity {

    private Toolbar toolbar;


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        Intent i;
        // Handle item selection
        switch (item.getItemId()) {
            case R.id.action_settings:
                i=new Intent(this,ActivitySetting.class);
                startActivity(i);
                break;
            case R.id.help:
                i=new Intent(this,HelpMenue.class);
                startActivity(i);
                break;
            case R.id.ContactUs:
                i=new Intent(this,ContactUs.class);
                startActivity(i);
                break;
            case R.id.AboutUs:
                i=new Intent(this,AboutUs.class);
                startActivity(i);
                break;
        }
        return super.onOptionsItemSelected(item);
    }

}
