package com.example.nz160.testapplication;

import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.EditText;
import android.widget.TextView;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.content_main);

        final TextView card_text=(TextView)findViewById(R.id.card_text);
        EditText textView=(EditText)findViewById(R.id.textview);
        textView.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {

            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {

            }

            @Override
            public void afterTextChanged(Editable s) {
                String card_str="";
                String str=s.toString();
                String[] string=str.split("");
                for (int i=0;i<string.length;i++) {
                    if(i%4==0) {
                        card_str = card_str+string[i]+" ";
                    } else {
                        card_str=card_str+string[i];
                    }
                }

                card_text.setText(card_str);

            }
        });

    }

}
