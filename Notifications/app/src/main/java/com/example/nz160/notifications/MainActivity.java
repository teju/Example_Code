package com.example.nz160.notifications;

import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.TaskStackBuilder;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.app.NotificationCompat;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;

public class MainActivity extends AppCompatActivity implements OnClickListener {
    private static Button start, stop, update, start_two, stop_two, update_two;

    private NotificationManager mNotificationManager;// Notification Manager

    // Notification id for both Notifications
    private final int notificationID_SingleLine = 111;
    private final int notificationID_MultiLine = 222;
    // No. of messages count for both type of notifications
    private int numMessages_SingleLine = 0;
    private int numMessages_MultiLine = 0;

    private static Uri alarmSound;// Alarm sound uri

    private final long[] pattern = { 100, 300, 300, 300 };// Vibrate pattern in
    // long array

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // Find all views id
        start = (Button) findViewById(R.id.start_notificationone);
        stop = (Button) findViewById(R.id.stop_notificationone);
        update = (Button) findViewById(R.id.update_notificationone);
        start_two = (Button) findViewById(R.id.start_notificationtwo);
        stop_two = (Button) findViewById(R.id.stop_notificationtwo);
        update_two = (Button) findViewById(R.id.update_notificationtwo);

        // Implement click listeners
        start.setOnClickListener(this);
        stop.setOnClickListener(this);
        update.setOnClickListener(this);
        start_two.setOnClickListener(this);
        stop_two.setOnClickListener(this);
        update_two.setOnClickListener(this);

