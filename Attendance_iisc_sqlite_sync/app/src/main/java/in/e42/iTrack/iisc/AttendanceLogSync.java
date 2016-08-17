package in.e42.iTrack.iisc;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.widget.Toast;

import com.google.gson.Gson;
import com.loopj.android.http.AsyncHttpClient;
import com.loopj.android.http.AsyncHttpResponseHandler;
import com.loopj.android.http.RequestParams;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by CN92 on 7/13/2015.
 */
public class AttendanceLogSync extends BroadcastReceiver {
    @Override
    public void onReceive(final Context context, Intent intent) {
        Context arg0=null;

        final DataBaseHelper db=new DataBaseHelper(context);

        //Send all the details of tstudentlog to server for every 1 min
        AsyncHttpClient client = new AsyncHttpClient();
        RequestParams params = new RequestParams();
        Object[] attendancelogArray=db.getAllAttendanceLogDetails();

        if(attendancelogArray.length!=0){
            //  prgDialog.show();
            Gson gson = new Gson();
            // convert java object to JSON format,
            // and returned as JSON formatted string
            TelephonyManager telephonyManager = (TelephonyManager)context.getSystemService(Context.
                    TELEPHONY_SERVICE);
            String attendancejson = gson.toJson(attendancelogArray);
            params.put("attendancelogJSON", attendancejson);
            client.post(MainActivity.BASEURL+"app/sync_iisc_log/"+telephonyManager.getDeviceId(),params ,new
            AsyncHttpResponseHandler() {

                //on successfully updating the tstudentlog in server get studentlog id
                // from server and update the is sync as 1 for that obtained studentlog id
                @Override
                public void onSuccess(String response) {
                    Log.d("String_attendance_log_response", response);
                    try {
                        JSONObject arr = new JSONObject(response);
                        if( arr.optString("status").equals("OK") ) {
                            System.out.println("jsonresponsence12345 " + arr);
                            JSONArray sync_attendancelog_arr = arr.getJSONArray("sync_attendancelog_id");
                            System.out.println("jsonattendanceresponsence4567 " + sync_attendancelog_arr);
                            for (int i = 0; i < sync_attendancelog_arr.length(); i++) {
                                System.out.println(" is123456 " + sync_attendancelog_arr.get(i));
                                System.out.println("jsonresponsence3456 ");
                                db.updateAttendanceLogSyncStatus(sync_attendancelog_arr.getString(i), 1);
                            }
                            Toast.makeText(context, "Attendance Log Sync completed!", Toast.LENGTH_LONG).show();
                        } else {
                            Toast.makeText(context,arr.optString("message"),Toast.LENGTH_LONG).show();
                        }
                    } catch (JSONException e) {
                        // TODO Auto-generated catch block
                        Log.d("String Error Response", response);
                        Toast.makeText(context, "Error Occured [Server's JSON response might be invalid]!", Toast.LENGTH_LONG).show();
                        e.printStackTrace();
                    }
                }
                @Override
                public void onFailure(int statusCode, Throwable error,
                                      String content) {
                    // TODO Auto-generated method stub

                    if(statusCode == 404){
                        Toast.makeText(context, "Requested resource not found", Toast.LENGTH_LONG).show();
                    }else if(statusCode == 500){
                        Toast.makeText(context, "Something went wrong at server end", Toast.LENGTH_LONG).show();
                    }else{
                        //      Toast.makeText(context, "Unexpected Error occcured! [Most common Error: Device might not be connected to Internet]", Toast.LENGTH_LONG).show();
                    }
                }
            });

        } else {
            //Toast.makeText(context, "No data in SQLite DB, please do enter User name to perform Sync action", Toast.LENGTH_LONG).show();
        }

    }
}
