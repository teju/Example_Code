/**
 * Created by CPC2 on 3/23/2015.
 */
package in.e42.iTrack.iisc;

import android.content.Context;
import android.content.Intent;
import android.telephony.TelephonyManager;

public class Config {
    Context mContext;
    public Config(Context mContext) {
        this.mContext = mContext;
    }
    // File upload url (replace the ip with your server address)

    public static final String FILE_UPLOAD_URL = MainActivity.BASEURL + "app/iisc_app_attendance_image_store/";
    public static final String FILE_SYNC_UPLOAD_URL = MainActivity.BASEURL + "app/app_sync_image_store" ;
    // Directory name to store captured images and videos
}