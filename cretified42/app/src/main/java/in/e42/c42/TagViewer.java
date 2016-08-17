/*
 * Copyright (C) 2010 The Android Open Source Project
 * Copyright (C) 2011 Adam Nybäck
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package in.e42.c42;

import android.app.ActionBar;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.app.SearchManager;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.Typeface;
import android.nfc.NdefMessage;
import android.nfc.NdefRecord;
import android.nfc.NfcAdapter;
import android.nfc.Tag;
import android.nfc.tech.MifareClassic;
import android.nfc.tech.MifareUltralight;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.os.Parcelable;
import android.provider.Settings;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.SearchView;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.StatusLine;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.nio.charset.Charset;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import in.e42.c42.record.ParsedNdefRecord;

/**
 * An {@link android.app.Activity} which handles a broadcast of a new tag that the device just discovered.
 */
public class TagViewer extends Activity {

    private static final DateFormat TIME_FORMAT = SimpleDateFormat.getDateTimeInstance();
    private LinearLayout mTagContent;
    private ActionBar actionBar;

    private NfcAdapter mAdapter;
    private PendingIntent mPendingIntent;
    private NdefMessage mNdefPushMessage;

    private AlertDialog mDialog;

    public static String BASEURL = "http://c.e42.in/";
    ProgressDialog dialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.tag_detecting);

        Context context = this;
        PackageManager packageManager = context.getPackageManager();

        // if device support camera?
        if (packageManager.hasSystemFeature(PackageManager.FEATURE_NFC)) {
            //yes
            Log.i("NFC", "This device has NFC!");
            actionBar = getActionBar();

            // Hide the action bar title
            //actionBar.setDisplayShowTitleEnabled(false);
            // Enabling Spinner dropdown navigation
            //actionBar.setNavigationMode(ActionBar.NAVIGATION_MODE_LIST);
            if (Build.VERSION.SDK_INT < 16) {
                getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN,
                        WindowManager.LayoutParams.FLAG_FULLSCREEN);
            }

            if (getIntent().getBooleanExtra("EXIT", false)) {
                finish();
            }

            resolveIntent(getIntent());

            //RelativeLayout mTagContent = (RelativeLayout) findViewById(R.id.list);
            Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");
            TextView textView = (TextView) findViewById(R.id.list_text);
            textView.setTextSize(25);
            textView.setTypeface(typeface);

            mDialog = new AlertDialog.Builder(this).setNeutralButton("Ok", null).create();
            try {
                mAdapter = NfcAdapter.getDefaultAdapter(this);
                if (mAdapter == null) {
                    showMessage(R.string.error, R.string.no_nfc);
                    finish();
                    return;
                }
            }
            catch (Exception e)
            {
                e.printStackTrace();
                Toast.makeText(getApplication(),"Please enable Nfc in Mobile",Toast.LENGTH_LONG);
            }

            mPendingIntent = PendingIntent.getActivity(this, 0,
                    new Intent(this, getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);
            mNdefPushMessage = new NdefMessage(new NdefRecord[] { newTextRecord(
                    "Message from NFC Reader :-)", Locale.ENGLISH, true) });
        } else {
            // No NFC
            Log.i("NFC", "This device has no NFC!");
            Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");

            TextView textView = (TextView) findViewById(R.id.list_text);
            textView.setText("NFC is not available for your mobile. Please use Roll No for verifying the Certificate.");
            textView.setTypeface(typeface);
        }
        Log.i("NFC NEXT", "This device has no NFC!");
    }
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.activity_main_actions, menu);

        // Associate searchable configuration with the SearchView
        SearchManager searchManager = (SearchManager) getSystemService(Context.SEARCH_SERVICE);
        SearchView searchView = (SearchView) menu.findItem(R.id.action_search)
                .getActionView();
        searchView.setSearchableInfo(searchManager
                .getSearchableInfo(getComponentName()));

        return super.onCreateOptionsMenu(menu);
    }

    /**
     * On selecting action bar icons
     * */
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Take appropriate action for each action item click
        switch (item.getItemId()) {
            case R.id.action_search:

                return true;
            //case R.id.action_location_found:
            // location found
            //  LocationFound();
            //return true;
            //case R.id.action_refresh:
            // refresh
            //return true;
            //case R.id.action_help:
            // help action
            //return true;
            //case R.id.action_check_updates:
            // check for updates action
            //return true;
            default:
                return super.onOptionsItemSelected(item);
        }
    }

    /**
     * Launching new activity
     * */
    private void LocationFound() {
        Intent i = new Intent(TagViewer.this, SearchFound.class);
        startActivity(i);
    }

    private void searchFound() {
        Intent i = new Intent(TagViewer.this, SearchResultsActivity.class);
        startActivity(i);
    }

    private void showMessage(int title, int message) {
        mDialog.setTitle(title);
        mDialog.setMessage(getText(message));
        mDialog.show();
    }

    private NdefRecord newTextRecord(String text, Locale locale, boolean encodeInUtf8) {
        byte[] langBytes = locale.getLanguage().getBytes(Charset.forName("US-ASCII"));

        Charset utfEncoding = encodeInUtf8 ? Charset.forName("UTF-8") : Charset.forName("UTF-16");
        byte[] textBytes = text.getBytes(utfEncoding);

        int utfBit = encodeInUtf8 ? 0 : (1 << 7);
        char status = (char) (utfBit + langBytes.length);

        byte[] data = new byte[1 + langBytes.length + textBytes.length];
        data[0] = (byte) status;
        System.arraycopy(langBytes, 0, data, 1, langBytes.length);
        System.arraycopy(textBytes, 0, data, 1 + langBytes.length, textBytes.length);

        return new NdefRecord(NdefRecord.TNF_WELL_KNOWN, NdefRecord.RTD_TEXT, new byte[0], data);
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (mAdapter != null) {
            if (!mAdapter.isEnabled()) {
                showWirelessSettingsDialog();
            }
            mAdapter.enableForegroundDispatch(this, mPendingIntent, null, null);
            mAdapter.enableForegroundNdefPush(this, mNdefPushMessage);
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        if (mAdapter != null) {
            mAdapter.disableForegroundDispatch(this);
            mAdapter.disableForegroundNdefPush(this);
        }
    }

    private void showWirelessSettingsDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setMessage(R.string.nfc_disabled);
        builder.setPositiveButton(android.R.string.ok, new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialogInterface, int i) {
                Intent intent = new Intent(Settings.ACTION_WIRELESS_SETTINGS);
                startActivity(intent);
            }
        });
        builder.setNegativeButton(android.R.string.cancel, new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialogInterface, int i) {
                finish();
            }
        });
        builder.create().show();
        return;
    }

    private void resolveIntent(Intent intent) {
        String action = intent.getAction();
        if (NfcAdapter.ACTION_TAG_DISCOVERED.equals(action)
                || NfcAdapter.ACTION_TECH_DISCOVERED.equals(action)
                || NfcAdapter.ACTION_NDEF_DISCOVERED.equals(action)) {
            Parcelable[] rawMsgs = intent.getParcelableArrayExtra(NfcAdapter.EXTRA_NDEF_MESSAGES);
            NdefMessage[] msgs;
            //if (rawMsgs != null) {
            //    msgs = new NdefMessage[rawMsgs.length];
            //    for (int i = 0; i < rawMsgs.length; i++) {
            //        msgs[i] = (NdefMessage) rawMsgs[i];
            //    }
            //} else {
            // Unknown tag type
            byte[] empty = new byte[0];
            byte[] id = intent.getByteArrayExtra(NfcAdapter.EXTRA_ID);
            Parcelable tag = intent.getParcelableExtra(NfcAdapter.EXTRA_TAG);
            byte[] payload = dumpTagData(tag).getBytes();
            NdefRecord record = new NdefRecord(NdefRecord.TNF_UNKNOWN, empty, id, payload);
            NdefMessage msg = new NdefMessage(new NdefRecord[] { record });
            msgs = new NdefMessage[] { msg };
            //}
            // Setup the views
            buildTagViews(msgs);
        }
    }

    class DemoTask extends AsyncTask<String, Void, JSONObject> {
        StringBuilder sb = new StringBuilder();

        protected JSONObject doInBackground(String... url) {
            try {
                String readJSON = getJSON(url[0]);
                try{
                    JSONObject jsonObject = new JSONObject(readJSON);
                    System.out.println("jsonObjecttostring "+jsonObject.toString());
                    return jsonObject;
                    //sb.append(jsonObject.getString("status"));
                } catch(Exception e){
                    e.printStackTrace();
                    sb.append("Something went wrong. Please contact customer care.").append("\n");
                }
            } catch (Exception ex) {
                sb.append("Something went wrong. Please contact customer care.").append("\n");
            }
            return null;
        }
        protected void onPostExecute(JSONObject result) {
            if ( result.optString("status").equals("ERROR") ) {
                Toast.makeText(getApplicationContext(), result.optString("message"), Toast.LENGTH_LONG).show();
                dialog.dismiss();
            } else {
                buildCertificateViews(result);
                dialog.dismiss();
            }
        }
    }

    public byte[] id;
    private String dumpTagData(Parcelable p) {
        StringBuilder sb = new StringBuilder();

        Tag tag = (Tag) p;
        id = tag.getId();
        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                TELEPHONY_SERVICE);
        DemoTask dt = new DemoTask();
        String url = BASEURL+"certificate/verify/"+getHex(id);
        System.out.println("BASEURLIS "+url);
        System.out.println("url " + url);
        //*****************************************************************

        //*****************************************************************
        dt.execute(url);

        dialog = ProgressDialog.show(this, "", "Please wait...", true);

        //Toast.makeText(getApplicationContext(), "Please Wait..", Toast.LENGTH_SHORT).show();
        sb.append("Tag ID: ").append(getHex(id)).append("\n");

        //finally{System.out.println("Success");
        //sb.append("Tag ID (dec): ").append(getDec(id)).append("\n");
        //sb.append("ID (reversed): ").append(getReversed(id)).append("\n");

        String prefix = "android.nfc.tech.";
        //sb.append("Technologies: ");
        for (String tech : tag.getTechList()) {
            //sb.append(tech.substring(prefix.length()));
            //sb.append(", ");
        }
        //sb.delete(sb.length() - 2, sb.length());
        for (String tech : tag.getTechList()) {
            if (tech.equals(MifareClassic.class.getName())) {
                //sb.append('\n');
                MifareClassic mifareTag = MifareClassic.get(tag);
                String type = "Unknown";
                switch (mifareTag.getType()) {
                    case MifareClassic.TYPE_CLASSIC:
                        type = "Classic";
                        break;
                    case MifareClassic.TYPE_PLUS:
                        type = "Plus";
                        break;
                    case MifareClassic.TYPE_PRO:
                        type = "Pro";
                        break;
                }
                //sb.append("Mifare Classic type: ");
                //sb.append(type);
                //sb.append('\n');

                //sb.append("Mifare size: ");
                //sb.append(mifareTag.getSize() + " bytes");
                //sb.append('\n');

                //sb.append("Mifare sectors: ");
                //sb.append(mifareTag.getSectorCount());
                //sb.append('\n');

                //sb.append("Mifare blocks: ");
                //sb.append(mifareTag.getBlockCount());
            }

            if (tech.equals(MifareUltralight.class.getName())) {
                //sb.append('\n');
                MifareUltralight mifareUlTag = MifareUltralight.get(tag);
                String type = "Unknown";
                switch (mifareUlTag.getType()) {
                    case MifareUltralight.TYPE_ULTRALIGHT:
                        type = "Ultralight";
                        break;
                    case MifareUltralight.TYPE_ULTRALIGHT_C:
                        type = "Ultralight C";
                        break;
                }
                //sb.append("Mifare Ultralight type: ");
                //sb.append(type);
            }
        }

        return sb.toString();
    }

    private String getHex(byte[] bytes) {
        StringBuilder sb = new StringBuilder();
        //for (int i = bytes.length - 1; i >= 0; --i) {
        for (int i = 0; i <= bytes.length-1; i++) {
            int b = bytes[i] & 0xff;
            if (b < 0x10)
                sb.append('0');
            sb.append(Integer.toHexString(b));
            if (i >= 0 && i != bytes.length-1) {
                sb.append(":");
            }
        }
        return sb.toString();
    }

    private long getDec(byte[] bytes) {
        long result = 0;
        long factor = 1;
        for (int i = 0; i < bytes.length; ++i) {
            long value = bytes[i] & 0xffl;
            result += value * factor;
            factor *= 256l;
        }
        return result;
    }

    private long getReversed(byte[] bytes) {
        long result = 0;
        long factor = 1;
        for (int i = bytes.length - 1; i >= 0; --i) {
            long value = bytes[i] & 0xffl;
            result += value * factor;
            factor *= 256l;
        }
        return result;
    }

    void buildTagViews(NdefMessage[] msgs) {
        if (msgs == null || msgs.length == 0) {
            return;
        }
        LayoutInflater inflater = LayoutInflater.from(this);
        LinearLayout content = mTagContent;

        // Parse the first message in the list
        // Build views for all of the sub records
        Date now = new Date();
        List<ParsedNdefRecord> records = NdefMessageParser.parse(msgs[0]);
        final int size = records.size();
        for (int i = 0; i < size; i++) {
            TextView timeView = new TextView(this);
            timeView.setText(TIME_FORMAT.format(now));
            //content.addView(timeView, 0);
            ParsedNdefRecord record = records.get(i);
            //content.addView(record.getView(this, inflater, content, i), 1 + i);
            //content.addView(inflater.inflate(R.layout.tag_divider, content, false), 2 + i);
        }
    }

    void buildCertificateViews(final JSONObject result) {

        setContentView(R.layout.tag_certificate);

        RelativeLayout mTagContent = (RelativeLayout) findViewById(R.id.certificate);
        mTagContent.setBackgroundColor(Color.parseColor("#fafafa"));

        Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");

        TextView textView = (TextView) findViewById(R.id.auth_text);
        textView.setText("The authenticity of the document has been certified through its digital ID");
        textView.setTextSize(12);
        textView.setTypeface(typeface);

        TextView textView1 = (TextView) findViewById(R.id.org_name);
        textView1.setText(result.optString("org_name"));
        textView1.setTextSize(15);
        textView1.setTypeface(typeface);

        // show The Image
        new DownloadImageTask((ImageView) findViewById(R.id.org_logo))
                .execute(BASEURL+"uploads/images/org/" + result.optString( "org_id") + "/" + result.optString("org_logo"));

        TextView textView2 = (TextView) findViewById(R.id.name);
        textView2.setText(result.optString("name"));
        textView2.setTextSize(24);
        textView2.setTypeface(typeface);

        // show The Image
        new DownloadImageTask((ImageView) findViewById(R.id.photo))
                .execute(BASEURL+"uploads/images/org/" + result.optString("org_id") + "/certificate/" + result.optString("photo"));

        TableLayout stk = (TableLayout) findViewById(R.id.table_content);
        try {
            JSONObject json = result;
            System.out.println("JSON_RESULT_IS "+json.toString()+" tagrows "+json.optString("tag_rows"));
            if(!json.optString("tag_rows").equals("0")) {
                JSONArray tag_name_arr = json.getJSONArray("tag_name");
                JSONArray tag_value_arr = json.getJSONArray("tag_value");
                System.out.println("tag_name_arr " + tag_name_arr + " tag_value_arr " + tag_value_arr);

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

            /*Button req_report = (Button)this.findViewById(R.id.req_report);
            req_report.setTypeface(typeface);
            req_report.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                final Thread t = new Thread(new Runnable() {
                    @Override
                    public void run() {
                    try {
                        runOnUiThread(new Runnable() {

                            @Override
                            public void run() {
                                progressDialog();
                            }
                        });
                        // Creating HTTP client
                        HttpClient httpClient = new DefaultHttpClient();
                        // Creating HTTP Post
                        System.out.println("HttpClient Before");
                        HttpPost httpPost = new HttpPost(BASEURL + "app/certificate");
                        System.out.println("HttpClient " + httpPost);

                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                                TELEPHONY_SERVICE);

                        // Building post parameters, key and value pair
                        List<NameValuePair> nameValuePair = new ArrayList<NameValuePair>(2);
                        nameValuePair.add(new BasicNameValuePair("do", "req_report"));
                        nameValuePair.add(new BasicNameValuePair("nfc_tag_id", result.optString("nfc_tag_id")));
                        nameValuePair.add(new BasicNameValuePair("imei", telephonyManager.getDeviceId()));
                        nameValuePair.add(new BasicNameValuePair("nfc_tag_id", getHex(id)));
                        System.out.println("REPORTTTNFCTAGID "+getHex(id));

                        // Url Encoding the POST parameters
                        try {
                            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePair));
                            //httpPost.setHeader("Accept", "application/json");
                            //httpPost.setHeader("Content-type", "application/json");
                        } catch (UnsupportedEncodingException e) {
                            // writing error to Log
                            e.printStackTrace();
                        }

                        // Making HTTP Request
                        try {
                            HttpResponse response = httpClient.execute(httpPost);
                            String json = EntityUtils.toString(response.getEntity());
                            System.out.println("httpClient_test "+json);

                            final JSONObject json_result = new JSONObject(json);
                            System.out.println("!@#$RESPONSE "+json_result);
                            System.out.println("isactive "+json_result.optString("is_active"));
                            if(json_result.optString("userimei_rows").equals("1")) {
                                if (json_result.optString("status").equals("OK")) {
                                    if(json_result.optString("is_active").equals("1")) {
                                        sendEmail(json_result);
                                        showToastMessage("Report request was success" +
                                                "and sent to " + json_result.optString("email_id"));
                                        runOnUiThread(new Runnable() {

                                            @Override
                                            public void run() {
                                                closeProgessDialog();
                                            }
                                        });

                                    } else {
                                        runOnUiThread(new Runnable() {

                                            @Override
                                            public void run() {
                                                closeProgessDialog();
                                                otpAlert(json_result.optString("email_id"), "Verification Code sent to "+json_result.optString("email_id"),getHex(id));
                                            }
                                        });
                                    }
                                } else {
                                    showToastMessage("There was an error requesting" +
                                            " the report. Please try later");
                                }
                            } else {
                                runOnUiThread(new Runnable() {

                                    @Override
                                    public void run() {
                                        closeProgessDialog();
                                        emailAlert(getHex(id));
                                    }
                                });
                            }
                        }catch(ClientProtocolException e){
                            // writing exception to log
                            e.printStackTrace();
                            runOnUiThread(new Runnable() {

                                @Override
                                public void run() {
                                    Toast.makeText(getApplicationContext(), "ClientProtocolException", Toast.LENGTH_LONG).show();
                                }
                            });
                        }catch(IOException e){
                            // writing exception to log
                            runOnUiThread(new Runnable() {

                                @Override
                                public void run() {
                                    Toast.makeText(getApplicationContext(),"IOException",Toast.LENGTH_LONG).show();
                                }
                            });
                            e.printStackTrace();
                        }catch(JSONException e){
                            runOnUiThread(new Runnable() {

                                @Override
                                public void run() {
                                    Toast.makeText(getApplicationContext(),"JSONException",Toast.LENGTH_LONG).show();
                                }
                            });
                            e.printStackTrace();
                        }
                    } catch (RuntimeException e) {
                        runOnUiThread(new Runnable() {

                            @Override
                            public void run() {
                                Toast.makeText(getApplicationContext(),"RuntimeException",Toast.LENGTH_LONG).show();
                            }
                        });
                        e.printStackTrace();
                    }
                    }
                });
                t.start();
                }
            }); */
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

    @Override
    public void onNewIntent(Intent intent) {
        setIntent(intent);
        resolveIntent(intent);
    }

    public String getJSON(String address){
        StringBuilder builder = new StringBuilder();
        HttpClient client = new DefaultHttpClient();
        HttpGet httpGet = new HttpGet(address);
        //HttpPost ajs
        try{
            HttpResponse response = client.execute(httpGet);
            StatusLine statusLine = response.getStatusLine();
            int statusCode = statusLine.getStatusCode();
            if(statusCode == 200){
                HttpEntity entity = response.getEntity();
                InputStream content = entity.getContent();
                BufferedReader reader = new BufferedReader(new InputStreamReader(content));
                String line;
                while((line = reader.readLine()) != null){
                    builder.append(line);
                }
            } else {
                builder.append("Failed JSON object");
            }
        }catch(ClientProtocolException e){
            e.printStackTrace();
        } catch (IOException e){
            e.printStackTrace();
        }
        return builder.toString();
    }

    public void showToastMessage(final String message) {
        runOnUiThread(new Runnable() {

            @Override
            public void run() {
                Log.d("showToastMessage():",message);
                Toast.makeText(getApplication(),message,Toast.LENGTH_LONG).show();

            }
        });
    }

    public void emailAlert(final String nfc_tag_id) {
        LayoutInflater li = LayoutInflater.from(TagViewer.this);
        final View promptsView = li.inflate(R.layout.prompts, null);
        final EditText userInput = (EditText) promptsView
                .findViewById(R.id.email);
        userInput.setVisibility(View.VISIBLE);
        TextView textView =(TextView)promptsView.findViewById(R.id.textView1);
        textView.setVisibility(View.VISIBLE);
        new AlertDialog.Builder(this)
            .setTitle("Register")
            .setView(promptsView)
            .setPositiveButton("OK",
                new DialogInterface.OnClickListener() {
                    public void onClick(final DialogInterface dialog, final int id) {
                        if(!userInput.getText().toString().equals("")) {
                            final Thread t = new Thread(new Runnable() {
                                @Override
                                public void run() {
                                    try {
                                        runOnUiThread(new Runnable() {

                                            @Override
                                            public void run() {
                                                progressDialog();
                                            }
                                        });
                                        // Creating HTTP client
                                        HttpClient httpClient = new DefaultHttpClient();
                                        // Creating HTTP Post
                                        System.out.println("OTP_HttpClient Before");
                                        HttpPost httpPost = new HttpPost(BASEURL + "app/user");
                                        System.out.println("OTP_HttpClient " + httpPost);
                                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                                                TELEPHONY_SERVICE);
                                        // Building post parameters, key and value pair
                                        List<NameValuePair> nameValuePair = new ArrayList<NameValuePair>(2);
                                        nameValuePair.add(new BasicNameValuePair("do", "register"));
                                        nameValuePair.add(new BasicNameValuePair("imei", telephonyManager.getDeviceId()));
                                        nameValuePair.add(new BasicNameValuePair("email_id",userInput.getText().toString() ));
                                        nameValuePair.add(new BasicNameValuePair("nfc_tag_id", nfc_tag_id));
                                        System.out.println("OOTPNFCTAGID "+nfc_tag_id);
                                        System.out.println("OTP "+userInput.getText().toString() );
                                        System.out.println("OTPNameValuePair "+nameValuePair.toString());

                                        // Url Encoding the POST parameters
                                        try {
                                            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePair));
                                            //httpPost.setHeader("Accept", "application/json");
                                            //httpPost.setHeader("Content-type", "application/json");
                                        } catch (UnsupportedEncodingException e) {
                                            // writing error to Log
                                            e.printStackTrace();
                                        }
                                        // Making HTTP Request
                                        try {
                                            HttpResponse response = httpClient.execute(httpPost);
                                            String json = EntityUtils.toString(response.getEntity());
                                            System.out.println("OTPhttpClient_test "+json);

                                            final JSONObject json_result = new JSONObject(json);
                                            System.out.println("OTPHttpResponse " + json_result.toString());
                                            System.out.println("OTPSTATUS_IS " + json_result.optString("status"));
                                            System.out.println("OTPMESSAGE_IS " + json_result.optString("message"));

                                            runOnUiThread(new Runnable() {

                                                @Override
                                                public void run() {
                                                    if (json_result.optString("status").equals("OK")) {
                                                        closeProgessDialog();
                                                        otpAlert(userInput.getText().toString(),json_result.optString("message"),nfc_tag_id);
                                                    } else {
                                                        new AlertDialog.Builder(TagViewer.this)
                                                            .setTitle("Message")
                                                            .setMessage(json_result.optString("message"))
                                                            .setPositiveButton("OK", new DialogInterface.OnClickListener() {
                                                                public void onClick(DialogInterface dialog, int id) {
                                                                    dialog.cancel();
                                                                }
                                                            }).show();                                                            }
                                                }
                                            });

                                        }catch(ClientProtocolException e){
                                            // writing exception to log
                                            e.printStackTrace();

                                        }catch(IOException e){
                                            // writing exception to log
                                            e.printStackTrace();
                                        }catch(JSONException e){
                                            e.printStackTrace();
                                        }
                                    } catch (RuntimeException e) {
                                        e.printStackTrace();
                                    }
                                }
                            });
                            t.start();

                        } else {
                            Toast.makeText(getApplicationContext(),"Please enter your emal id",
                                    Toast.LENGTH_LONG).show();
                        }
                        // dialog.cancel();
                    }
                })
            .setNegativeButton("cancel", new DialogInterface.OnClickListener() {
                public void onClick(DialogInterface dialog, int id) {
                    dialog.cancel();
                }
            }).show();
    }

    public void otpAlert(String email,String message, final String nfc_tag_id) {
        LayoutInflater li = LayoutInflater.from(TagViewer.this);
        final View promptsView = li.inflate(R.layout.prompts, null);
        final TextView userInput = (TextView) promptsView
                .findViewById(R.id.textView2);
        userInput.setText(email);
        userInput.setVisibility(View.VISIBLE);
        final EditText otp = (EditText) promptsView
                .findViewById(R.id.otp);
        otp.setVisibility(View.VISIBLE);
        TextView textView =(TextView)promptsView.findViewById(R.id.textView);
        textView.setVisibility(View.VISIBLE);
        textView.setText(message);
        userInput.setText(email);
        new AlertDialog.Builder(this)
            .setTitle("Register")
            .setView(promptsView)
            .setPositiveButton("OK",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, final int id) {
                        if (!userInput.getText().toString().equals("")) {
                            final Thread t = new Thread(new Runnable() {
                                @Override
                                public void run() {
                                    try {
                                        runOnUiThread(new Runnable() {

                                            @Override
                                            public void run() {
                                                Toast.makeText(getApplicationContext(),"Please wait...",
                                                        Toast.LENGTH_LONG).show();
                                            }
                                        });
                                        // Creating HTTP client
                                        HttpClient httpClient = new DefaultHttpClient();
                                        // Creating HTTP Post
                                        System.out.println("OTP_HttpClient Before");
                                        HttpPost httpPost = new HttpPost(BASEURL + "app/user");
                                        System.out.println("OTP_HttpClient " + httpPost);
                                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                                                TELEPHONY_SERVICE);
                                        // Building post parameters, key and value pair
                                        List<NameValuePair> nameValuePair = new ArrayList<NameValuePair>(2);
                                        nameValuePair.add(new BasicNameValuePair("do", "app_verify_otp"));
                                        nameValuePair.add(new BasicNameValuePair("email_id", userInput.getText().toString()));
                                        nameValuePair.add(new BasicNameValuePair("otp", otp.getText().toString()));
                                        nameValuePair.add(new BasicNameValuePair("imei", telephonyManager.getDeviceId()));
                                        nameValuePair.add(new BasicNameValuePair("nfc_tag_id",nfc_tag_id));
                                        System.out.println("VERIFYOOTPNFCTAGID "+nfc_tag_id);
                                        System.out.println("OTP " + otp.getText().toString());
                                        System.out.println("OTPNameValuePair " + nameValuePair.toString());

                                        // Url Encoding the POST parameters
                                        try {
                                            httpPost.setEntity(new UrlEncodedFormEntity(nameValuePair));
                                            //httpPost.setHeader("Accept", "application/json");
                                            //httpPost.setHeader("Content-type", "application/json");
                                        } catch (UnsupportedEncodingException e) {
                                            // writing error to Log
                                            e.printStackTrace();
                                        }
                                        // Making HTTP Request
                                        try {
                                            HttpResponse response = httpClient.execute(httpPost);
                                            String json = EntityUtils.toString(response.getEntity());
                                            System.out.println("OTPhttpClient_test " + json);

                                            final JSONObject json_result = new JSONObject(json);
                                            System.out.println("123654OTPHttpResponse " + json_result.toString());
                                            System.out.println("OTPSTATUS_IS " + json_result.optString("status"));
                                            System.out.println("OTPMESSAGE_IS " + json_result.optString("message"));

                                            runOnUiThread(new Runnable() {

                                                @Override
                                                public void run() {
                                                    new AlertDialog.Builder(TagViewer.this)
                                                            .setTitle("Message")
                                                            .setMessage(json_result.optString("message"))
                                                            .setPositiveButton("OK", new DialogInterface.OnClickListener() {
                                                                public void onClick(DialogInterface dialog, int id) {
                                                                    dialog.cancel();
                                                                }
                                                            }).show();
                                                    sendEmail(json_result);
                                                }
                                            });

                                        } catch (ClientProtocolException e) {
                                            // writing exception to log
                                            e.printStackTrace();

                                        } catch (IOException e) {
                                            // writing exception to log
                                            e.printStackTrace();
                                        } catch (JSONException e) {
                                            e.printStackTrace();
                                        }
                                    } catch (RuntimeException e) {
                                        e.printStackTrace();
                                    }
                                }
                            });
                            t.start();

                        } else {
                            Toast.makeText(getApplicationContext(), "Please enter your emal id",
                                    Toast.LENGTH_LONG).show();
                        }
                    }
                })
            .setNegativeButton("cancel", new DialogInterface.OnClickListener() {
                public void onClick(DialogInterface dialog, int id) {
                    dialog.cancel();
                }
            }).show();
    }

    public void sendEmail(final JSONObject response) {
        final Thread t = new Thread(new Runnable() {
            @Override
            public void run() {
                try {
                    // Creating HTTP client
                    HttpClient httpClient = new DefaultHttpClient();
                    // Creating HTTP Post
                    System.out.println("HttpClient Before");
                    HttpPost httpPost = new HttpPost(BASEURL + "app/certificate");
                    System.out.println("HttpClient " + httpPost);

                    TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                            TELEPHONY_SERVICE);

                    // Building post parameters, key and value pair
                    List<NameValuePair> nameValuePair = new ArrayList<NameValuePair>(2);
                    nameValuePair.add(new BasicNameValuePair("do", "send_email"));
                    nameValuePair.add(new BasicNameValuePair("report_mode", "EMAIL"));
                    nameValuePair.add(new BasicNameValuePair("subject", " Certificate Details for "));
                    nameValuePair.add(new BasicNameValuePair("content_type", "pdf_attachment"));
                    nameValuePair.add(new BasicNameValuePair("message", " Please find the below details of the student"));
                    nameValuePair.add(new BasicNameValuePair("certificate_id", response.optString("certificate_id")));
                    nameValuePair.add(new BasicNameValuePair("to",response.optString("email_id")));
                    nameValuePair.add(new BasicNameValuePair("from","pranoy@cloudnix.com"));
                    nameValuePair.add(new BasicNameValuePair("imei",telephonyManager.getDeviceId()));
                    nameValuePair.add(new BasicNameValuePair("nfc_tag_id",getHex(id)));
                    System.out.println("SENDEMAILNFCTAGID "+getHex(id));

                    // Url Encoding the POST parameters
                    try {
                        httpPost.setEntity(new UrlEncodedFormEntity(nameValuePair));
                        //httpPost.setHeader("Accept", "application/json");
                        //httpPost.setHeader("Content-type", "application/json");
                    } catch (UnsupportedEncodingException e) {
                        // writing error to Log
                        e.printStackTrace();
                    }
                    // Making HTTP Request
                    try {
                        HttpResponse response = httpClient.execute(httpPost);

                        String json = EntityUtils.toString(response.getEntity());
                        System.out.println("httpClient_test ");

                    }catch(ClientProtocolException e){
                        // writing exception to log
                        e.printStackTrace();

                    }catch(IOException e){
                        // writing exception to log
                        e.printStackTrace();
                    }
                } catch (RuntimeException e) {
                    e.printStackTrace();
                }
            }
        });
        t.start();

    }
    public void progressDialog() {
        dialog = ProgressDialog.show(this, "", "Please wait...", true);
    }

    public void closeProgessDialog(){
        dialog.dismiss();
    }
}