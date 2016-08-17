package in.e42.c42;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.CountDownTimer;

/**
 * Created by CPC2 on 4/30/2015.
 */
public class TagPage extends Activity {


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.tag_viewer);

        CountDownTimer c= new CountDownTimer(2000,1000) {
            public void onFinish() {
                Intent startActivity = new Intent(TagPage.this,TagViewer.class);
                startActivity(startActivity);
                finish();
            }

            public void onTick(long millisUntilFinished) {
            }

        }.start();

    }

    public void onBackPressed(){
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);

    }
}

