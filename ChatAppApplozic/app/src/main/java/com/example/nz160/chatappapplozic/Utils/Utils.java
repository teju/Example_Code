package com.example.nz160.chatappapplozic.Utils;

import android.content.Context;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;

/**
 * Created by nz160 on 26-09-2017.
 */
public class Utils {

    public static String getMetaDataValue(Context context, String metaDataName) {
        try {
            ApplicationInfo ai = context.getPackageManager().getApplicationInfo(context.getPackageName(),
                    PackageManager.GET_META_DATA);
            if (ai.metaData != null) {
                return ai.metaData.getString(metaDataName);
            }
        } catch (PackageManager.NameNotFoundException e) {
            e.printStackTrace();
            return null;
        }
        return null;
    }
}
