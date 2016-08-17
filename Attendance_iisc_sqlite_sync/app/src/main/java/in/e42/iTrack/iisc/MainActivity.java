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

package in.e42.iTrack.iisc;

import com.loopj.android.http.*;
import android.app.Activity;
import android.app.AlarmManager;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.ContextWrapper;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteException;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.Typeface;
import android.location.Address;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationManager;
import android.net.Uri;
import android.nfc.NdefMessage;
import android.nfc.NdefRecord;
import android.nfc.NfcAdapter;
import android.nfc.Tag;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.os.Parcelable;
import android.provider.MediaStore;
import android.provider.Settings;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
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
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.channels.FileChannel;
import java.nio.charset.Charset;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.Random;
import java.util.concurrent.TimeUnit;

import in.e42.iTrack.iisc.record.ParsedNdefRecord;

/**
 * An {@link Activity} which handles a broadcast of a new tag that the device just discovered.
 */
public class MainActivity extends Activity {

    private static final DateFormat TIME_FORMAT = SimpleDateFormat.getDateTimeInstance();
    private LinearLayout mTagContent;
    private String id_number;
    private String student_name;
    private String guardian_name;
    private String guardian;
    private String guardian_id;
    private String type;
    public final static String DOMAIN = "192.168.1.35";
    public final static String BASEURL = "http://" + DOMAIN + "/REPO/services/iisc/";
    public final static String MEDIA = "media/localhost/images/student/";
    private NfcAdapter mAdapter;
    private PendingIntent mPendingIntent;
    private NdefMessage mNdefPushMessage;
    private String phone;
    private String email_id;
    private AlertDialog mDialog;
    private Uri fileUri;
    public static final int MEDIA_TYPE_IMAGE = 1;
    private static final int CAMERA_CAPTURE_IMAGE_REQUEST_CODE = 100;
    private static final String TAG = MainActivity.class.getSimpleName();
    private String filePath = null;
    private String studentlog_id ;
    private String nfc_tag_id;
    long totalSize = 0;
    private String image_type;
    private boolean is_capture_image;
    public double latitude = 0.00;
    public double longitude = 0.00;

