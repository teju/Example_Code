package in.e42.c42;

/**
 * Created by Pranoy on 1/20/2015.
 */

import android.app.ActionBar;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;

public class SearchFound extends Activity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_location_found);

        // get the action bar
        ActionBar actionBar = getActionBar();

        // Enabling Back navigation on Action Bar icon
        actionBar.setDisplayHomeAsUpEnabled(true);
    }
    public void onBackPressed(){
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);

    }
}