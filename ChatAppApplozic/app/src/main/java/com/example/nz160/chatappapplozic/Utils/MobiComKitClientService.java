package com.example.nz160.chatappapplozic.Utils;

import android.content.Context;
import android.text.TextUtils;

/**
 * Created by nz160 on 26-09-2017.
 */
public class MobiComKitClientService {
    private  Context context;
    public static String APPLICATION_KEY_HEADER_VALUE_METADATA = "com.applozic.application.key";

    public MobiComKitClientService() {

    }

    public MobiComKitClientService(Context context) {
        this.context = context.getApplicationContext();
    }

    public static String getApplicationKey(Context context) {
        String applicationKey = Applozic.getInstance(context).getApplicationKey();
        if (!TextUtils.isEmpty(applicationKey)) {
            return applicationKey;
        }
        return Utils.getMetaDataValue(context.getApplicationContext(), APPLICATION_KEY_HEADER_VALUE_METADATA);
    }

}
