package com.example.nz160.chatappapplozic;

import android.content.Context;
import android.text.TextUtils;

import com.example.nz160.chatappapplozic.Utils.GsonUtils;
import com.example.nz160.chatappapplozic.Utils.MobiComUserPreference;
import com.example.nz160.chatappapplozic.Utils.SyncBlockUserApiResponse;
import com.example.nz160.chatappapplozic.Utils.Utils;

/**
 * Created by nz160 on 26-09-2017.
 */
public class UserClientService {
    public static final String BASE_URL_METADATA = "com.applozic.server.url";
    public static final String MQTT_BASE_URL_METADATA = "com.applozic.mqtt.server.url";
    public static final String FILE_URL = "/rest/ws/aws/file/";
    public static String APPLICATION_KEY_HEADER_VALUE_METADATA = "com.applozic.application.key";
    public static String APP_MODULE_NAME_META_DATA_KEY = "com.applozic.module.key";
    protected Context context;
    protected String DEFAULT_URL = "https://apps.applozic.com";
    protected String FILE_BASE_URL = "https://applozic.appspot.com";
    protected String DEFAULT_MQTT_URL = "tcp://apps.applozic.com:1883";
    public static final String BLOCK_USER_SYNC_URL = "/rest/ws/user/blocked/sync";
    private HttpRequestUtils httpRequestUtils;

    public String getBlockUserSyncUrl() {
        return getBaseUrl() + BLOCK_USER_SYNC_URL;
    }

    public SyncBlockUserApiResponse getSyncUserBlockList(String lastSyncTime) {
        try {
            String url = getBlockUserSyncUrl() + "?lastSyncTime=" + lastSyncTime;
            String response = httpRequestUtils.getResponse(url, "application/json", "application/json");

            if (response == null || TextUtils.isEmpty(response) || response.equals("UnAuthorized Access")) {
                return null;
            }
            return (SyncBlockUserApiResponse) GsonUtils.getObjectFromJson(response, SyncBlockUserApiResponse.class);
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }
    protected String getBaseUrl() {
        String SELECTED_BASE_URL = MobiComUserPreference.getInstance(context).getUrl();

        if (!TextUtils.isEmpty(SELECTED_BASE_URL)) {
            return SELECTED_BASE_URL;
        }
        String BASE_URL = Utils.getMetaDataValue(context.getApplicationContext(), BASE_URL_METADATA);
        if (!TextUtils.isEmpty(BASE_URL)) {
            return BASE_URL;
        }

        return DEFAULT_URL;
    }

}
