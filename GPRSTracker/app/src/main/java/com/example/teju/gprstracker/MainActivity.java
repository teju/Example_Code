package com.example.teju.gprstracker;

import android.app.Activity;
import android.app.AlarmManager;
import android.app.AlertDialog;
import android.app.IntentService;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Color;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.provider.Settings;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import org.w3c.dom.Document;

import java.io.IOException;
import java.net.URLEncoder;
import java.util.Locale;


public class MainActivity extends Activity implements View.OnClickListener {

    private AlarmManager manager;
    public PendingIntent pendingIntent;
    Boolean isInternetPresent = false;
    ConnectionDetector cd;
    private ProgressDialog mDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_startup);

        CountDownTimer c = new CountDownTimer(3000, 1000) {
            public void onFinish() {
                setContentView(R.layout.activity_main);


                cd = new ConnectionDetector(getApplicationContext());
                isInternetPresent = cd.isConnectingToInternet();
                if (!isInternetPresent) {

                    showAlert();
                } else {


                    //Starting the service for getting location details
                    Intent i = new Intent(MainActivity.this, GetGpsLocation.class);
                    startService(i);
                    TextView latitude=(TextView)findViewById(R.id.latitude);
                    latitude.setText("My location Latitude is "+GetGpsLocation.myLocationlatitude);

                    TextView longitude=(TextView)findViewById(R.id.loggitude);
                    longitude.setText("My location Longitude is "+GetGpsLocation.myLocationlongitude);
                    TextView address=(TextView)findViewById(R.id.address);
                    address.setText("Address is "+GetGpsLocation.address);


                    //Starting the broadcast receiver to plot the location details to google spreadsheets
                    Intent alarmIntent = new Intent(MainActivity.this,
                            LocationReceiver.class);
                    pendingIntent = PendingIntent.getBroadcast
                            (MainActivity.this, 0, alarmIntent, 0);
                    sendBroadcast(alarmIntent);
                    setAlarm();
                }
                //Plotting directions
                findViewById(R.id.btn_simple).setOnClickListener( MainActivity.this);

            }
            public void onTick(long millisUntilFinished) {
            }

        }.start();


    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        if (id == R.id.btn_simple) {
            if (!isInternetPresent) {

                showAlert();
            } else  if(GetGpsLocation.myLocationlatitude==0.00){
                if(GetGpsLocation.myLocationlongitude==0.00){
                    showMsgAlert();
                }
            } else
                {
                    Intent i = new Intent(this, MyMap.class);
                    startActivity(i);

                }

        }
    }
    public void setAlarm() {
        manager = (AlarmManager) getSystemService(Context.ALARM_SERVICE);
        int interval =30* 60000;
        Log.d("alaraaaaaam", pendingIntent.toString());
        manager.setRepeating(AlarmManager.RTC_WAKEUP, System.currentTimeMillis(), interval, pendingIntent);
    }

    public void showAlert(){
        AlertDialog.Builder alertDialog = new AlertDialog.Builder(
                this);

        // Setting Dialog Title
        alertDialog.setTitle("Confirm...");

        // Setting Dialog Message
        alertDialog.setMessage("Do you want to go to wifi settings?");

        // Setting Icon to Dialog
        // alertDialog.setIcon(R.drawable.ic_launcher);

        // Setting Positive "Yes" Button
        alertDialog.setPositiveButton("yes",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {

                        // Activity transfer to wifi settings
                        startActivity(new Intent(Settings.ACTION_WIFI_SETTINGS));
                    }
                });

        // Setting Negative "NO" Button
        alertDialog.setNegativeButton("no",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        // Write your code here to invoke NO event

                        dialog.cancel();
                    }
                });

        // Showing Alert Message
        alertDialog.show();
    }

    public void showMsgAlert(){
        AlertDialog alertDialog = new AlertDialog.Builder(
                MainActivity.this).create();

        // Setting Dialog Title
        alertDialog.setTitle("Alert Dialog");

        // Setting Dialog Message
        alertDialog.setMessage("Current location has not been found");

        // Setting Icon to Dialog

        // Setting OK Button
        alertDialog.setButton("OK", new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
                // Write your code here to execute after dialog closed
            }
        });

        // Showing Alert Message
        alertDialog.show();
    }
}
