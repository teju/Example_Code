package com.example.nz160.notifications;

import android.app.NotificationManager;
import android.content.Context;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.text.util.Linkify;
import android.widget.TextView;

public class NotificationActivity extends AppCompatActivity {

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.notification_activity);
		String message = "";
		int id = 0;

		Bundle extras = getIntent().getExtras();// get intent data
		if (extras == null) {
			// If it is null then show error
			message = "error";
		} else {
			// get id and message
			id = extras.getInt("notificationId");
			message = extras.getString("message");
		}
		TextView text = (TextView) findViewById(R.id.show_notificationmessage);

		message = "Notification Id : " + id + "\n Message : " + message;// Concat
																		// id
																		// and
																		// message
		text.setText(message);// set text
		Linkify.addLinks(text, Linkify.ALL);// Linkify textview for phone
											// numbers, url ,etc.
		NotificationManager myNotificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);

		// remove the notification with the specific id
		myNotificationManager.cancel(id);

	}
}