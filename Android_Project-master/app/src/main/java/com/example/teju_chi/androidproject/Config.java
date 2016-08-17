package com.example.teju_chi.androidproject;

import android.content.Context;
import android.widget.Toast;

/**
 * Created by Teju-Chi on 12/23/2015.
 */
public class Config {

    Context context;
    public Config(Context context){
        this.context=context;
    }

    public  void ToastMsg(String msg){
        Toast.makeText(context,msg,Toast.LENGTH_LONG).show();
    }
}
