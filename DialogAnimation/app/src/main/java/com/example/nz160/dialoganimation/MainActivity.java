package com.example.nz160.dialoganimation;

import android.app.Dialog;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.view.View;

public class MainActivity extends AppCompatActivity {

    private Dialog dialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.content_main);
    }

    public void dialog(View view){
        dialog = new Dialog(this);
        dialog.setContentView(R.layout.dialog);
        dialog.getWindow().getAttributes().windowAnimations = R.style.dialog_animation;
        dialog.show();
    }

}
