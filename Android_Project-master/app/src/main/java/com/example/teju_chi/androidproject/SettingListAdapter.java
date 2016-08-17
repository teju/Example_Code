package com.example.teju_chi.androidproject;

import android.content.Context;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

/**
 * Created by Teju on 1/21/2016.
 */
public class SettingListAdapter extends BaseAdapter {

    String[] settingArray;
    int[] images={R.drawable.notification,R.drawable.profile_icon,R.drawable.unnamed,
            R.drawable.ic_launcher,R.drawable.ic_launcher};
    Context context;

    public SettingListAdapter(Context context){
        settingArray=context.getResources().getStringArray(R.array.settingslist);
        this.context=context;
    }

    @Override
    public int getCount() {
        return settingArray.length;
    }

    @Override
    public Object getItem(int position) {
        return settingArray[position];
    }

    @Override
    public long getItemId(int position) {
        if(position%2==0){

        }
        return position;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent)
    {
        View row=null;
        if(convertView == null){
            LayoutInflater inflater=(LayoutInflater)context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            row=inflater.inflate(R.layout.activity_setting_list,parent,false);
        } else {
            row=convertView;
        }
        if (position % 2 == 1) {
            row.setBackgroundColor(Color.parseColor("#e0e0e0"));
        } else {
            row.setBackgroundColor(Color.WHITE);
        }

        TextView textView=(TextView)row.findViewById(R.id.setting_list_text);
        textView.setText(settingArray[position]);
        ImageView imageView=(ImageView)row.findViewById(R.id.setting_list_image);
        imageView.setImageResource(images[position]);
        return row;
    }

}
