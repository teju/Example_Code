/* Copyright (c) 2013, Intel Corporation
*
* Redistribution and use in source and binary forms, with or without 
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice, 
*   this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright notice, 
*   this list of conditions and the following disclaimer in the documentation 
*   and/or other materials provided with the distribution.
* - Neither the name of Intel Corporation nor the names of its contributors 
*   may be used to endorse or promote products derived from this software 
*   without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
* POSSIBILITY OF SUCH DAMAGE.
*
*/

package android.intel.sample.otpdemo;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;

import android.intel.sample.otpdemo.R;
import com.intel.android.ipt.wrapper.IPTWrapper;
import com.intel.host.ipt.iha.IhaException;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.AssetManager;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.CompoundButton.OnCheckedChangeListener;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

public class OTPDemoActivity extends Activity {
	/** Called when the activity is first created. */
	
	private static final String LOG_TAG = "OTP_DEMO";
	private static final String DEVELOPER_KEY_ID_FILENAME = "developer_key_id.txt";
	private static final String PRIVATE_KEY_FILENAME = "private_key.txt";
	private static final String MSG_TITLE = "IPT Message";
	private static final String MSG_OK = "OK";
	private static final short SET_VISIBLE = 0;
	private static final short SET_INVISIBLE = 4;
	static final String SERVER_URL = "https://ipds.intel.com/ipt/otp/";
	static final String OCRA_TOKEN_INFO = "ocra";
	static final String TOTP_TOKEN_INFO = "non-ocra";
	static Context contextGlobal = null;
	static String developer_key_id = null;
	static String private_key = null;
	static CheckBox selectOCRA = null;
	static EditText question = null;
	static EditText pin = null;
	private Dialog dialog = null;
	static byte[] OTP = null;
	static int tokenDBHandle = 1;

	TextView labelQuestion = null;
	TextView labelPIN = null;
	TextView otpMessage = null;
	EditText tokenDBHandler;

	// Notify user
	public void notifyUser(String msg) {
		Toast toast = Toast.makeText(this, msg, Toast.LENGTH_LONG);
		toast.setGravity(Gravity.BOTTOM, 5, 10);
		toast.show();
	}

	// Dialog box
	@SuppressWarnings("deprecation")
	public void displayMessage(String message) {
		AlertDialog alertDialog = new AlertDialog.Builder(this).create();
		alertDialog.setTitle(MSG_TITLE);
		alertDialog.setMessage(message);
		alertDialog.setButton(MSG_OK, new DialogInterface.OnClickListener() {
			public void onClick(DialogInterface dialog, int which) {
				return;
			}
		});
		alertDialog.show();
	}

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.home);
		dialog = new Dialog(this);

        dialog.setContentView(R.layout.setup_dialog);
        dialog.setTitle("");

        Button loginButton=(Button)this.findViewById(R.id.login);
        Button setupButton=(Button)dialog.findViewById(R.id.setup);
        Button cancelButton=(Button)dialog.findViewById(R.id.cancel);
        TextView otpMessage = (TextView) findViewById(R.id.otpmessage);
        if (isOTPCapable()){
        	dialog.show();
        	otpMessage.setVisibility(SET_INVISIBLE);
        }else{
        	otpMessage.setVisibility(SET_VISIBLE);
        }
        
        TextView otptoken = (TextView) findViewById(R.id.otpLabel);
        otptoken.setVisibility(SET_INVISIBLE);
        
        loginButton.setOnClickListener(new Button.OnClickListener() {
 			public void onClick(View arg0) {
 				Intent i = new Intent(OTPDemoActivity.this,
 						LoginActivity.class);
 				startActivity(i);
 			}
 		});
        
 		setupButton.setOnClickListener(new Button.OnClickListener() {
 			public void onClick(View arg0) {
 				Intent i = new Intent(OTPDemoActivity.this,
 						ProvisionActivity.class);
 				startActivity(i);
 			}
 		});
 		
 		cancelButton.setOnClickListener(new Button.OnClickListener() {
 			public void onClick(View arg0) {

 				dialog.hide();
 			}
 		});
 		
		contextGlobal = getApplicationContext();

		// Initialize developer_key_id and private key
		AssetManager am = contextGlobal.getAssets();
		try {
			InputStream is = am.open(DEVELOPER_KEY_ID_FILENAME);
			BufferedReader br = new BufferedReader(new InputStreamReader(is));
			developer_key_id = br.readLine().trim();
			is.close();
			br.close();
			Log.v(LOG_TAG, "Developer_key_id: " + developer_key_id);

			is = am.open(PRIVATE_KEY_FILENAME);
			br = new BufferedReader(new InputStreamReader(is));
			String line = "";
			private_key = "";
			while ((line = br.readLine()) != null) {
				private_key += line.trim();
			}
			is.close();
			br.close();
			Log.v(LOG_TAG, "Private key: " + private_key);
		} catch (IOException e) {
			e.printStackTrace();
		}

	}	
	
	private void generateOTP(){
		if (OTPDemoActivity.selectOCRA.isChecked()) {
			if (question.getText().toString().trim()
					.equalsIgnoreCase("")) {
				notifyUser("Please enter a valid question.");
				return;
			}
		}

		if (tokenDBHandler.getText().toString().trim()
				.equalsIgnoreCase("")) {
			notifyUser("Please enter a valid token DB handle.");
			return;
		} else {
			tokenDBHandle = Integer.parseInt(tokenDBHandler.getText()
					.toString().trim());
		}

	}
	
	private void verifyOTP(){
		if (OTPDemoActivity.selectOCRA.isChecked()) {
			if (question.getText().toString().trim()
					.equalsIgnoreCase("")) {
				notifyUser("Please enter a valid question.");
				return;
			}
		}
		if (tokenDBHandler.getText().toString().trim()
				.equalsIgnoreCase("")) {
			notifyUser("Please enter a valid token DB handle.");
			return;
		} else {
			tokenDBHandle = Integer.parseInt(tokenDBHandler.getText()
					.toString().trim());
		}

	}
	
	private boolean isOTPCapable(){
		try {
			IPTWrapper caps = new IPTWrapper();
			String cap = caps.GetCapabilities();
			//displayMessage("Capabilities: " + cap);
			return true; 
		} catch (IhaException e) {
			String error = "GetCapabilities() failed. Message: "
					+ e.getLocalizedMessage() + " Error code: "
					+ e.GetError();
			//notifyUser("Failed: " + error);
			return false;
		} catch (Exception e) {
			String error = "GetCapabilities() failed: "
					+ e.getClass().getName() + ": "
					+ e.getLocalizedMessage();
			//notifyUser("Failed: " + error);
			return false;
		}
	}
}
