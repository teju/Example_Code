package com.example.teju_chi.androidproject;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

/**
 * Created by Teju on 1/21/2016.
 */
public class NavigatorListArrayAdapter extends BaseAdapter {

    String[] navigatorArray;
    int[] images={R.drawable.homeicon,R.drawable.contacts,R.drawable.fire,R.drawable.photo};
    Context context;

    public NavigatorListArrayAdapter(Context context){
        navigatorArray=context.getResources().getStringArray(R.array.list);
        this.context=context;
    }

    @Override
    public int getCount() {
        return navigatorArray.length;
    }

    @Override
    public Object getItem(int position) {
        return navigatorArray[position];
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent)
    {
        View row=null;
        if(convertView == null){
            LayoutInflater inflater=(LayoutInflater)context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
            row=inflater.inflate(R.layout.activity_main_listview,parent,false);
        } else {
            row=convertView;
        }

        TextView textView=(TextView)row.findViewById(R.id.list);
        textView.setText(navigatorArray[position]);
        ImageView imageView=(ImageView)row.findViewById(R.id.image);
        imageView.setImageResource(images[position]);

        return row;
    }

}
