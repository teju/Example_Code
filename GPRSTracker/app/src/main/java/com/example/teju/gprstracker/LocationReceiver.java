package com.example.teju.gprstracker;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.widget.Toast;

import java.net.URLEncoder;

/**
 * Created by Teju on 1/14/2016.
 */
public class LocationReceiver extends BroadcastReceiver {

    Context context;
    @Override
    public void onReceive(Context context, Intent intent) {
        this.context=context;
        //Start a new thread to perform network operations
        Thread t=new Thread(new Runnable() {
            @Override
            public void run() {
                postData();
            }
        });
        t.start();
    }

    //Posting the data to google spreadsheets
    public void postData(){

        //Get the url ,location of data to be posted by doing viesource code of our google forms
        // form tag.
        String url="https://docs.google.com/forms/d/1k5iqkDz8MPge26a15F_mh2TrKkviSPMr-w4G0rYUqDk/formResponse";
        HttpRequest httpRequest=new HttpRequest();
        Double latitude=GetGpsLocation.myLocationlatitude;

        //send data to send post of HttpReuest class
        String  data="entry_21748661="+latitude+"&"+"entry_1744024353="+GetGpsLocation.myLocationlongitude+
                "&"+"entry_876696867="+URLEncoder.encode(GetGpsLocation.address)+"&"+
                "entry_1254765768="+GetGpsLocation.klatitude+"&"+"entry_1470860234="+GetGpsLocation.klongitude;
        String resp=httpRequest.sendPost(url,data);
        System.out.println("response is "+resp);

    }
}
