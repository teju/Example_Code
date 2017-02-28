package com.applozic.mobicomkit.uiwidgets.conversation;

import android.content.Intent;
import android.net.Uri;
import android.provider.ContactsContract;
import android.provider.MediaStore;
import android.support.v4.app.FragmentActivity;
import android.view.View;
import android.widget.AdapterView;
import android.widget.GridView;
import android.widget.PopupWindow;

import com.applozic.mobicomkit.api.attachment.FileClientService;
import com.applozic.mobicomkit.uiwidgets.conversation.activity.ConversationActivity;
import com.applozic.mobicomkit.uiwidgets.conversation.activity.MobiComAttachmentSelectorActivity;
import com.applozic.mobicomkit.uiwidgets.conversation.fragment.MultimediaOptionFragment;

import java.io.File;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

/**
 * Created by reytum on 19/3/16.
 */
public class MultimediaOptionsGridView {
    private Uri capturedImageUri;
    public PopupWindow showPopup;
    FragmentActivity context;
    GridView multimediaOptions;

    public MultimediaOptionsGridView(FragmentActivity context, GridView multimediaOptions) {
        this.context = context;
        this.multimediaOptions = multimediaOptions;
    }

    public void setMultimediaClickListener(final List<String> keys) {
        capturedImageUri = null;

        multimediaOptions.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {

                executeMethod(keys.get(position));
            }
        });
    }

    public void executeMethod(String key) {

        switch (key) {

            case ":location":
                ((ConversationActivity) context).processLocation();
                break;
            case ":camera":
                ((ConversationActivity) context).isTakePhoto(true);
                ((ConversationActivity) context).processCameraAction();

                break;

            case ":file":
                ((ConversationActivity) context).isAttachment(true);
                ((ConversationActivity) context).processAttachment();
                break;

            case ":audio":
                ((ConversationActivity) context).showAudioRecordingDialog();
                break;

            case ":video":
                ((ConversationActivity) context).isTakePhoto(false);
                ((ConversationActivity) context).processVideoRecording();
                break;

            case ":contact":
                //Sharing contact.
                ((ConversationActivity) context).processContact();
                break;

            case ":pricing":
                new ConversationUIService(context).sendPriceMessage();
                break;
            default:
        }
        multimediaOptions.setVisibility(View.GONE);
    }
}