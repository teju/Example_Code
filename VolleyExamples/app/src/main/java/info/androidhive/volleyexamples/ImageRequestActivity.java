package info.androidhive.volleyexamples;

import info.androidhive.volleyexamples.app.AppController;
import info.androidhive.volleyexamples.utils.Const;

import java.io.UnsupportedEncodingException;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;

import com.android.volley.Cache;
import com.android.volley.Cache.Entry;
import com.android.volley.toolbox.ImageLoader;
import com.android.volley.toolbox.NetworkImageView;

public class ImageRequestActivity extends Activity {

	private static final String TAG = ImageRequestActivity.class
			.getSimpleName();
	private Button btnImageReq;
	private NetworkImageView imgNetWorkView;
	private ImageView imageView;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_image);

		btnImageReq = (Button) findViewById(R.id.btnImageReq);
		imgNetWorkView = (NetworkImageView) findViewById(R.id.imgNetwork);
		imageView = (ImageView) findViewById(R.id.imgView);

		btnImageReq.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				makeImageRequest();
			}
		});
	}

	private void makeImageRequest() {
		ImageLoader imageLoader = AppController.getInstance().getImageLoader();
		imgNetWorkView.setImageUrl(Const.URL_IMAGE, imageLoader);

		imageLoader.get(Const.URL_IMAGE, ImageLoader.getImageListener(
				imageView, R.drawable.ico_loading, R.drawable.ico_error));
		
		Cache cache = AppController.getInstance().getRequestQueue().getCache();
		Entry entry = cache.get(Const.URL_IMAGE);
		if(entry != null){
			try {
				String data = new String(entry.data, "UTF-8");
				// handle data, like converting it to xml, json, bitmap etc.,
			} catch (UnsupportedEncodingException e) {		
				e.printStackTrace();
			}
		}

	}
}
