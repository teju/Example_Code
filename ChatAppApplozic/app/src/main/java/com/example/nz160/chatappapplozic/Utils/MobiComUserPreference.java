package com.example.nz160.chatappapplozic.Utils;

import android.content.Context;
import android.content.SharedPreferences;
import android.text.TextUtils;

/**
 * Created by nz160 on 26-09-2017.
 */

public class MobiComUserPreference {

    private static MobiComUserPreference userpref;
    public SharedPreferences sharedPreferences;
    private Context context;
    private static String device_registration_id = "device_registration_id";
    private static String base_url = "base_url";
    private static String device_key_string = "device_key_string";

    public MobiComUserPreference(Context context) {
        this.context = context;
        sharedPreferences = context.getSharedPreferences(MobiComKitClientService.getApplicationKey(context),
                context.MODE_PRIVATE);
    }
    public String getUrl() {
        return sharedPreferences.getString(base_url, null);
    }
    public static MobiComUserPreference getInstance(Context context) {
        if (userpref == null) {
            userpref = new MobiComUserPreference(context.getApplicationContext());
        }
        return userpref;
    }
    public boolean isRegistered() {
        return !TextUtils.isEmpty(getDeviceKeyString());
    }

    public String getDeviceRegistrationId() {
        return sharedPreferences.getString(device_registration_id, null);
    }

    public void setDeviceRegistrationId(String deviceRegistrationId) {
        sharedPreferences.edit().putString(device_registration_id, deviceRegistrationId).commit();
    }

    public String getDeviceKeyString() {
        return sharedPreferences.getString(device_key_string, null);
    }

    public void setDeviceKeyString(String deviceKeyString) {
        sharedPreferences.edit().putString(device_key_string, deviceKeyString).commit();
    }

}
