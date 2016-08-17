package in.e42.iTrack.iisc;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.graphics.Typeface;
import android.os.Environment;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Toast;

import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.AsyncHttpResponseHandler;
import com.loopj.android.http.RequestParams;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.List;

/**
 * Created by CN92 on 8/10/2015.
 */
public class UpdateAlarmReceiver extends BroadcastReceiver {
    private String photo;

    @Override
    public void onReceive(final Context context, Intent intent) {

        final DataBaseHelper db=new DataBaseHelper(context);

        AsyncHttpClient client = new AsyncHttpClient();
        // Http Request Params Object
        RequestParams params = new RequestParams();
        final TelephonyManager telephonyManager = (TelephonyManager)context.getSystemService(Context.
                TELEPHONY_SERVICE);

        // Make Http call to getusers.php
        client.post(MainActivity.BASEURL + "app/updatesqlite/"+telephonyManager.getDeviceId(), params, new
                AsyncHttpResponseHandler() {
                    @Override
                    public void onSuccess(String response) {

                        Log.d("btnSyncResponse ", response);
                        try {
                            JSONObject arr = new JSONObject(response);
                            if(!arr.optString("NROWS").equals("0")) {
                                updateAppSyncSQLite(response, context);
                            } else {
                                db.deleteImeiSupervisor(telephonyManager.getDeviceId() );
                            }

                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }
                    public void onFailure(int statusCode, Throwable error, String content) {
                        // TODO Auto-generated method stub
                        // Hide ProgressBar
                        if (statusCode == 404) {
                            Toast.makeText(context, "Requested resource not found",
                                    Toast.LENGTH_LONG).show();
                        } else if (statusCode == 500) {
                            Toast.makeText(context, "Something went wrong at server end",
                                    Toast.LENGTH_LONG).show();
                        } else {
                            Toast.makeText(context, "Unexpected Error occcured! [Most " +
                                            "common Error: Device might not be connected to Internet]",
                                    Toast.LENGTH_LONG).show();
                        }
                    }
                });

    }
    public void updateAppSyncSQLite(String response, Context context) throws IOException {
        JSONObject obj = null;
        JSONObject obj2 = null;
        final DataBaseHelper db=new DataBaseHelper(context);

        try {
            JSONObject arr = new JSONObject(response);
            System.out.println("student_arr12345 " + arr.optString("appsync_row"));
            if(arr.optString("status").equals("OK")) {

                //Syncing Student Row from server to sqlite
                JSONArray student_arr = arr.getJSONArray("appsync_row");
                for (int i = 0; i < student_arr.length(); i++) {
                    obj = (JSONObject) student_arr.get(i);
                    String table_name = obj.get("table_name").toString();
                    String status = obj.get("status").toString();
                    String id = obj.getString("primary_id");
                    int d_travel_id = obj.getInt("travel_id");
                    System.out.println("student_arr1234....." + table_name);

                    switch (table_name) {
                        case "tStudent":
                            JSONObject table_row = student_arr.getJSONObject(i);
                            System.out.println("JSONObjecttable_row" + table_row);
                            table_row = table_row.getJSONObject("table_row");
                            int NROWS = table_row.getInt("NROWS");
                            if( NROWS > 0  ) {
                                int student_id = table_row.getInt("student_id");
                                String name = table_row.getString("name");
                                final String student_photo = table_row.getString("student_photo");
                                String id_number = table_row.getString("id_number");
                                String phone = table_row.getString("phone");
                                String email_id = table_row.getString("email_id");
                                int travel_id = table_row.getInt("travel_id");
                                int is_active = table_row.getInt("is_active");
                                String exp_dt = table_row.getString("exp_dt");
                                System.out.println(" String exp_dt = table_rowis" + name + " " + exp_dt);
                                File filepath = Environment.getExternalStorageDirectory();
                                File file_path = new File(filepath.getAbsolutePath()
                                        + "/" + MainActivity.DOMAIN+"/"+student_photo);
                                File file = new File(file_path.toString());

                                List<DataBase> contacts = db.getStudent(student_id, travel_id);
                                System.out.println("getStudent1234 "+contacts);
                                for (DataBase cn : contacts) {
                                    String photo = cn.getStudentImage();
                                    System.out.println("Updated_iamge "+student_photo+" db photo "+photo);
                                    if ( student_photo.equals("no_image.jpg") && !photo.equals("no_image.jpg") ) {
                                        File del_file_path = new File(filepath.getAbsolutePath()
                                                + "/" + MainActivity.DOMAIN+"/"+photo);
                                        File del_file = new File(del_file_path.toString());
                                        delete(del_file,context);

                                    }
                                }

                                if ( !student_photo.equals("no_image.jpg") && !file.exists()) {
                                    final JSONObject finalObj = obj;
                                    Thread t = new Thread(new Runnable() {

                                        @Override
                                        public void run() {
                                            URL url = null;
                                            try {
                                                url = new URL(MainActivity.BASEURL + MainActivity.MEDIA + student_photo);
                                                System.out.println("xxxxx12345"+url);
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
                                                        + "/" + MainActivity.DOMAIN + "/");
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
                                switch (status) {
                                    case "U":

                                        db.updateStudent(student_id, name, id_number, phone,
                                                email_id, student_photo, travel_id,
                                                is_active,exp_dt);


                                        break;
                                    case "A":
                                        int student_count = db.getStudentCount(student_id, travel_id);
                                        if(student_count == 0) {
                                            db.putStudentInformation(db, student_id, name, id_number, phone,
                                                    email_id, photo, travel_id, is_active, exp_dt);
                                        }
                                        break;
                                }

                            } else if( status.equals("D")) {
                                System.out.println("naaaame is" +status.equals("D" ));

                                db.deleteStudent(id, d_travel_id);
                            }
                            break;
                        case "tSupervisor":
                            JSONObject supervisor_table_row = student_arr.getJSONObject(i);
                            supervisor_table_row = supervisor_table_row.getJSONObject("table_row");
                            int supervisor_NROWS = supervisor_table_row.getInt("NROWS");
                            if (supervisor_NROWS > 0) {
                                int supervisor_id = supervisor_table_row.getInt("supervisor_id");
                                int is_active = supervisor_table_row.getInt("is_active");
                                String imei = supervisor_table_row.getString("imei");
                                int supervisor_travel_id = supervisor_table_row.getInt("travel_id");
                                System.out.println("supervisor123naaaame is" + supervisor_id + " " + imei);

                                switch (status) {
                                    case "U":
                                        db.updateSupervisor(supervisor_id, imei, supervisor_travel_id,is_active);
                                        break;
                                    case "A":
                                        int supervisorcount = db.getSupervisorCount(imei,supervisor_travel_id);
                                        if(supervisorcount == 0) {
                                            db.putSupervisorInfo(db, supervisor_id, imei, supervisor_travel_id, is_active);
                                        }
                                        break;

                                }
                            } else if(status.equals("D")) {
                                db.deleteSupervisor(id,d_travel_id);
                            }
                            break;
                        case "tSetting":
                            JSONObject setting_table_row = student_arr.getJSONObject(i);
                            setting_table_row = setting_table_row.getJSONObject("table_row");
                            int setting_NROWS = setting_table_row.getInt("NROWS");
                            if (setting_NROWS > 0) {
                                int setting_id = setting_table_row.getInt("setting_id");
                                String setting_name = setting_table_row.getString("name");
                                String value = setting_table_row.getString("value");
                                int setting_travel_id = setting_table_row.getInt("travel_id");
                                System.out.println("supervisor123naaaame is" + setting_id + " " + setting_name);
                                switch (status) {
                                    case "U":
                                        db.updateSetting(setting_id, setting_name, value, setting_travel_id);
                                        break;
                                    case "A":
                                        int supervisor_count = db.getSettingCount(setting_id, setting_travel_id);
                                        if(supervisor_count == 0) {
                                            db.putSettingInfo(db, setting_id, setting_name, value, setting_travel_id);
                                        }
                                        break;

                                }

                            } else if(status.equals("D")) {
                                db.deleteSetting(id,d_travel_id);
                            }
                            break;
                        case "tNFCTag":
                            String nfc_tag_id = null;
                            int nfctag_travel_id = 0;
                            JSONObject nfctag_table_row = student_arr.getJSONObject(i);
                            nfctag_table_row = nfctag_table_row.getJSONObject("table_row");
                            int nfctag_NROWS = nfctag_table_row.getInt("NROWS");
                            System.out.println("tNFCTagNROWS"+nfctag_NROWS);
                            if (nfctag_NROWS > 0) {
                                nfc_tag_id = nfctag_table_row.get("nfc_tag_id").toString();
                                String nfc_tag_id_number = nfctag_table_row.getString("id_number");
                                String type = nfctag_table_row.getString("type");
                                nfctag_travel_id = nfctag_table_row.getInt("travel_id");
                                System.out.println("nfctag123naaaame is" + nfc_tag_id + " " +
                                        nfc_tag_id_number+" "+status);

                                switch (status) {
                                    case "U":
                                        db.updateNFCTag(nfc_tag_id, nfc_tag_id_number, type, nfctag_travel_id);
                                        break;
                                    case "A":
                                        int nfctagcount = db.getNFCTagCount(nfc_tag_id,nfctag_travel_id);
                                        if(nfctagcount == 0) {
                                            db.putNFCTagInfo(db, nfc_tag_id, nfc_tag_id_number, type, nfctag_travel_id);
                                        }
                                        break;
                                }

                            } else if(status.equals("D")) {
                                db.deleteNFCTag(nfc_tag_id,d_travel_id);
                            }
                            break;
                        case "tImeiLocation":
                            String imei = null;
                            JSONObject imeilocation_table_row = student_arr.getJSONObject(i);
                            imeilocation_table_row = imeilocation_table_row.getJSONObject("table_row");
                            int imei_location_NROWS = imeilocation_table_row.getInt("NROWS");
                            System.out.println("tImeiLocationNROWS"+imei_location_NROWS);
                            if (imei_location_NROWS > 0) {
                                String location_id = imeilocation_table_row.get("location_id").toString();
                                imei = imeilocation_table_row.getString("imei");
                                int imei_location_travel_id = imeilocation_table_row.getInt("travel_id");
                                System.out.println("imeilocationnaaaame is" + imei + " " +
                                        imei_location_travel_id+" "+status + " "+ location_id);

                                switch (status) {
                                    case "A":
                                        int imeiloccount = db.getImeiLocationCount(imei,imei_location_travel_id);
                                        if(imeiloccount == 0) {
                                            db.putImeiLocationInfo(db, location_id, imei, imei_location_travel_id);
                                        }
                                        break;
                                }

                            } else if(status.equals("D")) {
                                db.deleteImeiLocation(id,d_travel_id);
                            }
                            break;
                        case "tStLocGroup":
                            JSONObject stlocgrp_table_row = student_arr.getJSONObject(i);
                            stlocgrp_table_row = stlocgrp_table_row.getJSONObject("table_row");
                            int st_locgrp_NROWS = stlocgrp_table_row.getInt("NROWS");
                            System.out.println("tStLocLocation"+st_locgrp_NROWS);
                            if (st_locgrp_NROWS > 0) {
                                String stlocgrp_id = stlocgrp_table_row.get("stlocgrp_id").toString();
                                String location_id = stlocgrp_table_row.get("location_id").toString();
                                String group_id = stlocgrp_table_row.getString("group_id");
                                int imei_location_travel_id = stlocgrp_table_row.getInt("travel_id");
                                System.out.println("tStLocLocationertbg is" + group_id + " " +
                                        imei_location_travel_id+" "+status + " "+ stlocgrp_id+" id "+id);
                                switch (status) {
                                    case "A":
                                        int stlocgrpcount = db.getStLocGroupCount(stlocgrp_id,imei_location_travel_id);
                                        System.out.println("stlocgrpcount"+stlocgrpcount);
                                        if(stlocgrpcount == 0) {
                                            db.putStLocGroupInfo(db,stlocgrp_id, location_id, group_id, imei_location_travel_id);
                                        }
                                        break;

                                }

                            } else if(status.equals("D")) {
                                db.deleteStLocGroup(id,d_travel_id);
                            }
                            break;
                        case "tGroup":
                            JSONObject grp_table_row = student_arr.getJSONObject(i);
                            System.out.println("UPDATE_TGROUP "+grp_table_row.toString());
                            grp_table_row = grp_table_row.getJSONObject("table_row");
                            int grp_NROWS = grp_table_row.getInt("NROWS");
                            System.out.println("tStLocLocation"+grp_NROWS);
                            if (grp_NROWS > 0) {
                                String group_id = grp_table_row.get("group_id").toString();
                                String group_name = grp_table_row.get("group_name").toString();
                                String is_active = grp_table_row.getString("is_active");
                                String created_dt = grp_table_row.getString("created_dt");
                                int group_travel_id = grp_table_row.getInt("travel_id");
                                System.out.println("tFGROUP is" + group_id + " " +
                                        group_travel_id+" "+status + " "+ group_id+" id "+id);
                                switch (status) {
                                    case "A":
                                        int stlocgrpcount = db.getGroupCount(group_id,group_travel_id);
                                        System.out.println("GROUPCOUNT"+stlocgrpcount);
                                        if(stlocgrpcount == 0) {
                                            db.putGroupInfo(db,group_id, group_name, is_active,created_dt, group_travel_id);
                                        }
                                        break;
                                    case "U":
                                        db.updateGroup(group_id, group_name,is_active, created_dt, group_travel_id);
                                        break;
                                }

                            } else if(status.equals("D")) {
                                db.deleteGroup(id,d_travel_id);
                            }
                            break;
                        case "tLocation":
                            JSONObject location_table_row = student_arr.getJSONObject(i);
                            location_table_row = location_table_row.getJSONObject("table_row");
                            int loc_NROWS = location_table_row.getInt("NROWS");
                            System.out.println("tStLocLocation"+loc_NROWS);
                            if (loc_NROWS > 0) {
                                String location_id = location_table_row.get("location_id").toString();
                                String location_name = location_table_row.get("location_name").toString();
                                String created_dt = location_table_row.getString("created_dt");
                                int loc_travel_id = location_table_row.getInt("travel_id");
                                System.out.println("tFGROUP is" + location_id + " " +
                                        loc_travel_id+" "+status + " "+ location_id+" id "+id);
                                switch (status) {
                                    case "A":
                                        int loccount = db.getLocationCount(location_id,loc_travel_id);
                                        System.out.println("GROUPCOUNT"+loccount);
                                        if(loccount == 0) {
                                            db.putLocationInfo(db,location_id, location_name,created_dt, loc_travel_id);
                                        }
                                        break;
                                    case "U":
                                        db.updateLocation(location_id, location_name, created_dt, loc_travel_id);
                                        break;
                                }

                            } else if(status.equals("D")) {
                                db.deleteLocation(id,d_travel_id);
                            }
                            break;
                        case "tStudentGroup":
                            JSONObject stgrp_table_row = student_arr.getJSONObject(i);
                            stgrp_table_row = stgrp_table_row.getJSONObject("table_row");
                            int st_grp_NROWS = stgrp_table_row.getInt("NROWS");
                            System.out.println("tStudentGroupst_grp_NROWS"+st_grp_NROWS);
                            if (st_grp_NROWS > 0) {
                                String stgrp_id = stgrp_table_row.get("stgrp_id").toString();
                                String student_id = stgrp_table_row.get("student_id").toString();
                                String group_id = stgrp_table_row.getString("group_id");
                                int st_grp_travel_id = stgrp_table_row.getInt("travel_id");
                                System.out.println("ttStudentGroupnertbg is" + group_id + " " +
                                        st_grp_travel_id+" "+status + " "+ stgrp_id);
                                switch (status) {
                                    case "A":
                                        int stgrpcount = db.getStudentGroupCount(stgrp_id,st_grp_travel_id);
                                        if(stgrpcount == 0) {
                                            db.putStudentGroupInfo(db,stgrp_id,student_id, group_id,
                                                    st_grp_travel_id);
                                        }
                                        break;
                                }

                            } else if(status.equals("D")) {
                                db.deleteStudentGroup(id,d_travel_id);
                            }
                            break;

                    }
                }
            }
            else {
                Toast.makeText(context,"No Rows found to update",Toast.LENGTH_LONG).show();
            }

            server_supervisor_update(context);

        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    //Send request to update the supervisor is sync to 1 after the row is updated in sqlite
    public  void server_supervisor_update(final Context context) {
        AsyncHttpClient client = new AsyncHttpClient();
        // Http Request Params Object
        RequestParams params = new RequestParams();
        // Show ProgressBar
        TelephonyManager telephonyManager = (TelephonyManager)context.getSystemService(Context.
                TELEPHONY_SERVICE);

        // Make Http call to getusers.php
        client.post(MainActivity.BASEURL + "app/updatesupervisor/"+telephonyManager.getDeviceId(), params, new
                AsyncHttpResponseHandler() {
                    @Override
                    public void onSuccess(String response) {

                        Log.d("btnSyncResponse ", response);
                        try {
                            updateAppSyncSQLite(response, context);
                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }
                    public void onFailure(int statusCode, Throwable error, String content) {
                        // TODO Auto-generated method stub
                        // Hide ProgressBar
                        if (statusCode == 404) {
                            Toast.makeText(context, "Requested resource not found",
                                    Toast.LENGTH_LONG).show();
                        } else if (statusCode == 500) {
                            Toast.makeText(context, "Something went wrong at server end",
                                    Toast.LENGTH_LONG).show();
                        } else {
                            Toast.makeText(context, "Unexpected Error occcured! [Most " +
                                            "common Error: Device might not be connected to Internet]",
                                    Toast.LENGTH_LONG).show();
                        }
                    }

                });

    }

    public void delete(File file, Context context) {
            boolean status=file.delete();
            Toast.makeText(context, file.toString()+" status "+status,
                    Toast.LENGTH_LONG).show();
        System.out.println(file.toString()+" status "+status);
    }
}
