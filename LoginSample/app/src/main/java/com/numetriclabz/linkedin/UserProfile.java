package com.numetriclabz.linkedin;

import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import com.linkedin.platform.APIHelper;
import com.linkedin.platform.LISessionManager;
import com.linkedin.platform.errors.LIApiError;
import com.linkedin.platform.listeners.ApiListener;
import com.linkedin.platform.listeners.ApiResponse;
import com.squareup.picasso.Picasso;

import org.json.JSONObject;

public class UserProfile extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener {

    private static final String host = "api.linkedin.com";
    private static final String topCardUrl = "https://" + host + "/v1/people/~:(email-address,formatted-name,phone-numbers,public-profile-url,picture-url,picture-urls::(original))";
    ProgressDialog progress;
    ImageView profile_pic;
    TextView user_name,user_email;
    NavigationView navigation_view;
    Button logout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_userprofile);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        logout = (Button) findViewById(R.id.logout);
        setSupportActionBar(toolbar);
        progress= new ProgressDialog(this);
        progress.setMessage("Retrieve data...");
        progress.setCanceledOnTouchOutside(false);
        progress.show();
        getUserData();

        FloatingActionButton fab = (FloatingActionButton) findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Snackbar.make(view, "Replace with your own action", Snackbar.LENGTH_LONG)
                        .setAction("Action", null).show();
            }
        });

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                this, drawer, toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        drawer.setDrawerListener(toggle);
        toggle.syncState();

        setNavigationHeader();
        navigation_view.setNavigationItemSelectedListener(this);

        // this is used for clear session i.e logout from linkedin

        logout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                LISessionManager.getInstance(getApplicationContext()).clearSession();
                Intent intent = new Intent(UserProfile.this, MainActivity.class);
                startActivity(intent);
            }
        });
    }

    /*Once User's can authenticated,
      It make an HTTP GET request to LinkedIn's REST API using the currently authenticated user's credentials.
      If successful, A LinkedIn ApiResponse object containing all of the relevant aspects of the server's response will be returned.
     */

    public void getUserData(){
        APIHelper apiHelper = APIHelper.getInstance(getApplicationContext());
        apiHelper.getRequest(UserProfile.this, topCardUrl, new ApiListener() {
            @Override
            public void onApiSuccess(ApiResponse result) {
              try {

                  setUserProfile(result.getResponseDataAsJson());
                  progress.dismiss();

              } catch (Exception e){
                 e.printStackTrace();
              }

            }

            @Override
            public void onApiError(LIApiError error) {
              //  ((TextView) findViewById(R.id.error)).setText(error.toString());

            }
        });
    }
    /*
       Set Navigation header by using Layout Inflater.
     */

    public void setNavigationHeader(){

        navigation_view = (NavigationView) findViewById(R.id.nav_view);

        View header = LayoutInflater.from(this).inflate(R.layout.nav_header_user_profile, null);
        navigation_view.addHeaderView(header);

        user_name = (TextView) header.findViewById(R.id.username);
        profile_pic = (ImageView) header.findViewById(R.id.profile_pic);
        user_email = (TextView) header.findViewById(R.id.email);
    }

    public  void  setUserProfile(JSONObject response){

        try {

            user_email.setText(response.get("emailAddress").toString());
            user_name.setText(response.get("formattedName").toString());

            Picasso.with(this).load(response.getString("pictureUrl"))
                    .into(profile_pic);

        } catch (Exception e){
            e.printStackTrace();
        }
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.user_profile, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        // Handle navigation view item clicks here.
        int id = item.getItemId();

        if (id == R.id.nav_camera) {
            // Handle the camera action
        } else if (id == R.id.nav_gallery) {

        } else if (id == R.id.nav_slideshow) {

        } else if (id == R.id.nav_manage) {

        } else if (id == R.id.nav_share) {

        } else if (id == R.id.nav_send) {

        }

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }
}
