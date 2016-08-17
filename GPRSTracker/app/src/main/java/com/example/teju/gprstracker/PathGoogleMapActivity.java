package com.example.teju.gprstracker;

import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.FragmentActivity;
import android.util.Log;

import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class PathGoogleMapActivity extends FragmentActivity  {
    Polyline line;



    private static final LatLng BROOKLYN_BRIDGE = new LatLng(GetGpsLocation.myLocationlatitude,
            GetGpsLocation.myLocationlongitude);
    private static final LatLng WALL_STREET = new LatLng(12.9355712, 77.6223906);

    GoogleMap googleMap;
    final String TAG = "PathGoogleMapActivity";
    private PolylineOptions lineOptions;
    private Marker marker1;
    private Marker marker2;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_path_google_map);

        System.out.println("MAPSLATITUDE "+GetGpsLocation.myLocationlatitude+" "+GetGpsLocation.myLocationlongitude);
        SupportMapFragment fm = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        googleMap = fm.getMap();

        MarkerOptions options = new MarkerOptions();
        options.position(BROOKLYN_BRIDGE);
        options.position(WALL_STREET);
        googleMap.addMarker(options);
        String url = getMapsApiDirectionsUrl();

        googleMap.moveCamera(CameraUpdateFactory.newLatLngZoom(BROOKLYN_BRIDGE,
                13));
        addMarkers();

    }

    private String getMapsApiDirectionsUrl() {
        String waypoints = "waypoints=optimize:true|"
                + "|" + "|" + BROOKLYN_BRIDGE.latitude + ","
                + BROOKLYN_BRIDGE.longitude + "|" + WALL_STREET.latitude + ","
                + WALL_STREET.longitude;
        String OriDest = "origin="+BROOKLYN_BRIDGE.latitude+","+BROOKLYN_BRIDGE.longitude+"&destination="+BROOKLYN_BRIDGE.latitude+","+BROOKLYN_BRIDGE.longitude;

        String sensor = "sensor=false";
        String params = OriDest+"&%20"+waypoints + "&" + sensor;
        String output = "json";
        String url = "https://maps.googleapis.com/maps/api/directions/"
                + output + "?" + params;
        System.out.println("URLIS "+url);
        return url;
    }

    private void addMarkers() {
        if (googleMap != null) {
            marker1=googleMap.addMarker(new MarkerOptions().position(BROOKLYN_BRIDGE)
                    .title("First Point"));
            marker2=googleMap.addMarker(new MarkerOptions().position(WALL_STREET)
                    .title("Third Point"));
            PolylineOptions polylineOptions=new PolylineOptions()
                    .add(marker1.getPosition())
                    .add(marker2.getPosition())
                    .color(Color.BLUE)
                    .width(4);
            googleMap.addPolyline(polylineOptions);


        }
    }

}
