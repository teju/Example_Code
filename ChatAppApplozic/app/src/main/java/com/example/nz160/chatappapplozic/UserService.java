package com.example.nz160.chatappapplozic;

import android.text.TextUtils;

import com.example.nz160.chatappapplozic.Utils.SyncBlockUserApiResponse;
import com.example.nz160.chatappapplozic.Utils.SyncUserBlockFeed;
import com.example.nz160.chatappapplozic.Utils.SyncUserBlockListFeed;

import java.util.List;

/**
 * Created by nz160 on 26-09-2017.
 */
public class UserService {
    UserClientService userClientService;

    public synchronized void processSyncUserBlock() {
        try {
            SyncBlockUserApiResponse apiResponse = userClientService.getSyncUserBlockList(userPreference.getUserBlockSyncTime());
            if (apiResponse != null && SyncBlockUserApiResponse.SUCCESS.equals(apiResponse.getStatus())) {
                SyncUserBlockListFeed syncUserBlockListFeed = apiResponse.getResponse();
                if (syncUserBlockListFeed != null) {
                    List<SyncUserBlockFeed> blockedToUserList = syncUserBlockListFeed.getBlockedToUserList();
                    List<SyncUserBlockFeed> blockedByUserList = syncUserBlockListFeed.getBlockedByUserList();
                    if (blockedToUserList != null && blockedToUserList.size() > 0) {
                        for (SyncUserBlockFeed syncUserBlockedFeed : blockedToUserList) {
                            Contact contact = new Contact();
                            if (syncUserBlockedFeed.getUserBlocked() != null &&
                                    !TextUtils.isEmpty(syncUserBlockedFeed.getBlockedTo())) {
                                if (baseContactService.isContactExists(syncUserBlockedFeed.getBlockedTo())) {
                                    baseContactService.updateUserBlocked(syncUserBlockedFeed.getBlockedTo(),
                                            syncUserBlockedFeed.getUserBlocked());
                                } else {
                                    contact.setBlocked(syncUserBlockedFeed.getUserBlocked());
                                    contact.setUserId(syncUserBlockedFeed.getBlockedTo());
                                    baseContactService.upsert(contact);
                                    baseContactService.updateUserBlocked(syncUserBlockedFeed.getBlockedTo(),
                                            syncUserBlockedFeed.getUserBlocked());
                                }
                            }
                        }
                    }
                    if (blockedByUserList != null && blockedByUserList.size() > 0) {
                        for (SyncUserBlockFeed syncUserBlockByFeed : blockedByUserList) {
                            Contact contact = new Contact();
                            if (syncUserBlockByFeed.getUserBlocked() != null && !TextUtils.
                                    isEmpty(syncUserBlockByFeed.getBlockedBy())) {
                                if (baseContactService.isContactExists(syncUserBlockByFeed.getBlockedBy())) {
                                    baseContactService.updateUserBlockedBy(syncUserBlockByFeed.getBlockedBy(),
                                            syncUserBlockByFeed.getUserBlocked());
                                } else {
                                    contact.setBlockedBy(syncUserBlockByFeed.getUserBlocked());
                                    contact.setUserId(syncUserBlockByFeed.getBlockedBy());
                                    baseContactService.upsert(contact);
                                    baseContactService.updateUserBlockedBy(syncUserBlockByFeed.getBlockedBy(),
                                            syncUserBlockByFeed.getUserBlocked());
                                }
                            }
                        }
                    }
                }
                userPreference.setUserBlockSyncTime(apiResponse.getGeneratedAt());
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