    AppLocationService appLocationService;
    Location location;
    LocationAddress locationAddress;
    String res = "Not Found";
    private String photo;
    Boolean isInternetPresent = false;
    ConnectionDetector cd;
    DataBaseHelper db;
    private String imei;
    private String reg_no;
    private  int travel_id;
    private int student_id;
    private String message;
    private int count;
    ProgressDialog prgDialog;
    private AlarmManager manager;
    private PendingIntent pendingIntent;
    private PendingIntent pendingIntentAtt;
    private PendingIntent pendingIntentUpdate;
    private int is_active;
    private String exp_dt;
    private String is_image_sync="0";
    DateFormat attendance_dateFormat = new SimpleDateFormat("yyyy-MM-dd " +
            "HH:mm:ss");
    String image_capture="null";
    Date d = new Date();
    private String iisclog_id;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.tag_viewer);
        saveToSqlite();
        cd = new ConnectionDetector(getApplicationContext());
        isInternetPresent = cd.isConnectingToInternet();
        CountDownTimer c = new CountDownTimer(2000, 1000) {
            public void onFinish() {
                setContentView(R.layout.tag_detecting);
                Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");
                TextView textView = (TextView) findViewById(R.id.list_text);
                textView.setTypeface(typeface);
                TextView textView2 = (TextView) findViewById(R.id.sync);
                textView2.setTypeface(typeface);

                // check for Internet status
                if (isInternetPresent) {
                } else {
                    showAlertDialog(MainActivity.this, "No Internet Connection",
                            "You don't have internet connection.", false);
                }
            }
            public void onTick(long millisUntilFinished) {
            }

        }.start();

        db = new DataBaseHelper(this);

        if (Build.VERSION.SDK_INT < 16) {
            getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN,
                    WindowManager.LayoutParams.FLAG_FULLSCREEN);
        }
        if (getIntent().getBooleanExtra("EXIT", false)) {
            finish();
        }
        prgDialog = new ProgressDialog(MainActivity.this);
        prgDialog.setMessage("Transferring Data from Remote MySQL DB and Syncing " +
                "SQLite. Please wait...");
        prgDialog.setCancelable(false);

        Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");
        TextView textView = (TextView) findViewById(R.id.success);
        textView.setTypeface(typeface);

        resolveIntent(getIntent());

        mDialog = new AlertDialog.Builder(this).setNeutralButton("Ok", null).create();

        mAdapter = NfcAdapter.getDefaultAdapter(this);
        if (mAdapter == null) {
            showMessage(R.string.error, R.string.no_nfc);
            finish();
            return;
        }

        if (isInternetPresent) {
            Intent alarmIntent = new Intent(MainActivity.this, UpdateAlarmReceiver.class);
            pendingIntentUpdate = PendingIntent.getBroadcast(MainActivity.this, 0, alarmIntent, 0);
            sendBroadcast(alarmIntent);
            Calendar calNow = Calendar.getInstance();
            Calendar calSet = (Calendar) calNow.clone();


            Random r = new Random();
            int minute = 54; //r.nextInt(30);

            calSet.set(Calendar.HOUR_OF_DAY, 19);
            calSet.set(Calendar.MINUTE, minute);
            calSet.set(Calendar.SECOND, 0);
            calSet.set(Calendar.MILLISECOND, 0);

            if (calSet.compareTo(calNow) <= 0) {

                calSet.add(Calendar.DATE, 1);
            }
            setUpdateAlarm(calSet);
        }
        mPendingIntent = PendingIntent.getActivity(this, 0,
                new Intent(this, getClass()).addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP), 0);
        mNdefPushMessage = new NdefMessage(new NdefRecord[]{newTextRecord(
                "Message from NFC Reader :-)", Locale.ENGLISH, true)});
    }

    public void showAlertDialog(Context context, String title, String message, Boolean status) {
        AlertDialog alertDialog = new AlertDialog.Builder(context).create();
        alertDialog.setTitle(title);
        alertDialog.setMessage(message);
        alertDialog.setButton("OK", new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
            }
        });
        mDialog.dismiss();
        alertDialog.show();

    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        return super.onCreateOptionsMenu(menu);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            default:
                return super.onOptionsItemSelected(item);
        }
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
            byte[] empty = new byte[0];
            byte[] id = intent.getByteArrayExtra(NfcAdapter.EXTRA_ID);
            Parcelable tag = intent.getParcelableExtra(NfcAdapter.EXTRA_TAG);
            byte[] payload = dumpTagData(tag).getBytes();
            NdefRecord record = new NdefRecord(NdefRecord.TNF_UNKNOWN, empty, id, payload);
            NdefMessage msg = new NdefMessage(new NdefRecord[]{record});
            msgs = new NdefMessage[]{msg};
            buildTagViews(msgs);
        }
    }

    //############################################\\
    //#  FUNCTION nfc_tag_Reader(String address) TO GET DATA #\\
    //############################################\\
    public String nfc_tag_Reader(String address) {
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

    //############################################\\
    //#  VehicleJson CLASS TO GET JSON DATA FROM URL  #\\
    //############################################\\
    class VehicleJson extends AsyncTask<String, Void, JSONObject> {
        @Override
        protected void onPreExecute() {
            mDialog = ProgressDialog.show(MainActivity.this, "Please wait...", "Retrieving Data");
        }

        StringBuilder sb = new StringBuilder();

        protected JSONObject doInBackground(String... url) {
            try {
                String readJSON = nfc_tag_Reader(url[0]);
                System.out.println("JSON_RESPONSE "+readJSON.toString());
                try {
                    JSONObject jsonObject = new JSONObject(readJSON);
                    return jsonObject;
                } catch (Exception e) {
                    e.printStackTrace();
                }
            } catch (Exception ex) {
                sb.append("Something went wrong. Please contact customer care.").append("\n");
            }
            return null;
        }

        protected void onPostExecute(JSONObject result) {

            try {
                if (result.toString().equals("{}")) {
                }
                mDialog.dismiss();
                if (result.optString("status").equals("ERROR")) {
                    setContentView(R.layout.invalidtag);
                    TextView textView = (TextView) findViewById(R.id.invalid_tag);
                    Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");
                    textView.setTypeface(typeface);
                    textView.setBackgroundColor(Color.parseColor("#F05032"));
                    textView.setTextColor(Color.WHITE);
                    textView.setText(result.optString("message"));
                    return;
                }

                //Get all the following variables from server
                nfc_tag_id = result.optString("tag_id");
                imei = result.optString("imei");
                reg_no = result.optString("vehicle_reg_no");
                studentlog_id=result.optString("studentlog_id");
                guardian_id = result.optString("guardian_id");
                guardian_name = result.optString("guardian_name");
                guardian = result.optString("guardian");
                student_name = result.optString("name");
                phone = result.optString("phone");
                id_number = result.optString("id_number");
                email_id = result.optString("email_id");
                type = result.optString("type");
                photo = result.optString("photo");
                is_capture_image = result.optBoolean("is_capture_image", true);
                travel_id = result.optInt("travel_id");
                student_id = result.optInt("student_id");
                message = result.optString("message");
                is_active = result.optInt("is_active");
                exp_dt = result.optString("exp_dt");
                System.out.println("time_in123 "+is_capture_image+ " "+ "check_in_image "
                        +exp_dt);
                int vehicle_is_active =0;
                int supervisor_is_active =0;

                // on status ok insert the details into student table
                if(result.optString("status").equals("OK")) {

                    count = db.getContactsCount(getHex(id),travel_id);
                    if (count == 0) {
                        db.putStudentInformation(db, student_id, student_name, id_number, phone,
                                email_id, photo, travel_id,is_active,exp_dt);
                    }


                    JSONArray location_row = result.optJSONArray("location_row");
                    int loc_nrows = (int) ((JSONObject) location_row.get(0)).get("NROWS");
                    System.out.println("looocationnrowscle_nrows "+location_row.toString());
                    if(loc_nrows>0) {
                        for (int i = 0; i < location_row.length(); i++) {
                            JSONObject obj3 = (JSONObject) location_row.get(i);
                            System.out.println("location_row123654" + obj3);
                            String location_id = obj3.get("location_id").toString();
                            String location_name = obj3.get("location_name").toString();
                            String loc_created_dt = obj3.get("created_dt").toString();
                            int travel_id = obj3.getInt("travel_id");
                            TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                            System.out.println("locatiorownaaaame is" + location_id + travel_id);
                            count = db.getLocationCount(telephonyManager.getDeviceId(),travel_id);
                            if (count == 0) {
                                db.putLocationInfo(db, location_id,location_name, loc_created_dt, travel_id);
                            }
                        }
                    }

                    JSONArray group_row = result.optJSONArray("group_row");
                    int group_nrows = (int) ((JSONObject) group_row.get(0)).get("NROWS");
                    System.out.println("groupnrowscle_nrows "+group_nrows);
                    if(group_nrows>0) {
                        for (int i = 0; i < group_row.length(); i++) {
                            JSONObject obj3 = (JSONObject) group_row.get(i);
                            System.out.println("location_row123654" + obj3);
                            String group_id = obj3.get("group_id").toString();
                            String group_name = obj3.get("group_name").toString();
                            String is_active = obj3.get("is_active").toString();
                            String group_created_dt = obj3.get("created_dt").toString();
                            int travel_id = obj3.getInt("travel_id");

                            System.out.println("group_rownaaaame is" + group_id + travel_id);
                            count = db.getGroupCount(group_id,travel_id);
                            if (count == 0) {
                                db.putGroupInfo(db, group_id,group_name,is_active, group_created_dt, travel_id);
                            }
                        }
                    }

                    //Inserting into NFCTag table
                    JSONArray nfc_tag_row = result.optJSONArray("nfc_tag_row");
                    int nfc_nrows = (int) ((JSONObject) nfc_tag_row.get(0)).get("NROWS");
                    System.out.println("nfc_tag_rowehicle_nrows "+nfc_nrows);
                    if(nfc_nrows>0) {
                        for (int i = 0; i < nfc_tag_row.length(); i++) {
                            JSONObject obj2 = (JSONObject) nfc_tag_row.get(i);
                            System.out.println("nfc_tag_row123654" + obj2);
                            String nfc_tag_id = obj2.get("nfc_tag_id").toString();
                            nfc_tag_id.replaceAll("\\s+", "");
                            String id_number = obj2.get("id_number").toString();
                            String type = obj2.get("type").toString();
                            int travel_id = obj2.getInt("travel_id");

                            System.out.println("nfctag_rownaaaame is" + nfc_tag_id + id_number +
                                    type + travel_id);
                            count = db.getNFCTagCount(nfc_tag_id, travel_id);
                            if (count == 0) {
                                db.putNFCTagInfo(db, nfc_tag_id, id_number, type, travel_id);
                            }
                        }
                    }

                   //Inserting into Supervisor table
                    JSONArray supervisor_row = result.optJSONArray("supervisor_row");
                    int sup_nrows = (int) ((JSONObject) supervisor_row.get(0)).get("NROWS");
                    System.out.println("supervisor_rowvehicle_nrows "+sup_nrows);
                    if(sup_nrows>0) {
                        for (int i = 0; i < supervisor_row.length(); i++) {
                            JSONObject obj2 = (JSONObject) supervisor_row.get(i);
                            System.out.println("supervisor_row123654" + obj2);
                            int supervisor_id = obj2.getInt("supervisor_id");
                            String imei = obj2.get("imei").toString();
                            int travel_id = obj2.getInt("travel_id");
                            supervisor_is_active = obj2.getInt("is_active");

                            System.out.println("supervisor_rownaaaame is" + supervisor_id + imei +
                                    travel_id);
                            TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                            count = db.getSupervisorCount(telephonyManager.getDeviceId(), travel_id);
                            if (count == 0) {
                                db.putSupervisorInfo(db, supervisor_id, imei, travel_id, is_active);
                            }
                        }
                    }

                    //Inserting into tSetting
                    JSONArray setting_row = result.optJSONArray("setting_row");
                    int setting_nrows = (int) ((JSONObject) setting_row.get(0)).get("NROWS");
                    System.out.println("setting_rowvehicle_nrows "+setting_nrows);
                    if(setting_nrows>0) {
                        for (int i = 0; i < setting_row.length(); i++) {
                            JSONObject obj2 = (JSONObject) setting_row.get(i);
                            System.out.println("setting_row_row123654" + obj2);
                            int setting_id = obj2.getInt("setting_id");
                            String name = obj2.get("name").toString();
                            String value = obj2.get("value").toString();
                            int travel_id = obj2.getInt("travel_id");

                            System.out.println("setting_rownaaaame is" + setting_id + name +
                                    travel_id);
                            count = db.getSettingCount(setting_id, travel_id);
                            if (count == 0) {
                                db.putSettingInfo(db, setting_id, name, value, travel_id);
                            }
                        }
                    }

                    JSONArray imei_location_row = result.optJSONArray("imei_location_row");
                    int imeiloc_nrows = (int) ((JSONObject) location_row.get(0)).get("NROWS");
                    System.out.println("loc_nrowscle_nrows "+imei_location_row.toString());
                    if(imeiloc_nrows>0) {
                        for (int i = 0; i < imei_location_row.length(); i++) {
                            JSONObject obj2 = (JSONObject) imei_location_row.get(i);
                            System.out.println("location_row123654" + obj2);
                            String location_id = obj2.get("location_id").toString();
                            String imei = obj2.get("imei").toString();
                            int travel_id = obj2.getInt("travel_id");
                            TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                            System.out.println("location_rownaaaame is" + location_id + travel_id);
                            count = db.getImeiLocationCount(telephonyManager.getDeviceId(),travel_id);
                            if (count == 0) {
                                db.putImeiLocationInfo(db, location_id, imei, travel_id);
                            }
                        }
                    }

                    JSONArray location_group_row = result.optJSONArray("location_group_row");
                    int locgrp_nrows = (int) ((JSONObject) location_group_row.get(0)).get("NROWS");
                    System.out.println("location_group_rowe_nrows "+locgrp_nrows);
                    if(locgrp_nrows>0) {
                        for (int i = 0; i < location_group_row.length(); i++) {
                            JSONObject obj2 = (JSONObject) location_group_row.get(i);
                            System.out.println("location_group_row123654" + obj2);
                            String stlocgrp_id = obj2.get("stlocgrp_id").toString();
                            String location_id = obj2.get("location_id").toString();
                            String group_id = obj2.get("group_id").toString();
                            int travel_id = obj2.getInt("travel_id");
                            System.out.println("location_group_rownaaaame is" + location_id + travel_id);
                            count = db.getStLocGroupCount(stlocgrp_id,travel_id);
                            if (count == 0) {
                                db.putStLocGroupInfo(db, stlocgrp_id, location_id, group_id, travel_id);
                            }
                        }
                    }

                    JSONArray student_group_row = result.optJSONArray("student_group_row");
                    int stdgrp_nrows = (int) ((JSONObject) student_group_row.get(0)).get("NROWS");
                    System.out.println("student_group_rowle_nrows "+stdgrp_nrows);
                    if(stdgrp_nrows>0) {
                        for (int i = 0; i < student_group_row.length(); i++) {
                            JSONObject obj2 = (JSONObject) student_group_row.get(i);
                            System.out.println("student_group_row123654" + obj2);
                            String stgrp_id = obj2.get("stgrp_id").toString();
                            String student_id = obj2.get("student_id").toString();
                            String group_id = obj2.get("group_id").toString();
                            int travel_id = obj2.getInt("travel_id");
                            System.out.println("student_group_rownaaaame is" + student_id + travel_id);
                            count = db.getStudentGroupCount(stgrp_id,travel_id);
                            if (count == 0) {
                                db.putStudentGroupInfo(db, stgrp_id, student_id, group_id, travel_id);
                            }
                        }
                    }

                 }
                saveToSqlite();
                if( supervisor_is_active !=0 ) {
                    student_details(result);
                } else {
                    setContentView(R.layout.invalidtag);
                    TextView textView = (TextView) findViewById(R.id.invalid_tag);
                    Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");
                    textView.setTypeface(typeface);
                    textView.setBackgroundColor(Color.parseColor("#F05032"));
                    textView.setTextColor(Color.WHITE);
                    textView.setText("INVALID");
                }

                // define constant travel id

            } catch (Exception e) {
                String error = e.getMessage();

            }
        }
    }

    //########################################################\\
    //#  FUNCTION vehicle_display_details(JSONObject jsonObject) TO SHOW DATA   #\\
    //########################################################\\

    void student_details(JSONObject result) {
        setContentView(R.layout.activity_main);
        TableLayout table = (TableLayout) findViewById(R.id.student_table_content);
        TableRow row = (TableRow) findViewById(R.id.guardian_row);

        Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");

        TextView textView9 = (TextView) findViewById(R.id.text3);
        textView9.setTypeface(typeface);
        TextView textView10 = (TextView) findViewById(R.id.text4);
        textView10.setTypeface(typeface);
        TextView textView11 = (TextView) findViewById(R.id.text5);
        textView11.setTypeface(typeface);
        TextView textView12 = (TextView) findViewById(R.id.text6);
        textView12.setTypeface(typeface);
        //   TextView textView13 = (TextView) findViewById(R.id.text7);
        //   textView13.setTypeface(typeface);


        TextView txtview2 = (TextView) findViewById(R.id.student_name);
        txtview2.setText(student_name);
        txtview2.setTypeface(typeface);

        TextView txtview3 = (TextView) findViewById(R.id.id_number);
        txtview3.setText(id_number);
        txtview3.setTypeface(typeface);


        TextView txtview4 = (TextView) findViewById(R.id.phone_no);
        txtview4.setText(phone);
        txtview4.setTypeface(typeface);

        TextView txtview5 = (TextView) findViewById(R.id.email_id);
        txtview5.setText(email_id);
        txtview5.setTypeface(typeface);
        if (!guardian_name.equals("") && !guardian_name.equals("null")) {

            TextView textView7 = (TextView) findViewById(R.id.text7);
            textView7.setText("GUARDIAN");
            textView7.setTypeface(typeface);

            TextView txtview6 = (TextView) findViewById(R.id.guardian_name);
            txtview6.setText(guardian_name);
            txtview6.setTypeface(typeface);

            //  new DownloadImageTask((ImageView) findViewById(R.id.guardian_photo))
            //        .execute(DOMAIN+"media/localhost/images/guardian/" + result.optString("guardian_photo"));

        } else {
            table.removeView(row);

        }

        TextView txtview = (TextView) findViewById(R.id.check);
        txtview.setText(result.optString("message"));
        txtview.setTypeface(typeface);

        // check in sd card if the student image exists. if exists fetch the student image from sd
        // card else download the image from server
        File f = new File(Environment.getExternalStorageDirectory() + "/" + DOMAIN + "/" + result.optString("photo"));
        System.out.println("Environment"+f.getAbsolutePath());
        if (!f.exists()) {
            new DownloadImageTask((ImageView) findViewById(R.id.photo))
                    .execute(BASEURL + MEDIA + result.optString("photo"));
        } else {
            ImageView mImgView1 = (ImageView) findViewById(R.id.photo);
            Bitmap bmp = BitmapFactory.decodeFile(f.getAbsolutePath());
            System.out.println("BitmapFactory"+bmp);
            mImgView1.setImageBitmap(bmp);
        }

        // capture image after every 1 min of check in and check out
        if (result.optString("success").equals("yes")) {
            CountDownTimer c = new CountDownTimer(5000, 1000) {
                public void onFinish() {
                    if (is_capture_image) {
                        captureImage();
                        Log.d("captuphotoreImage","from server");
                    }
                }

                public void onTick(long millisUntilFinished) {
                }
            }.start();
        }

        //update tstudentlog with captured image
        File sourceFile = new File(filePath);
        String filename = sourceFile.getName();
        Log.d("uploadFileToSqlite", filename);
    }

    //Function to save the student profile photos into sd card
    public boolean saveToSDCard(Bitmap bitmap) {
        OutputStream output;
        File file=null;

        File filepath = Environment.getExternalStorageDirectory();

        File dir = new File(filepath.getAbsolutePath()
                + "/" + DOMAIN + "/");
        dir.mkdirs();

        System.out.println("selectPhotofromlocal " + photo);

        file = new File(dir, photo);

        try {

            output = new FileOutputStream(file);

            bitmap.compress(Bitmap.CompressFormat.PNG, 100, output);
            output.flush();
            output.close();
        } catch (Exception e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        return false;
    }

    // Function to download the student profile from server
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
            saveToSDCard(result);
        }
    }

    //Function to get latitude of current location
    public void latitudeLongitude() {
        appLocationService = new AppLocationService(
                MainActivity.this);

        location = appLocationService
                .getLocation(LocationManager.NETWORK_PROVIDER);
        try {
            if (!location.equals(null)) {

                latitude = location.getLatitude();
                longitude = location.getLongitude();
                locationAddress = new LocationAddress();
                locationAddress.getAddressFromLocation(latitude, longitude,
                        getApplicationContext(), new GeocoderHandler());
            } else {
                showSettingsAlert();
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void showSettingsAlert() {
        AlertDialog.Builder alertDialog = new AlertDialog.Builder(
                MainActivity.this);
        alertDialog.setTitle("SETTINGS");
        alertDialog.setMessage("Enable Location Provider! Go to settings menu?");
        alertDialog.setPositiveButton("Settings",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        Intent intent = new Intent(
                                Settings.ACTION_LOCATION_SOURCE_SETTINGS);
                        MainActivity.this.startActivity(intent);
                    }
                });
        alertDialog.setNegativeButton("Cancel",
                new DialogInterface.OnClickListener() {
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
        alertDialog.show();
    }

    private class GeocoderHandler extends Handler {

        public String address;

        public void handleMessage(Message message) {
            switch (message.what) {
                case 1:
                    Bundle bundle = message.getData();
                    address = bundle.getString("address");
                    break;
                default:
                    address = null;
            }
        }
    }

    public byte[] id;

    //Function to sent the url to server after getting the tag id
    private String dumpTagData(Parcelable p) {

        if (isInternetPresent) {
            latitudeLongitude();
        }

        StringBuilder sb = new StringBuilder();
        StringBuilder sb1 = new StringBuilder();
        Geocoder code = new Geocoder(MainActivity.this);

        try {
            List<Address> addresses = code.getFromLocation(latitude, longitude, 1);
            Address add = new Address(Locale.getDefault());
            if (addresses.size() > 0) {
                add = addresses.get(0);
                for (int i = 0; i < add.getMaxAddressLineIndex(); i++) {
                    sb1 = sb1.append(add.getAddressLine(i)).append(", ");
                }
                res = sb1.toString();
            }
        } catch (Exception ex) {
            Log.d("Error", ex.getMessage());
        }

        Tag tag = (Tag) p;
        id = tag.getId();
        count = db.getContactsCount(getHex(id),1);
        System.out.println("getContactsCount123 "+count);
        VehicleJson vehicleJson = new VehicleJson();
        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

        cd = new ConnectionDetector(getApplicationContext());

        // if internet connection is available and the student row does not exist in tstudent
        // in sqlite then the student details is fetched from server else student details is fetched
        // from sqkite
        isInternetPresent = cd.isConnectingToInternet();
        if (count == 0) {
            // check for Internet status
            if (isInternetPresent) {
                String resp =URLEncoder.encode(res);
                String result=resp.replaceAll("%2F","%2D");

                String url = BASEURL + "app/iisc/" + getHex(id) + "/" +
                        telephonyManager.getDeviceId()  ;
                vehicleJson.execute(url);
                Toast.makeText(getApplicationContext(),"from server",Toast.LENGTH_LONG).show();
                System.out.println("BASEURL12345 "+url);

            } else {
                db.putAttendanceLogInfo(db,telephonyManager.getDeviceId(),getHex(id),"",0,latitude,
                        longitude,res,"nfc tag "+getHex(id)+" not found in sqlite and there was no " +
                                "internet connection to fetch the details from server",
                        attendance_dateFormat.format(d),1);
                showAlertDialog(MainActivity.this, "No Internet Connection",
                        "You don't have internet connection.", false);
            }
        } else
            displayStudentDetails(getHex(id));
        return sb.toString();
    }

    //Function to fetch the details from sqlite
    public void displayStudentDetails(String nfc_tag_id) {

        Date date = new Date();
        DateFormat dateFormat1 = new SimpleDateFormat("yyyy-MM-dd");
        DateFormat msgdateFormat1 = new SimpleDateFormat("yyyy-MM-dd HH:mm a ");
        System.out.println("msgdateFormat1" + msgdateFormat1.format(d));
        Boolean setting_is_capture_image = false;
        List<DataBase> contacts = db.getAllContacts(nfc_tag_id, 1);
        for (DataBase cn : contacts) {
            TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                    TELEPHONY_SERVICE);
            List<DataBase> supervisor = db.getSupervisor(telephonyManager.getDeviceId(),
                    cn.getTravelId());
            System.out.println("getSupervisort53432" + supervisor.toString());

            //If supervisor is not active then show invalid
            for (DataBase s : supervisor) {
                if (db.getSupervisorCount(telephonyManager.getDeviceId(), cn.getTravelId())
                        != 0 && s.getSupervisorIsActive() == 1) {
                    // Check if the vehicle exists or not for that supervisor depending on imei
                    System.out.println("qwewrertret " +
                            s.getSupervisorIsActive()  +
                            db.getSupervisorCount(telephonyManager.getDeviceId(),
                                    cn.getTravelId()));
                    String exp_dt = cn.getExpDate();
                    String current_dt = dateFormat1.format(date);
                    int result = exp_dt.compareTo(current_dt);

                    //Check if the student is active and the expiry date is less than current date
                    System.out.println("mnbvcxzaasd " + result + " is_active "+cn.getStudentIsActive());
                    String locationId;
                    int g_id = 0;
                    if (cn.getStudentIsActive() != 0 && result > 0) {
                        List<DataBase> imeiLocationList = db.getAllImeiLocationCount(telephonyManager.getDeviceId(), cn.getTravelId());
                        if (imeiLocationList.size() != 0) {
                            for (DataBase imei_location_id : imeiLocationList) {
                                locationId = imei_location_id.getImeiLocationId();
                                List<DataBase> groupList = db.getAllStLocGroup(locationId, cn.getTravelId());
                                System.out.println("getGroupId " + groupList.size());

                                if (groupList.size() != 0) {

                                    for (DataBase group_id : groupList) {
                                        g_id = group_id.getGroupId();
                                        //System.out.println("getGroupId "+ g_id );

                                        List<DataBase> student_group = db.getAllStudentGroup(cn.getStudentId(), cn.getTravelId());
                                        if (student_group.size() != 0) {

                                            for (DataBase group : student_group) {
                                                int student_group_id = group.getStudentGroupId();
                                                System.out.println("student_group_id " + group.getStudentGroupName() + " g_id " + group_id.getGroupName());
                                                if (g_id == student_group_id) {
                                                    setContentView(R.layout.activity_main);
                                                    int travel_id = 0;

                                                    //Display Details of student and insert into tstudentlog table
                                                    TableLayout table = (TableLayout) findViewById
                                                            (R.id.student_table_content);
                                                    TableRow row = (TableRow) findViewById(R.id.guardian_row);

                                                    Typeface typeface = Typeface.createFromAsset(getAssets(),
                                                            "fonts/trajan-pro-regular.ttf");

                                                    TextView textView9 = (TextView) findViewById(R.id.text3);
                                                    textView9.setTypeface(typeface);
                                                    TextView textView10 = (TextView) findViewById(R.id.text4);
                                                    textView10.setTypeface(typeface);
                                                    TextView textView11 = (TextView) findViewById(R.id.text5);
                                                    textView11.setTypeface(typeface);
                                                    TextView textView12 = (TextView) findViewById(R.id.text6);
                                                    textView12.setTypeface(typeface);
                                                    Log.d("Reading: ", "Reading all contacts..");
                                                    int sqlite_is_capture_image = 0;
                                                    String log = "Id: " + cn.getStudentName() + " ,Name: " +
                                                            cn.getIdNumber() + " ,Phone: " + cn.getPhoneNumber();

                                                    // Writing Contacts to log
                                                    Log.d("Name123: ", log);
                                                    String name = cn.getStudentName();
                                                    String id_num = cn.getIdNumber();
                                                    String ph = cn.getPhoneNumber();
                                                    String em = cn.getEmail();
                                                    String img = cn.getImage();
                                                    travel_id = cn.getTravelId();
                                                    System.out.println("qasderfrtg---" + img);
                                                    sqlite_is_capture_image = cn.getIsCaptureImage();
                                                    Log.d("Booleanis_capture_image", Float.toString
                                                            (sqlite_is_capture_image));

                                                    TextView txtview2 = (TextView) findViewById
                                                            (R.id.student_name);
                                                    txtview2.setText(name);
                                                    txtview2.setTypeface(typeface);

                                                    TextView txtview3 = (TextView) findViewById(R.id.id_number);
                                                    txtview3.setText(id_num);
                                                    txtview3.setTypeface(typeface);

                                                    TextView txtview4 = (TextView) findViewById(R.id.phone_no);
                                                    txtview4.setText(ph);
                                                    txtview4.setTypeface(typeface);

                                                    TextView txtview5 = (TextView) findViewById(R.id.email_id);
                                                    txtview5.setText(em);
                                                    txtview5.setTypeface(typeface);
                                                    TextView txtview = (TextView) findViewById(R.id.check);
                                                    message = "Check In - " + imei_location_id.getlocationName() + " AT " + msgdateFormat1.format(d);
                                                    txtview.setText(message);
                                                    txtview.setTypeface(typeface);
                                                    table.removeView(row);

                                                    //fetch the student profile photo from sd card
                                                    File f = new File(Environment.getExternalStorageDirectory()
                                                            + "/" + DOMAIN + "/" + img);
                                                    System.out.println("table.removeView(row);" +
                                                            f.getAbsolutePath());
                                                    if (!f.exists()) {
                                                        Toast.makeText(getApplication(), "No image found in sd card",
                                                                Toast.LENGTH_LONG).show();
                                                    } else {
                                                        ImageView mImgView1 = (ImageView) findViewById
                                                                (R.id.photo);
                                                        Bitmap bmp = BitmapFactory.decodeFile(f.getAbsolutePath());
                                                        mImgView1.setImageBitmap(bmp);
                                                    }

                                                    // Inserting into tStudentLog table
                                                    DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd " +
                                                            "HH:mm:ss");
                                                    DateFormat dateFormat2 = new SimpleDateFormat("HH:mm:ss");
                                                    int log_count = db.getIISCCount(cn.getStudentId());

                                                    if (log_count == 0) {
                                                        db.putIISCLogInfo(db, cn.getStudentId(), imei_location_id.getlocationName(), cn.getTravelId(), dateFormat.format(date));
                                                    } else {
                                                        List<DataBase> iisc_log = db.getiiscLog(cn.getStudentId());
                                                        System.out.println("iisc_log " + iisc_log);
                                                        for (DataBase slog : iisc_log) {
                                                            String d1 = slog.getCreatedDate();
                                                            String d2 = dateFormat.format(date);
                                                            try {
                                                                Date from_time = dateFormat.parse(d1);
                                                                Date to_time = dateFormat.parse(d2);
                                                                Log.d("from_time", from_time.toString());
                                                                Log.d("to_time", d2);
                                                                long elapsed_time = Math.abs((to_time.getTime()
                                                                        - from_time.getTime())) / 60;
                                                                int minutes = (int) TimeUnit.MILLISECONDS.
                                                                        toSeconds(elapsed_time);
                                                                Log.d("elapsed_time", Float.toString(minutes));
                                                                if (minutes <= 2) {
                                                                } else {

                                                                    db.putIISCLogInfo(db, cn.getStudentId(), imei_location_id.getlocationName(), cn.getTravelId(), dateFormat.format(date));

                                                                    //Delete the row from tStudentLog from sqlite after 5min of checkout
                                                                    CountDownTimer c = new CountDownTimer(300000, 2000) {
                                                                        public void onFinish() {

                                                                            db.deleteLog();
                                                                            Toast.makeText(getApplicationContext(),
                                                                                    "Log Deleted", Toast.LENGTH_LONG).show();
                                                                        }

                                                                        public void onTick(long millisUntilFinished) {
                                                                        }
                                                                    }.start();
                                                                }
                                                            } catch (Exception e) {
                                                                e.printStackTrace();

                                                            }

                                                        }
                                                    }
                                                    //Select value for image capture range
                                                    int attendance_image_capture_range = 0;
                                                    String value = null;
                                                    int no_image_count = 0;
                                                    String image = "no_image.jpg";
                                                    List<DataBase> setting = db.getSetting(cn.getTravelId());
                                                    for (DataBase setng : setting) {
                                                        value = setng.getValue();
                                                        System.out.println("valueee123 is " + value);

                                                        switch (value) {
                                                            case "always":
                                                                setting_is_capture_image = true;
                                                                break;
                                                            case "frequently":
                                                                attendance_image_capture_range = 2;
                                                                break;
                                                            case "sometimes":
                                                                attendance_image_capture_range = 4;
                                                                break;
                                                            case "rarely":
                                                                attendance_image_capture_range = 8;
                                                                break;
                                                        }

                                                        if (!value.equals("always") && !value.equals("never")) {
                                                            List<DataBase> setting1 = db.getSettingSelect
                                                                    (cn.getStudentId(), attendance_image_capture_range,
                                                                            cn.getTravelId());
                                                            for (DataBase setng1 : setting1) {
                                                                image = setng1.getSettingCheckInImage();
                                                                System.out.println("value.equals(\"always\")" + image);
                                                                if (image.equals("no_image.jpg") || image.equals("")) {
                                                                    no_image_count++;
                                                                }
                                                            }
                                                            // 1. when new student comes for first time no of rows will be 0 so image must be captured
                                                            // 2. if image capture range is equal to no of rows with no images then image must be captured
                                                            if (db.getLogCount(cn.getStudentId(),
                                                                    cn.getTravelId()) == 0 ||
                                                                    no_image_count == attendance_image_capture_range) {
                                                                setting_is_capture_image = true;
                                                            }

                                                        }
                                                    }
                                                    //capture image after check in and check out if image capture is enabled
                                                    final Boolean finalSetting_is_capture_image = setting_is_capture_image;
                                                    CountDownTimer c = new CountDownTimer(5000, 2000) {
                                                        public void onFinish() {
                                                            if (finalSetting_is_capture_image) {
                                                                captureImage();
                                                                is_capture_image = false;
                                                                type = "null";
                                                                Log.d("captureImage", "from local");
                                                                image_capture = "local";
                                                            }
                                                        }

                                                        public void onTick(long millisUntilFinished) {
                                                        }
                                                    }.start();

                                                    //sync to server for every 10sec after check-in if internet is connected
                                                    final int finalTravel_id = travel_id;
                                                    CountDownTimer c1 = new CountDownTimer(2000, 1000) {
                                                        public void onFinish() {
                                                            cd = new ConnectionDetector(getApplicationContext());
                                                            isInternetPresent = cd.isConnectingToInternet();
                                                            // check for Internet status and set alarm to sync the data from from sqlite to
                                                            // server database for every 1 min
                                                            if (isInternetPresent) {
                                                                Intent alarmIntent = new Intent(MainActivity.this,
                                                                        AlarmReceiver.class);
                                                                pendingIntent = PendingIntent.getBroadcast
                                                                        (MainActivity.this, 0, alarmIntent, 0);
                                                                alarmIntent.putExtra("travel_id", finalTravel_id);
                                                                System.out.println("finalTravel_id" +
                                                                        finalTravel_id);
                                                                sendBroadcast(alarmIntent);
                                                                setAlarm();
                                                            }
                                                            if (isInternetPresent) {
                                                                Intent alarmIntent = new Intent(MainActivity.this,
                                                                        AttendanceLogSync.class);
                                                                pendingIntentAtt = PendingIntent.getBroadcast
                                                                        (MainActivity.this, 0, alarmIntent, 0);
                                                                sendBroadcast(alarmIntent);
                                                                setAttendanceLogAlarm();
                                                            }
                                                        }

                                                        public void onTick(long millisUntilFinished) {
                                                        }

                                                    }.start();
                                                } else {
                                                    setContentView(R.layout.invalidtag);
                                                    TextView textView = (TextView) findViewById(R.id.invalid_tag);
                                                    Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-" +
                                                            "pro-regular.ttf");
                                                    textView.setTypeface(typeface);
                                                    textView.setBackgroundColor(Color.parseColor("#F05032"));
                                                    textView.setTextColor(Color.WHITE);
                                                    textView.setText("INVALID");
                                                    System.out.println("STUDENT_GROUP_NAME " + group_id.getStudentGroupName() + " IMEI_GROUP_NAME " + group_id.getGroupName());
                                                    db.putAttendanceLogInfo(db, telephonyManager.getDeviceId(),
                                                            nfc_tag_id, cn.getIdNumber(), cn.getStudentId(),
                                                            latitude, longitude, res, "The group " +
                                                                    "assigned to student is "+group.getStudentGroupName()+" and the group fetched" +
                                                                    " based on imei is "+group_id.getGroupName()+ " did not match",
                                                            attendance_dateFormat.format(d), cn.getTravelId());
                                                }
                                            }
                                        } else {
                                            setContentView(R.layout.invalidtag);
                                            TextView textView = (TextView) findViewById(R.id.invalid_tag);
                                            Typeface typeface = Typeface.createFromAsset(getAssets(),
                                                    "fonts/trajan-pro-regular.ttf");
                                            textView.setTypeface(typeface);
                                            textView.setBackgroundColor(Color.parseColor("#F05032"));
                                            textView.setTextColor(Color.WHITE);
                                            textView.setText("INVALID");
                                            db.putAttendanceLogInfo(db, telephonyManager.getDeviceId(),
                                                    nfc_tag_id, cn.getIdNumber(), cn.getStudentId(),
                                                    latitude, longitude, res, "The student is not " +
                                                            "assigned to any group",
                                                    attendance_dateFormat.format(d), cn.getTravelId());

                                        }

                                    }
                                } else {
                                    setContentView(R.layout.invalidtag);
                                    TextView textView = (TextView) findViewById(R.id.invalid_tag);
                                    Typeface typeface = Typeface.createFromAsset(getAssets(),
                                            "fonts/trajan-pro-regular.ttf");
                                    textView.setTypeface(typeface);
                                    textView.setBackgroundColor(Color.parseColor("#F05032"));
                                    textView.setTextColor(Color.WHITE);
                                    textView.setText("INVALID");
                                    db.putAttendanceLogInfo(db, telephonyManager.getDeviceId(),
                                            nfc_tag_id, cn.getIdNumber(), cn.getStudentId(),
                                            latitude, longitude, res, "The Location " + imei_location_id.getlocationName() + "  is not " +
                                                    "assigned to any group",
                                            attendance_dateFormat.format(d), cn.getTravelId());
                                }
                            }
                        } else {
                            db.putAttendanceLogInfo(db, telephonyManager.getDeviceId(),
                                    nfc_tag_id, cn.getIdNumber(), cn.getStudentId(),
                                    latitude, longitude, res, "The student with imei " +
                                            telephonyManager.getDeviceId() + "is" +
                                            "not assigned to any location",
                                    attendance_dateFormat.format(d), cn.getTravelId());
                            setContentView(R.layout.invalidtag);
                            TextView textView = (TextView) findViewById(R.id.invalid_tag);
                            Typeface typeface = Typeface.createFromAsset(getAssets(),
                                    "fonts/trajan-pro-regular.ttf");
                            textView.setTypeface(typeface);
                            textView.setBackgroundColor(Color.parseColor("#F05032"));
                            textView.setTextColor(Color.WHITE);
                            textView.setText("INVALID");
                        }
                    } else {
                        db.putAttendanceLogInfo(db, telephonyManager.getDeviceId(),
                                nfc_tag_id, cn.getIdNumber(), cn.getStudentId(),
                                latitude, longitude, res, "The student  " +
                                        "is either in-active or expired",
                                attendance_dateFormat.format(d), cn.getTravelId());
                        setContentView(R.layout.invalidtag);
                        TextView textView = (TextView) findViewById(R.id.invalid_tag);
                        Typeface typeface = Typeface.createFromAsset(getAssets(),
                                "fonts/trajan-pro-regular.ttf");
                        textView.setTypeface(typeface);
                        textView.setBackgroundColor(Color.parseColor("#F05032"));
                        textView.setTextColor(Color.WHITE);
                        textView.setText("INVALID");
                    }

                } else {
                    db.putAttendanceLogInfo(db, telephonyManager.getDeviceId(), nfc_tag_id, "",
                            0, latitude, longitude, res, "There is no supervisor with imei " +
                                    telephonyManager.getDeviceId() + " or supervisor is in-active",
                            attendance_dateFormat.format(d), cn.getTravelId());
                    setContentView(R.layout.invalidtag);
                    TextView textView = (TextView) findViewById(R.id.invalid_tag);
                    Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-" +
                            "pro-regular.ttf");
                    textView.setTypeface(typeface);
                    textView.setBackgroundColor(Color.parseColor("#F05032"));
                    textView.setTextColor(Color.WHITE);
                    textView.setText("INVALID");
                }
            }
        }
        saveToSqlite();
    }

    // thread to upload the captured images to server media file
    public void SyncPhotoToServer(final String filepath) {

        Thread t = new Thread(new Runnable() {

            @Override
            public void run() {

                uploadImageToServer(filepath);

            }
        });
        t.start();
    }

    private String getHex(byte[] bytes) {
        StringBuilder sb = new StringBuilder();
        //for (int i = bytes.length - 1; i >= 0; --i) {
        for (int i = 0; i <= bytes.length - 1; i++) {
            int b = bytes[i] & 0xff;
            if (b < 0x10)
                sb.append('0');
            sb.append(Integer.toHexString(b));
            if (i >= 0 && i != bytes.length - 1) {
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
        Date now = new Date();
        List<ParsedNdefRecord> records = NdefMessageParser.parse(msgs[0]);
        final int size = records.size();
        for (int i = 0; i < size; i++) {
            TextView timeView = new TextView(this);
            timeView.setText(TIME_FORMAT.format(now));
            //content.addView(timeView, 0);
            ParsedNdefRecord record = records.get(i);
        }
    }

    public void onNewIntent(Intent intent) {
        setIntent(intent);
        resolveIntent(intent);
    }

    public void onBackPressed() {
        Intent a = new Intent(Intent.ACTION_MAIN);
        a.addCategory(Intent.CATEGORY_HOME);
        a.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(a);

    }

    private void captureImage() {

        Intent intent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);

        fileUri = getOutputMediaFileUri(MEDIA_TYPE_IMAGE);

        intent.putExtra(MediaStore.EXTRA_OUTPUT, fileUri);

        // start the image capture Intent
        startActivityForResult(intent, CAMERA_CAPTURE_IMAGE_REQUEST_CODE);
    }

    public Uri getOutputMediaFileUri(int type) {
        return Uri.fromFile(getOutputMediaFile(type));
    }

    private static File getOutputMediaFile(int type) {

        final String IMAGE_DIRECTORY_NAME = "Android File Upload";
        File mediaStorageDir = new File(
                Environment
                        .getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES),
                IMAGE_DIRECTORY_NAME);

        // Create the storage directory if it does not exist
        if (!mediaStorageDir.exists()) {
            if (!mediaStorageDir.mkdirs()) {
                Log.d(TAG, "Oops! Failed create " +
                        IMAGE_DIRECTORY_NAME + " directory");
                return null;
            }
        }

        // Create a media file name
        String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss",
                Locale.getDefault()).format(new Date());
        File mediaFile;
        if (type == MEDIA_TYPE_IMAGE) {
            mediaFile = new File(mediaStorageDir.getPath() + File.separator
                    + "IMG_" + timeStamp + ".jpg");
        } else {
            return null;
        }

        return mediaFile;
    }

    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);

        // save file url in bundle as it will be null on screen orientation
        // changes
        outState.putParcelable("file_uri", fileUri);
    }

    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);

        // get the file url
        fileUri = savedInstanceState.getParcelable("file_uri");
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        // if the result is capturing Image
        if (requestCode == CAMERA_CAPTURE_IMAGE_REQUEST_CODE) {
            if (resultCode == RESULT_OK) {
                //  Toast.makeText(getApplicationContext(),"image",Toast.LENGTH_LONG);
                // successfully captured the image
                // launching upload activity
                cd = new ConnectionDetector(getApplicationContext());

                isInternetPresent = cd.isConnectingToInternet();
                if(image_capture.equals("local")){
                    uploadFileToSqlite();
                } else {
                    // check for Internet status
                    if (isInternetPresent) {

                        launchUploadActivity();
                        Toast.makeText(getApplicationContext(),
                                "Image Uploaded Successful", Toast.LENGTH_SHORT)
                                .show();
                    }
                }

            } else if (resultCode == RESULT_CANCELED) {
                // user cancelled Image capture
                Toast.makeText(getApplicationContext(),
                        "User cancelled image capture", Toast.LENGTH_SHORT)
                        .show();
            } else {
                // failed to capture image
                Toast.makeText(getApplicationContext(),
                        "Sorry! Failed to capture image", Toast.LENGTH_SHORT)
                        .show();
            }

        } else if (resultCode == RESULT_CANCELED) {

            // user cancelled recording
            Toast.makeText(getApplicationContext(),
                    "User cancelled video recording", Toast.LENGTH_SHORT)
                    .show();

        } else {
            // failed to record video
            Toast.makeText(getApplicationContext(),
                    "Sorry! Failed to record video", Toast.LENGTH_SHORT)
                    .show();
        }
    }

    //To store the captured image into sd card when student details is fetched from server
    private void launchUploadActivity() {
        filePath = fileUri.getPath();
        image_type = "student";
        if (filePath != null) {
            Intent i = new Intent();
            i.putExtra("filePath", fileUri.getPath());
            Toast.makeText(getApplicationContext(), "inside  upload", Toast.LENGTH_LONG);
            upload();
            System.out.println("isInternetPresent");
        } else {
            Toast.makeText(getApplicationContext(),
                    "Sorry no filepath found", Toast.LENGTH_LONG).show();
        }
    }

    //To store the captured image into sd card when student details is fetched from server in background
    public void upload() {

        Thread t = new Thread(new Runnable() {

            @Override
            public void run() {

                uploadFile();


            }
        });
        t.start();
    }

    // to save captured image into sqlite databade and update tStudentLog row
    public void uploadFileToSqlite() {
        filePath = fileUri.getPath();
        System.out.println();
        List<DataBase> contacts = db.getAllContacts(getHex(id),1);
        Log.d("getHex(id)",getHex(id));
        DateFormat dateFormat1 = new SimpleDateFormat("yyyy-MM-dd");
        Date date = new Date();
        for(DataBase cn :contacts) {
            List<DataBase> iisc_log = db.getiiscLog(cn.getStudentId());
            for(DataBase slog :iisc_log) {
                File sourceFile = new File(filePath);
                String filename = sourceFile.getName();
                Log.d("uploadFileToSqlite", filePath);
                    db.updateIISCLog(filename, slog.getiisclog_id(),cn.getTravelId());
                    SyncPhotoToServer(filePath);
            }
        }

        saveToSqlite();
    }

    // to save captured image into server databade and update tStudentLog row
    private String uploadFile() {
        String responseString = null;

        HttpClient httpclient = new DefaultHttpClient();
        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                TELEPHONY_SERVICE);
        System.out.println("CONFIG_URL "+Config.FILE_UPLOAD_URL+telephonyManager.getDeviceId());
        HttpPost httppost = new HttpPost(Config.FILE_UPLOAD_URL+telephonyManager.getDeviceId());

        try {
            AndroidMultiPartEntity entity = new AndroidMultiPartEntity(
                    new AndroidMultiPartEntity.ProgressListener() {

                        @Override
                        public void transferred(long num) {

                        }
                    });

            File sourceFile = new File(filePath);
            Log.d("sourceFile12345",sourceFile.toString());

            // Adding file data to http body
            entity.addPart("image", new FileBody(sourceFile));

            // Extra parameters if you want to pass to server
            entity.addPart("image", new FileBody(sourceFile));
                System.out.println("STUDENT_LOG id" + studentlog_id);

            entity.addPart("studentlog_id", new StringBody(studentlog_id));
            entity.addPart("image_type", new StringBody("student"));
            totalSize = entity.getContentLength();
            httppost.setEntity(entity);

            // Making server call
            HttpResponse response = httpclient.execute(httppost);
            HttpEntity r_entity = response.getEntity();

            int statusCode = response.getStatusLine().getStatusCode();
            if (statusCode == 200) {
                // Server response
                responseString = EntityUtils.toString(r_entity);
            } else {
                responseString = "Error occurred! Http Status Code: "
                        + statusCode;
            }

        } catch (ClientProtocolException e) {
            responseString = e.toString();
        } catch (IOException e) {
            responseString = e.toString();
        }
        return responseString;
    }

    //to get the sqlite database view in phone
    public void saveToSqlite() {
        try {
            File sd = Environment.getExternalStorageDirectory();
            File data = Environment.getDataDirectory();

            if (sd.canWrite()) {

                String currentDBPath = "/data/data/" + getPackageName() + "/databases/iisc_attendance";
                System.out.println("currentDBPath1233"+currentDBPath);
                String backupDBPath = "iisc.sqlite3";
                File currentDB = new File(currentDBPath);
                File backupDB = new File(sd, backupDBPath);

                if (currentDB.exists()) {

                    FileChannel src = new FileInputStream(currentDB).getChannel();
                    FileChannel dst = new FileOutputStream(backupDB).getChannel();
                    dst.transferFrom(src, 0, src.size());
                    src.close();
                    dst.close();
                }
            }
        } catch (Exception e) {

        }
    }

    // to sync the data from server to sqlite
    public void btnSync(View view) {
        Typeface typeface = Typeface.createFromAsset(getAssets(), "fonts/trajan-pro-regular.ttf");
        TextView textView = (TextView) findViewById(R.id.sync);
        textView.setTypeface(typeface);
        AsyncHttpClient client = new AsyncHttpClient();
        // Http Request Params Object
        RequestParams params = new RequestParams();
        // Show ProgressBar
        prgDialog.show();
        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.
                TELEPHONY_SERVICE);

        // Make Http call to getusers.php
        client.post(BASEURL + "app/iisc_sync/"+telephonyManager.getDeviceId(), params, new
                AsyncHttpResponseHandler() {
            @Override
            public void onSuccess(String response) {

                Log.d("btnSyncResponse", response);
                try {
                    updateSQLite(response);
                } catch (IOException e) {
                    e.printStackTrace();
                }

            }
            public void onFailure(int statusCode, Throwable error, String content) {
                // TODO Auto-generated method stub
                // Hide ProgressBar
                prgDialog.hide();
                if (statusCode == 404) {
                    Toast.makeText(getApplicationContext(), "Requested resource not found",
                            Toast.LENGTH_LONG).show();
                } else if (statusCode == 500) {
                    Toast.makeText(getApplicationContext(), "Something went wrong at server end",
                            Toast.LENGTH_LONG).show();
                } else {
                    Toast.makeText(getApplicationContext(), "Unexpected Error occcured! [Most " +
                                    "common Error: Device might not be connected to Internet]",
                            Toast.LENGTH_LONG).show();
                }
            }
        });
    }

    // To save the synced data from server to sqlite
    public void updateSQLite(String response) throws IOException {
        JSONObject obj = null;
        JSONObject obj2 = null;
        try {
            JSONObject arr = new JSONObject(response);
            System.out.println("student_arr12345 " + arr.optString("status"));
            if(arr.optString("status").equals("OK")) {

                // Syncing Setting row from server to sqlite
                JSONArray setting_row = arr.getJSONArray("setting_row");
                int setting_nrows = (int) ((JSONObject) setting_row.get(0)).get("NROWS");
                System.out.println("setting_rowvehicle_nrows "+setting_nrows);
                if(setting_nrows>0) {
                    for (int i = 0; i < setting_row.length(); i++) {
                        JSONObject object = (JSONObject) setting_row.get(i);
                        System.out.println("setting_row_row123654" + object);
                        int setting_id = object.getInt("setting_id");
                        String name = object.get("name").toString();
                        String value = object.get("value").toString();
                        int travel_id = object.getInt("travel_id");

                        System.out.println(i + " setting_rownaaaame is" + setting_id + name +
                                travel_id);
                        count = db.getSettingCount(setting_id, travel_id);
                        if (count == 0) {
                            db.putSettingInfo(db, setting_id, name, value, travel_id);
                        }
                    }
                }

                //Syncing Supervisor row from server to sqlite
                JSONArray supervisor_row = arr.getJSONArray("supervisor_row");
                int sup_nrows = (int) ((JSONObject) supervisor_row.get(0)).get("NROWS");
                System.out.println("supervisor_rowvehicle_nrows "+sup_nrows);
                if(sup_nrows>0) {
                for (int i = 0; i < supervisor_row.length(); i++) {
                    JSONObject object = (JSONObject) supervisor_row.get(i);
                    System.out.println("supervisor_row123654" + object);
                        int supervisor_id = object.getInt("supervisor_id");
                        int is_active = object.getInt("is_active");
                        String imei = object.get("imei").toString();
                        int travel_id = object.getInt("travel_id");

                        System.out.println(i + " supervisor_rownaaaame is" + supervisor_id + imei +
                                travel_id);
                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                        count = db.getSupervisorCount(telephonyManager.getDeviceId(), travel_id);
                        count = db.getSupervisorCount(telephonyManager.getDeviceId(), travel_id);
                        if (count == 0) {
                            db.putSupervisorInfo(db, supervisor_id, imei, travel_id, is_active);
                        }
                    }

                }

                //Syncing NFCTag row from server to sqlite
                JSONArray nfc_tag_row = arr.getJSONArray("nfc_tag_row");
                int nfc_nrows = (int) ((JSONObject) nfc_tag_row.get(0)).get("NROWS");
                System.out.println("nfc_tag_rowehicle_nrows "+nfc_nrows);
                if(nfc_nrows>0) {
                    for (int i = 0; i < nfc_tag_row.length(); i++) {
                        JSONObject object = (JSONObject) nfc_tag_row.get(i);
                        System.out.println("nfc_tag_row123654" + object);
                        String nfc_tag_id = object.get("nfc_tag_id").toString();
                        String id_number = object.get("id_number").toString();
                        String type = object.get("type").toString();
                        int travel_id = object.getInt("travel_id");

                        System.out.println("nfctag_rownaaaame is" + nfc_tag_id + id_number +
                                type + travel_id);
                        count = db.getNFCTagCount(nfc_tag_id, travel_id);
                        if (count == 0) {
                            db.putNFCTagInfo(db, nfc_tag_id, id_number, type, travel_id);
                        }
                    }
                }

                JSONArray imei_location_row = arr.optJSONArray("imei_location_row");
                int imei_loc_nrows = (int) ((JSONObject) imei_location_row.get(0)).get("NROWS");
                System.out.println("locimei_nrowscle_nrows "+imei_loc_nrows);
                if(imei_loc_nrows>0) {
                    for (int i = 0; i < imei_location_row.length(); i++) {
                    JSONObject obj3 = (JSONObject) imei_location_row.get(i);
                    System.out.println("location_row123654" + obj3);
                        String location_id = obj3.get("location_id").toString();
                        String imei = obj3.get("imei").toString();
                        int travel_id = obj3.getInt("travel_id");
                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                        System.out.println("locationimei_rownaaaame is" + location_id + travel_id);
                        count = db.getImeiLocationCount(telephonyManager.getDeviceId(),travel_id);
                        if (count == 0) {
                            db.putImeiLocationInfo(db, location_id, imei, travel_id);
                        }
                    }
                }

                JSONArray location_row = arr.optJSONArray("location_row");
                int loc_nrows = (int) ((JSONObject) location_row.get(0)).get("NROWS");
                System.out.println("looocationnrowscle_nrows "+location_row.toString());
                if(loc_nrows>0) {
                    for (int i = 0; i < location_row.length(); i++) {
                        JSONObject obj3 = (JSONObject) location_row.get(i);
                        System.out.println("location_row123654" + obj3);
                        String location_id = obj3.get("location_id").toString();
                        String location_name = obj3.get("location_name").toString();
                        String created_dt = obj3.get("created_dt").toString();
                        int travel_id = obj3.getInt("travel_id");
                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                        System.out.println("locatiorownaaaame is" + location_id + travel_id);
                        count = db.getLocationCount(telephonyManager.getDeviceId(),travel_id);
                        if (count == 0) {
                            db.putLocationInfo(db, location_id,location_name, created_dt, travel_id);
                        }
                    }
                }

                JSONArray group_row = arr.optJSONArray("group_row");
                int group_nrows = (int) ((JSONObject) group_row.get(0)).get("NROWS");
                System.out.println("groupnrowscle_nrows "+group_nrows);
                if(group_nrows>0) {
                    for (int i = 0; i < group_row.length(); i++) {
                        JSONObject obj3 = (JSONObject) group_row.get(i);
                        System.out.println("location_row123654" + obj3);
                        String group_id = obj3.get("group_id").toString();
                        String group_name = obj3.get("group_name").toString();
                        String is_active = obj3.get("is_active").toString();
                        String created_dt = obj3.get("created_dt").toString();
                        int travel_id = obj3.getInt("travel_id");
                        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);

                        System.out.println("group_rownaaaame is" + group_id + travel_id);
                        count = db.getGroupCount(telephonyManager.getDeviceId(),travel_id);
                        if (count == 0) {
                            db.putGroupInfo(db, group_id,group_name,is_active, created_dt, travel_id);
                        }
                    }
                }

                JSONArray location_group_row = arr.optJSONArray("location_group_row");
                int locgrp_nrows = (int) ((JSONObject) location_group_row.get(0)).get("NROWS");
                System.out.println("location_group_rowe_nrows "+locgrp_nrows);
                if(locgrp_nrows>0) {
                for (int i = 0; i < location_group_row.length(); i++) {
                    JSONObject obj3 = (JSONObject) location_group_row.get(i);
                    System.out.println("location_group_row123654" + obj3);
                        String stlocgrp_id = obj3.get("stlocgrp_id").toString();
                        String location_id = obj3.get("location_id").toString();
                        String group_id = obj3.get("group_id").toString();
                        int travel_id = obj3.getInt("travel_id");
                        System.out.println("location_group_rownaaaame is" + location_id + travel_id);
                        count = db.getStLocGroupCount(location_id,travel_id);
                        if (count == 0) {
                            db.putStLocGroupInfo(db, stlocgrp_id, location_id, group_id, travel_id);
                        }
                    }
                }

                JSONArray student_group_row = arr.optJSONArray("student_group_row");
                int stdgrp_nrows = (int) ((JSONObject) student_group_row.get(0)).get("NROWS");
                System.out.println("student_group_rowle_nrows "+stdgrp_nrows);
                if(stdgrp_nrows>0) {
                    for (int i = 0; i < student_group_row.length(); i++) {
                        JSONObject obj4 = (JSONObject) student_group_row.get(i);
                        System.out.println("student_group_row123654" + obj4);
                        String stgrp_id = obj4.get("stgrp_id").toString();
                        String student_id = obj4.get("student_id").toString();
                        String group_id = obj4.get("group_id").toString();
                        int travel_id = obj4.getInt("travel_id");
                        System.out.println("student_group_rownaaaame is" + student_id + travel_id);
                        count = db.getStudentGroupCount(stgrp_id,travel_id);
                        if (count == 0) {
                            db.putStudentGroupInfo(db, stgrp_id, student_id, group_id, travel_id);
                        }
                    }
                }

                //Syncing Student Row from server to sqlite
                JSONArray student_row = arr.getJSONArray("student_row");
                int student_nrows = (int) ((JSONObject) student_row.get(0)).get("NROWS");
                System.out.println("student_nrowswsle_nrows "+student_nrows);
                if(student_nrows>0) {
                    for (int i = 0; i < student_row.length(); i++) {
                        JSONObject object = (JSONObject) student_row.get(i);
                        System.out.println("client_row123654" + object);
                        int student_id = object.getInt("student_id");
                        String id_number = object.get("id_number").toString();
                        int is_active = object.getInt("is_active");
                        String email = object.get("email_id").toString();
                        final String student_photo = object.get("student_photo").toString();
                        String name = object.get("name").toString();
                        String mobile = object.get("phone").toString();
                        int travel_id = object.getInt("travel_id");
                        String exp_dt = object.get("exp_dt").toString();
                        is_image_sync = arr.getString("is_image_sync");
                        System.out.println(i + " naaaame is" + is_image_sync + mobile + email + student_id +
                                travel_id + student_photo + "qqqq");
                        if (is_image_sync.equals("1")) {
                            File f = new File(Environment.getExternalStorageDirectory() + "/" + DOMAIN + "/" +
                                    student_photo);
                            System.out.println("ppphoto" + f.getAbsolutePath());

                            if (!f.exists()) {
                                final JSONObject finalObj = object;
                                Thread t = new Thread(new Runnable() {

                                    @Override
                                    public void run() {
                                        URL url = null;
                                        try {
                                            url = new URL(BASEURL + MEDIA + student_photo);
                                            System.out.println("xxxxx12345" + url);
                                        } catch (MalformedURLException e) {
                                            e.printStackTrace();
                                        }
                                        System.out.println("urllll" + url);
                                        InputStream input = null;
                                        try {
                                            input = url.openStream();
                                            Bitmap bitmap = BitmapFactory.decodeStream(input);
                                            OutputStream output;
                                            File file = null;

                                            File filepath = Environment.getExternalStorageDirectory();

                                            File dir = new File(filepath.getAbsolutePath()
                                                    + "/" + DOMAIN + "/");
                                            dir.mkdirs();

                                            file = new File(dir, student_photo);

                                            try {
                                                output = new FileOutputStream(file);
                                                bitmap.compress(Bitmap.CompressFormat.PNG, 100, output);
                                                output.flush();
                                                output.close();
                                            } catch (Exception e) {
                                                // TODO Auto-generated catch block
                                                e.printStackTrace();
                                            }
                                        } catch (IOException e) {
                                            e.printStackTrace();
                                        }
                                    }
                                });
                                t.start();
                            }
                        }
                        count = db.getStudentCount(student_id, travel_id);
                        if (count == 0) {
                            db.putStudentInformation(db, student_id, name, id_number, mobile,
                                    email, student_photo, travel_id, is_active, exp_dt);
                        }
                    }

                 }
                prgDialog.hide();
            } else {
                    Toast.makeText(getApplicationContext(),arr.optString("message"),Toast.LENGTH_LONG).show();

            }

        saveToSqlite();
        } catch (JSONException e) {
          e.printStackTrace();
        }
    }

    // Upload the captured image to server when the student details is obtained from sqlite
    protected String uploadImageToServer(String filepath) {
        String responseString = null;

        HttpClient httpclient = new DefaultHttpClient();
        HttpPost httppost = new HttpPost(Config.FILE_SYNC_UPLOAD_URL);

        try {
            AndroidMultiPartEntity entity = new AndroidMultiPartEntity(
                    new AndroidMultiPartEntity.ProgressListener() {

                        @Override
                        public void transferred(long num) {

                        }
                    });

            File sourceFile = new File(filepath);
            Log.d("sourceFile5678",sourceFile.toString());

            // Adding file data to http body
            entity.addPart("image", new FileBody(sourceFile));

            // Extra parameters if you want to pass to server
            entity.addPart("website",
                    new StringBody("www.androidhive.info"));
            entity.addPart("email", new StringBody("abc@gmail.com"));


            entity.addPart("image_type", new StringBody("student"));

         /*  if ( type.equals("GUARDIAN") ) {
                entity.addPart("guardian_id", new StringBody(guardian_id));
                entity.addPart("image_type", new StringBody("guardian"));
            }*/


            totalSize = entity.getContentLength();
            httppost.setEntity(entity);

            // Making server call
            HttpResponse response = httpclient.execute(httppost);
            HttpEntity r_entity = response.getEntity();

            int statusCode = response.getStatusLine().getStatusCode();
            if (statusCode == 200) {
                // Server response
                responseString = EntityUtils.toString(r_entity);

            } else {
                responseString = "Error occurred! Http Status Code: "
                        + statusCode;
            }
        } catch (ClientProtocolException e) {
            responseString = e.toString();
        } catch (IOException e) {
            responseString = e.toString();
        }
        File del = new File(filepath);
        System.out.println(" Filedel "+filepath);
        del.delete();

        return responseString;
    }

    // set alarm to sync the data from sqlite to server for every 1 min
    public void setAlarm() {
        manager = (AlarmManager)getSystemService(Context.ALARM_SERVICE);
        int interval = 60000;
        Log.d("alaraaaaaam",pendingIntent.toString());

        manager.setRepeating(AlarmManager.RTC_WAKEUP, System.currentTimeMillis(), interval, pendingIntent);
    }

    public void setAttendanceLogAlarm() {
        manager = (AlarmManager)getSystemService(Context.ALARM_SERVICE);
        int interval = 60000;
        Log.d("alaraaaaaam",pendingIntentAtt.toString());

        manager.setRepeating(AlarmManager.RTC_WAKEUP, System.currentTimeMillis(), interval, pendingIntentAtt);
    }

    public void setUpdateAlarm(Calendar targetCal) {
        int interval = 1000 * 60 * 20;
        Date date =new Date();
        DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        manager = (AlarmManager)getSystemService(Context.ALARM_SERVICE);
        System.out.println("setUpdateAlarm123 "+targetCal.getTimeInMillis());
        manager.setRepeating(AlarmManager.RTC_WAKEUP, targetCal.getTimeInMillis(), interval, pendingIntentUpdate);
    }
}