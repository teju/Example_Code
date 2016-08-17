package com.example.teju.gprstracker;

import android.app.AlertDialog;
import android.app.Service;
import android.content.DialogInterface;
import android.content.Intent;
import android.location.Address;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationManager;
import android.os.IBinder;
import android.provider.Settings;
import android.widget.Toast;

import java.io.IOException;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

/**
 * Created by Teju on 1/14/2016.
 */
public class GetGpsLocation extends Service {

    boolean isGPSEnabled = false;
    // flag for network status
    boolean isNetworkEnabled = false;

    Location location; // location
    public static double myLocationlatitude=0.00; // latitude
    public static double myLocationlongitude=0.00; // longitude

    protected LocationManager locationManager;
    public static String address="Not Found";
    public static double klatitude=0.0;
    public static double klongitude=0.0;

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        getLocation();
        return flags;
    }

    public Location getLocation() {


        Geocoder geocoder = new Geocoder(this, Locale.getDefault());
        try {

            locationManager = (LocationManager) this
                    .getSystemService(LOCATION_SERVICE);
            isGPSEnabled = locationManager
                    .isProviderEnabled(LocationManager.GPS_PROVIDER);

            // getting network status
            isNetworkEnabled = locationManager
                    .isProviderEnabled(LocationManager.NETWORK_PROVIDER);

            System.out.println("NETWORENABLED "+isGPSEnabled+ " "+isNetworkEnabled);
            if (!isGPSEnabled && !isNetworkEnabled) {
                gpsAlertShow();
            } else  if(isNetworkEnabled){

                if (locationManager != null) {
                    location = locationManager
                            .getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
                    if (location != null) {
                        myLocationlatitude = location.getLatitude();
                        myLocationlongitude = location.getLongitude();
                        address=geocoder.getFromLocation(myLocationlatitude,myLocationlongitude,1).toString();
                        System.out.println("LAITUDEMYLOC is "+myLocationlatitude +" LOGITUDE "+myLocationlongitude+"ADDRESS" +
                                " "+geocoder.getFromLocation(myLocationlatitude,myLocationlatitude,1).toString());
                    }

                }
            } else if(isGPSEnabled){
                if (locationManager != null) {
                    location = locationManager
                            .getLastKnownLocation(LocationManager.GPS_PROVIDER);
                    if (location != null) {
                        myLocationlatitude = location.getLatitude();
                        myLocationlongitude = location.getLongitude();
                        address=geocoder.getFromLocation(myLocationlatitude,myLocationlongitude,1).toString();
                        System.out.println("LAITUDE is "+myLocationlatitude +" LOGITUDE "+myLocationlongitude+"ADDRESS" +
                                " "+geocoder.getFromLocation(myLocationlatitude,myLocationlongitude,1).toString());
                    }
                }
            }
        } catch (Exception e) {
            System.out.println("ERROR is "+e.toString());
        }

        try {
            ArrayList<Address> adresses = (ArrayList<Address>)geocoder.getFromLocationName("Floor," +
                    " 3rd Block,, 1054, Ganapathi Temple Rd, HAL 3rd Stage, Koramangala 6 Block," +
                    " Koramangala, Bengaluru, Karnataka 560034",50);
            for(Address add : adresses){
                     klongitude = add.getLongitude();
                     klatitude = add.getLatitude();
                System.out.println("LAITUDE is "+klatitude +" LOGITUDE "+klongitude+"ADDRESS" +
                        " "+geocoder.getFromLocation(klatitude,klongitude,1).toString());
            }

        } catch (IOException e) {
            e.printStackTrace();
        }
        return location;
    }


    public void gpsAlertShow(){
        AlertDialog.Builder alertDialogBuilder = new AlertDialog.Builder(
                this);
        alertDialogBuilder
                .setMessage("GPS is disabled in your device. Enable it?")
                .setCancelable(false)
                .setPositiveButton("Enable GPS",
                        new DialogInterface.OnClickListener() {
                            public void onClick(DialogInterface dialog,
                                                int id) {
                                Intent callGPSSettingIntent = new Intent(
                                        android.provider.Settings.ACTION_LOCATION_SOURCE_SETTINGS);
                                startActivity(callGPSSettingIntent);
                            }
                        });
        alertDialogBuilder.setNegativeButton("Cancel",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int id) {
                        dialog.cancel();
                    }
                });
        AlertDialog alert = alertDialogBuilder.create();
        alert.show();
    }
}

