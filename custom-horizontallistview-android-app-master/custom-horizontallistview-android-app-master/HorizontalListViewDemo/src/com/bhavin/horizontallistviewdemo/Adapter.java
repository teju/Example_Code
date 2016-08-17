package com.bhavin.horizontallistviewdemo;

import com.example.horizontallistviewdemo.R;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.ViewGroup.LayoutParams;
import android.widget.BaseAdapter;
import android.widget.TextView;

public class Adapter extends BaseAdapter {

	Context context;
	LayoutInflater inflater;
	private String[] mSimpleListValues = new String[] { "Android", "iPhone",
			"WindowsMobile", "Blackberry", "WebOS", "Ubuntu", "Windows7",
			"Max OS X", "Linux", "OS/2" };
	int count = 0;

	public Adapter(Context context) {
		this.context = context;
		inflater = (LayoutInflater) context
				.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
	}

	@Override
	public int getCount() {
		return mSimpleListValues.length;
	}

	@Override
	public Object getItem(int position) {
		return null;
	}

	@Override
	public long getItemId(int position) {
		return 0;
	}

	@Override
	public View getView(int position, View convertView, ViewGroup parent) {
		Holder holder;
		View view = convertView;
		if (view == null) {
			view = inflater.inflate(R.layout.dataset, null);
			holder = new Holder();
			holder.tvSSID = (TextView) view.findViewById(R.id.textView1);

			view.setTag(holder);
		} else {
			holder = (Holder) view.getTag();
		}
		holder.tvSSID.setText(mSimpleListValues[position]);
		view.setLayoutParams(new LayoutParams(135, 60));
		return view;
	}

	class Holder {
		TextView tvSSID;
	}
}
