package com.bhavin.horizontallistviewdemo;

import android.app.Activity;
import android.os.Bundle;
import android.widget.ArrayAdapter;

import com.bhavin.horizontallistview.HorizontalListView;
import com.example.horizontallistviewdemo.R;

public class MainActivity extends Activity {

	HorizontalListView hlvSimple, hlvCustom;

	private String[] mSimpleListValues = new String[] { "Android", "iPhone",
			"WindowsMobile", "Blackberry", "WebOS", "Ubuntu", "Windows7",
			"Max OS X", "Linux", "OS/2" };

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
		hlvSimple = (HorizontalListView) findViewById(R.id.hlvSimpleList);
		hlvCustom = (HorizontalListView) findViewById(R.id.hlvCustomList);
		setAdapter();
	}

	private void setAdapter() {
		ArrayAdapter<String> adapter = new ArrayAdapter<String>(this,
				android.R.layout.simple_list_item_2, android.R.id.text1,
				mSimpleListValues);
		hlvSimple.setAdapter(adapter);
		hlvCustom.setAdapter(new Adapter(getApplicationContext()));
	}
}
