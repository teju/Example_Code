package com.example.teju_chi.androidproject;

import android.graphics.Color;
import android.os.Bundle;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Gravity;
import android.widget.AbsListView;
import android.widget.ImageView;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;

/**
 * Created by Teju on 1/31/2016.
 */
public class ContactUs extends BaseClass {
    private Toolbar toolbar;
    private String[] names;
    private String[] email;
    private String[] phone;

    @Override
    protected void onCreate( Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_contact_us);
        toolbar = (Toolbar) findViewById(R.id.app_login_bar);
        setSupportActionBar(toolbar);
        getSupportActionBar().setDisplayShowTitleEnabled(false);
        getSupportActionBar().setDisplayShowHomeEnabled(true);
        TextView toolbarText=(TextView)findViewById(R.id.toolbar_text);
        toolbarText.setText("Contact Us");

        names=this.getResources().getStringArray(R.array.contactlistNames);
        email=this.getResources().getStringArray(R.array.contactlistEmail);
        phone=this.getResources().getStringArray(R.array.contactlistPhone);

        ImageView imageView=(ImageView)findViewById(R.id.toolbar_image);
        imageView.setImageResource(R.drawable.contact_us);

        NavigationBarFragment navdrawer=(NavigationBarFragment)getSupportFragmentManager().
                findFragmentById(R.id.navdrawer);
        navdrawer.setUp((DrawerLayout)findViewById( R.id.mainactivitydrawer),toolbar);
        for(int j=0;j<names.length;j++) {

            TableLayout stk = (TableLayout) findViewById(R.id.contact_table);


            //Show last 5 transaction of a student
            TableRow.LayoutParams aParams = new TableRow.LayoutParams(AbsListView.LayoutParams.
                    WRAP_CONTENT, AbsListView.LayoutParams.WRAP_CONTENT);
            aParams.topMargin = 2;
            aParams.rightMargin = 2;
            TableRow tr = new TableRow(this);
            if (j % 2 != 0) {
                tr.setBackgroundColor(Color.parseColor("#D3D3D3"));
            } else if(j == 0 ){
                tr.setBackgroundColor(Color.parseColor("#d6f5d6"));
            } else {
                tr.setBackgroundColor(Color.WHITE);
            }



            TextView t1v = new TextView(this);
            t1v.setText(names[j]);
            t1v.setTextSize(16);
            t1v.setLayoutParams(new TableRow.LayoutParams(0, TableRow.LayoutParams.MATCH_PARENT, 0.5f));
            t1v.setGravity(Gravity.LEFT);
            t1v.setTextColor(Color.BLACK);
            t1v.setPadding(6, 10, 0, 0);
            tr.addView(t1v);

            TextView t2v = new TextView(this);
            t2v.setText(email[j]);
            t2v.setTextSize(16);
            t2v.setPadding(18, 10, 18, 18);
            t2v.setLayoutParams(new TableRow.LayoutParams(0, TableRow.LayoutParams.MATCH_PARENT, 0.5f));
            t2v.setGravity(Gravity.RIGHT);
            t2v.setTextColor(Color.BLACK);
            tr.addView(t2v);

            TextView t3v = new TextView(this);
            t3v.setText(phone[j]);
            t3v.setTextSize(16);
            t3v.setPadding(18, 10, 18, 18);
            t3v.setLayoutParams(new TableRow.LayoutParams(0, TableRow.LayoutParams.MATCH_PARENT, 0.5f));
            t3v.setGravity(Gravity.RIGHT);
            t3v.setTextColor(Color.BLACK);
            tr.addView(t3v);

            stk.addView(tr);
        }
    }
}
