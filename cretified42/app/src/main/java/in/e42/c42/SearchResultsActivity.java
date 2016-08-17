package in.e42.c42;

/**
 * Created by Pranoy on 1/20/2015.
 */

import android.app.ActionBar;
import android.app.Activity;
import android.app.SearchManager;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.Typeface;
import android.os.AsyncTask;
import android.os.Bundle;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.view.Gravity;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.StatusLine;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONArray;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;

public class SearchResultsActivity extends Activity {

    private TextView txtQuery;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_search_results);

        // get the action bar
        ActionBar actionBar = getActionBar();

        // Enabling Back navigation on Action Bar icon
        actionBar.setDisplayHomeAsUpEnabled(true);

        txtQuery = (TextView) findViewById(R.id.txtQuery);

        handleIntent(getIntent());
    }

    @Override
    protected void onNewIntent(Intent intent) {
        setIntent(intent);
        handleIntent(intent);
    }

    class DemoTask extends AsyncTask<String, Void, JSONObject> {
        StringBuilder sb = new StringBuilder();

        protected JSONObject doInBackground(String... url) {
            try {
                String readJSON = getJSON(url[0]);
                try {
                    JSONObject jsonObject = new JSONObject(readJSON);
                    return jsonObject;
                    //sb.append(jsonObject.getString("status"));
                } catch (Exception e) {
                    e.printStackTrace();
                    sb.append("Something went wrong. Please contact customer care.").append("\n");
                }
            } catch (Exception ex) {
                sb.append("Something went wrong. Please contact customer care.").append("\n");
            }
            return null;
        }

        protected void onPostExecute(JSONObject result) {
            if (result.optString("status").equals("ERROR")) {
                Toast.makeText(getApplicationContext(), result.optString("message"), Toast.LENGTH_LONG).show();
            }

        }
    }

    /**
     * Handling intent data
     */
    private void handleIntent(Intent intent) {
        if (Intent.ACTION_SEARCH.equals(intent.getAction())) {
            String query = intent.getStringExtra(SearchManager.QUERY);
            /**
             * Use this query to display search results like
             * 1. Getting the data from SQLite and showing in listview
             * 2. Making webrequest and displaying the data
             * For now we just display the query only
             */
            //txtQuery.setText("Search Query: " + query);
            DemoTask dt = new DemoTask();
            String url = TagViewer.BASEURL+"app/certificate/verify/rollno/"+query;
            dt.execute(url);
        }

    }

    public String getJSON(String address) {
        StringBuilder builder = new StringBuilder();
        HttpClient client = new DefaultHttpClient();
        HttpGet httpGet = new HttpGet(address);
        try {
            HttpResponse response = client.execute(httpGet);
            StatusLine statusLine = response.getStatusLine();
            int statusCode = statusLine.getStatusCode();
            if (statusCode == 200) {
                HttpEntity entity = response.getEntity();
                InputStream content = entity.getContent();
                BufferedReader reader = new BufferedReader(new InputStreamReader(content));
                String line;
                while ((line = reader.readLine()) != null) {
                    builder.append(line);
                }
            } else {
                builder.append("Failed JSON object");
            }
        } catch (ClientProtocolException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return builder.toString();
    }

    void buildCertificateViews(JSONObject result) {

        setContentView(R.layout.tag_certificate);

        RelativeLayout mTagContent = (RelativeLayout) findViewById(R.id.certificate);
        mTagContent.setBackgroundColor(Color.parseColor("#fafafa"));

        Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");

        TextView textView = (TextView) findViewById(R.id.auth_text);
        textView.setText("The originality of the document cannot be confirmed as it has not not been certified through its digital ID");
        textView.setTextSize(12);
        textView.setTypeface(typeface);

        TextView textView2 = (TextView) findViewById(R.id.org_name);
        textView2.setText(result.optString("org_name"));
        textView2.setTextSize(18);
        textView2.setTypeface(typeface);
        // show The Image
        new DownloadImageTask((ImageView) findViewById(R.id.org_logo))
                .execute(TagViewer.BASEURL+"uploads/images/org/" + result.optString("org_id") + "/" + result.optString("org_logo"));

        TextView textView1 = (TextView) findViewById(R.id.name);
        textView1.setText(result.optString("name"));
        textView1.setTextSize(24);
        textView1.setTypeface(typeface);

        // show The Image
        new DownloadImageTask((ImageView) findViewById(R.id.photo))
                .execute(TagViewer.BASEURL+"uploads/images/org/" + result.optString("org_id") + "/certificate/" + result.optString("photo"));


        TableLayout stk = (TableLayout) findViewById(R.id.table_content);
        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);
        try {
            JSONObject json = result;
            JSONArray tag_name_arr = json.getJSONArray("tag_name");
            JSONArray tag_value_arr = json.getJSONArray("tag_value");

            for (int i = 0; i < Integer.parseInt(result.optString("tag_rows")); i++) {

                TableRow tr = new TableRow(this);
                if (i % 2 != 0)
                    tr.setBackgroundColor(Color.parseColor("#f7f7f7"));

                TextView t1v = new TextView(this);
                t1v.setText(tag_name_arr.getString(i));
                t1v.setTextSize(16);
                t1v.setPadding(18, 18, 18, 18);
                t1v.setLayoutParams(new TableRow.LayoutParams(0, TableRow.LayoutParams.MATCH_PARENT, 0.5f));
                t1v.setPadding(21, 10, 0, 0);
                t1v.setGravity(Gravity.LEFT);
                t1v.setTypeface(typeface);
                tr.addView(t1v);

                TextView t2v = new TextView(this);
                t2v.setText(tag_value_arr.getString(i));
                t2v.setTextSize(16);
                t2v.setPadding(18, 18, 18, 18);
                t2v.setLayoutParams(new TableRow.LayoutParams(0, TableRow.LayoutParams.MATCH_PARENT, 0.5f));
                t2v.setGravity(Gravity.CENTER);
                t2v.setTypeface(typeface);
                tr.addView(t2v);

                stk.addView(tr);
            }

        }
        catch (Exception e){

        }
    }

    private class DownloadImageTask extends AsyncTask<String, Void, Bitmap> {
        ImageView bmImage;

        public DownloadImageTask(ImageView bmImage) {
            this.bmImage = bmImage;
        }

        protected Bitmap doInBackground(String... urls) {
            String urldisplay = urls[0];
            Bitmap mIcon11 = null;
            try {
                InputStream in = new java.net.URL(urldisplay).openStream();
                mIcon11 = BitmapFactory.decodeStream(in);
            } catch (Exception e) {
                Log.e("Error", e.getMessage());
                e.printStackTrace();
            }
            return mIcon11;
        }

        protected void onPostExecute(Bitmap result) {
            bmImage.setImageBitmap(result);
        }
    }

    public void onBackPressed(){
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);
    }

    public void onStop() {
        super.onStop();
    }

    public void onDestroy() {
        super.onDestroy();
    }
}