        // Set by default alarm sound
        alarmSound = RingtoneManager
                .getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);

        // setting notification manager
        mNotificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
    }

    // Call repersentative methods on buttons press
    @Override
    public void onClick(View view) {
        switch (view.getId()) {
            case R.id.start_notificationone:
                displayNotificationSingleLine();
                break;

            case R.id.stop_notificationone:
                cancelNotificationSingleLine();
                break;

            case R.id.update_notificationone:
                updateNotificationSingleLine();
                break;

            case R.id.start_notificationtwo:
                displayNotificationMultiLine();
                break;

            case R.id.stop_notificationtwo:
                cancelNotificationMultiLine();
                break;

            case R.id.update_notificationtwo:
                updateNotificationMultiLine();
                break;

        }

    }

    /** Single Line Notifications Methods **/
    protected void displayNotificationSingleLine() {
        Log.i("Start", "notification");
		/* Invoking the default notification service */
        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(
                MainActivity.this);// Setting builder
        mBuilder.setContentTitle("New Message");// title
        mBuilder.setContentText("You've received new message from Androhub.");// Message
        mBuilder.setTicker("New Message Alert!");// Ticker
        mBuilder.setSmallIcon(R.drawable.app_icon_small);// Small icon
		/* Increase notification number every time a new notification arrives */
        mBuilder.setNumber(++numMessages_SingleLine);

        mBuilder.setSound(alarmSound);// set alarm sound
        mBuilder.setVibrate(pattern);// set vibration
		/* Creates an explicit intent for an Activity in your app */
        Intent resultIntent = new Intent(MainActivity.this,
                NotificationActivity.class);

        resultIntent.putExtra("notificationId", notificationID_SingleLine);// put
        // notification
        // id
        // into
        // intent
        resultIntent.putExtra("message", "http://androhub.com");//Your message to show in next activity

        // Task builder to maintain task for pending intent
        TaskStackBuilder stackBuilder = TaskStackBuilder.create(this);
        stackBuilder.addParentStack(NotificationActivity.class);
		/* Adds the Intent that starts the Activity to the top of the stack */
        stackBuilder.addNextIntent(resultIntent);

        PendingIntent resultPendingIntent = stackBuilder.getPendingIntent(0,
                PendingIntent.FLAG_UPDATE_CURRENT);// Set flag to update current
        mBuilder.setContentIntent(resultPendingIntent);// set content intent

		/* notificationID allows you to update the notification later on. */
        mNotificationManager
                .notify(notificationID_SingleLine, mBuilder.build());
    }

    protected void cancelNotificationSingleLine() {
        Log.i("Cancel", "notification");
        mNotificationManager.cancel(notificationID_SingleLine);// Cancel the
        // notification
        // id
        // notification
        // if it is
        // showing
    }

    protected void updateNotificationSingleLine() {
        Log.i("Update", "notification");
		/* Invoking the default notification service */
        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(
                this);
        mBuilder.setContentTitle("Updated Message");
        mBuilder.setContentText("You've got updated message from Androhub.");
        mBuilder.setTicker("Updated Message Alert!");
        mBuilder.setSmallIcon(R.drawable.app_icon_small);
        mBuilder.setSound(alarmSound);
        mBuilder.setVibrate(pattern);
		/* Increase notification number every time a new notification arrives */
        mBuilder.setNumber(++numMessages_SingleLine); /*
													 * Creates an explicit
													 * intent for an Activity in
													 * your app
													 */
        Intent resultIntent = new Intent(this, NotificationActivity.class);
        resultIntent.putExtra("notificationId", notificationID_SingleLine);
        resultIntent.putExtra("message", "http://androhub.com");
        TaskStackBuilder stackBuilder = TaskStackBuilder.create(this);
        stackBuilder.addParentStack(NotificationActivity.class);
		/* Adds the Intent that starts the Activity to the top of the stack */
        stackBuilder.addNextIntent(resultIntent);
        PendingIntent resultPendingIntent = stackBuilder.getPendingIntent(0,
                PendingIntent.FLAG_UPDATE_CURRENT);
        mBuilder.setContentIntent(resultPendingIntent);

		/* Update the existing notification using same notification ID */
        mNotificationManager
                .notify(notificationID_SingleLine, mBuilder.build());
    }

    /** ---------------------------------------------------------------------- **/

    /** Multi Line Notifications Methods **/
    protected void displayNotificationMultiLine() {
        Log.i("Start", "notification");
		/* Invoking the default notification service */
        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(
                this);
        mBuilder.setContentTitle("New Message");
        mBuilder.setContentText("You've received new message from Androhub.");
        mBuilder.setTicker("New Multi Message Alert!");
        mBuilder.setSmallIcon(R.drawable.app_icon_small);

		/* Increase notification number every time a new notification arrives */
        mBuilder.setNumber(++numMessages_MultiLine);
        mBuilder.setSound(alarmSound);
        mBuilder.setVibrate(pattern);
		/* Add Big View Specific Configuration */
        NotificationCompat.InboxStyle inboxStyle = new NotificationCompat.InboxStyle();

        String[] events = new String[6];// No of lines to show

        // Loop to add 6 items to array
        for (int i = 0; i < events.length; i++)
            events[i] = new String("This is " + i + " line...");

        // Sets a title for the Inbox style big view
        inboxStyle.setBigContentTitle("Big Title Details:");
        // Moves events into the big view
        for (int i = 0; i < events.length; i++) {
            inboxStyle.addLine(events[i]);
        }
        mBuilder.setStyle(inboxStyle);
		/* Creates an explicit intent for an Activity in your app */
        Intent resultIntent = new Intent(this, NotificationActivity.class);
        resultIntent.putExtra("notificationId", notificationID_MultiLine);
        resultIntent.putExtra("message", "http://androhub.com");
        TaskStackBuilder stackBuilder = TaskStackBuilder.create(this);
        stackBuilder.addParentStack(NotificationActivity.class);
		/* Adds the Intent that starts the Activity to the top of the stack */
        stackBuilder.addNextIntent(resultIntent);
        PendingIntent resultPendingIntent = stackBuilder.getPendingIntent(0,
                PendingIntent.FLAG_UPDATE_CURRENT);
        mBuilder.setContentIntent(resultPendingIntent);
        mNotificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
		/* notificationID allows you to update the notification later on. */
        mNotificationManager.notify(notificationID_MultiLine, mBuilder.build());

    }

    protected void cancelNotificationMultiLine() {
        Log.i("Cancel", "notification");
        mNotificationManager.cancel(notificationID_MultiLine);
    }

    protected void updateNotificationMultiLine() {
        Log.i("Start", "notification");
		/* Invoking the default notification service */
        NotificationCompat.Builder mBuilder = new NotificationCompat.Builder(
                this);
        mBuilder.setContentTitle("New Message");
        mBuilder.setContentText("You've got updated message from Androhub.");
        mBuilder.setTicker("New Multi Message Alert!");
        mBuilder.setSmallIcon(R.drawable.app_icon_small);
		/* Increase notification number every time a new notification arrives */
        mBuilder.setNumber(++numMessages_MultiLine);
        mBuilder.setSound(alarmSound);
        mBuilder.setVibrate(pattern);
		/* Add Big View Specific Configuration */
        NotificationCompat.InboxStyle inboxStyle = new NotificationCompat.InboxStyle();
        String[] events = new String[6];
        // Loop to add 6 items to array
        for (int i = 0; i < events.length; i++)
            events[i] = new String("This is update of " + i + " line...");
        // Sets a title for the Inbox style big view
        inboxStyle.setBigContentTitle("Big Title Details");
        // Moves events into the big view
        for (int i = 0; i < events.length; i++) {
            inboxStyle.addLine(events[i]);
        }
        mBuilder.setStyle(inboxStyle);
		/* Creates an explicit intent for an Activity in your app */
        Intent resultIntent = new Intent(this, NotificationActivity.class);
        resultIntent.putExtra("notificationId", notificationID_MultiLine);
        resultIntent.putExtra("message", "http://androhub.com");
        TaskStackBuilder stackBuilder = TaskStackBuilder.create(this);
        stackBuilder.addParentStack(NotificationActivity.class);
		/* Adds the Intent that starts the Activity to the top of the stack */
        stackBuilder.addNextIntent(resultIntent);
        PendingIntent resultPendingIntent = stackBuilder.getPendingIntent(0,
                PendingIntent.FLAG_UPDATE_CURRENT);
        mBuilder.setContentIntent(resultPendingIntent);
        mNotificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
		/* notificationID allows you to update the notification later on. */
        mNotificationManager.notify(notificationID_MultiLine, mBuilder.build());

    }
    /** ---------------------------------------------------------------------- **/
}