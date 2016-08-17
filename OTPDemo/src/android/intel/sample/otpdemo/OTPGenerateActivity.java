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

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.TimeoutException;

import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.ResponseHandler;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.BasicResponseHandler;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.app.ProgressDialog;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.widget.TextView;

import android.intel.sample.otpdemo.R;
import com.intel.android.ipt.wrapper.IPTError;
import com.intel.android.ipt.wrapper.IPTWrapper;
import com.intel.android.ipt.wrapper.OTPResult;
import com.intel.host.ipt.iha.IhaException;

public class OTPGenerateActivity extends Activity {

	private static final String LOG_TAG = "OTP_DEMO";
	private static final String PROGRESS_BAR_MSG = "In progress...";
	private static final int SERVER_RESYNC_MSG_TIMEOUT = 5000;
	private static final int MAX_QUESTION_LEN = 128;
	private TextView tvOTPGenerate = null;
	private ProgressDialog progressDialog = null;
	private OTPResult otpRes = null;
	private String token_id = null;
	private String encrToken_b64 = null;
	private String serverResyncMessage = null;
	private String tokenInfo = null;
	private String PIN = null;
	private byte[] questionBytes = null;


	public boolean readData() {
		OtpDBHelper db = new OtpDBHelper(
				OTPDemoActivity.contextGlobal);

		OTPData iptdata = db.getIPTData(OTPDemoActivity.tokenDBHandle);
		if (iptdata != null) {
			token_id = iptdata.getSesionId();
			encrToken_b64 = iptdata.getToken();
			tokenInfo = iptdata.getTokenInfo();
			Log.v(LOG_TAG, "Read data.");
			Log.v(LOG_TAG, "DB Handle: " + OTPDemoActivity.tokenDBHandle);
			Log.v(LOG_TAG, "Token Id: " + token_id);
			Log.v(LOG_TAG, "Token: " + encrToken_b64);
			Log.v(LOG_TAG, "Token Info: " + tokenInfo);
			return true;
		} else {
			return false;
		}
	}

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.otp_gen);
		tvOTPGenerate = (TextView) findViewById(R.id.OTP_GEN_MSG);

		// Read token if exists
		if (!readData()) {
			tvOTPGenerate
					.setText("OTP generation failed: No token exists. Please do provisioning.");
			return;
		}

		if (tokenInfo.equalsIgnoreCase(OTPDemoActivity.OCRA_TOKEN_INFO)
				&& !OTPDemoActivity.selectOCRA.isChecked()) {
			tvOTPGenerate
					.setText("OTP generation failed: Please select OCRA option.");
			return;
		} else if (tokenInfo
				.equalsIgnoreCase(OTPDemoActivity.TOTP_TOKEN_INFO)
				&& OTPDemoActivity.selectOCRA.isChecked()) {
			tvOTPGenerate
					.setText("OTP generation failed: Please uncheck OCRA option.");
			return;
		}

		progressDialog = new ProgressDialog(this);
		progressDialog.setCancelable(true);
		progressDialog.setMessage(PROGRESS_BAR_MSG);
		progressDialog.setProgressStyle(ProgressDialog.STYLE_SPINNER);
		progressDialog.setProgress(0);
		progressDialog.setMax(100);
		progressDialog.show();

		// Invoke class library instance
		IPTWrapper obj = new IPTWrapper();
		try {

			// Check if token is of type OCRA
			if (tokenInfo.equalsIgnoreCase(OTPDemoActivity.OCRA_TOKEN_INFO)) {
				int questionLen = OTPDemoActivity.question.getText()
						.toString().trim().length();

				// Trim or pad question to a defined length
				questionBytes = new byte[MAX_QUESTION_LEN];
				System.arraycopy(OTPDemoActivity.question.getText()
						.toString().trim().getBytes(), 0, questionBytes, 0,
						questionLen < MAX_QUESTION_LEN ? questionLen
								: MAX_QUESTION_LEN);
				Log.v(LOG_TAG, "Question: "
						+ OTPDemoActivity.question.toString().trim());
				Log.v(LOG_TAG, "Question length: " + questionLen);
				PIN = OTPDemoActivity.pin.getText().toString().trim();
				if (PIN.equalsIgnoreCase("")) {
					PIN = null;
				}

				// Generate OCRA based OTP
				invokeGenerateOTP(obj, true);
			} else {

				// Generate TOTP
				invokeGenerateOTP(obj, false);
			}
			displayOTP();
			progressDialog.dismiss();
		} catch (IhaException e) {
			if (e.GetError() == IPTError.IPT_RET_E_RESYNC_REQUIRED) {
				Log.v(LOG_TAG, "Resync required.");
				if (!invokeResyncGenerateOTP(obj))
					return;
			} else if (e.GetError() == IPTError.IPT_RET_E_CLOCK_UNRECOVERABLE) {
				tvOTPGenerate
						.setText("OTP generation failed: Reprovisioning required.");
				progressDialog.dismiss();
				OTPDemoActivity.OTP = null;
				return;
			} else {
				tvOTPGenerate.setText("OTP generation failed. Message: "
						+ e.getLocalizedMessage() + " Error code: "
						+ e.GetError());
				progressDialog.dismiss();
				OTPDemoActivity.OTP = null;
			}
		} catch (Exception e) {
			String error = "OTP generation failed: " + e.getClass().getName()
					+ ": " + e.getLocalizedMessage();
			Log.v(LOG_TAG, error);
			tvOTPGenerate.setText(error);
			progressDialog.dismiss();
			OTPDemoActivity.OTP = null;
			return;
		}

	}

	private void invokeGenerateOTP(IPTWrapper obj, boolean ocra)
			throws IhaException, Exception {
		if (ocra) {
			otpRes = obj.GenerateOTP(encrToken_b64, questionBytes, PIN);
			OTPDemoActivity.OTP = otpRes.otp;
		} else {
			otpRes = obj.GenerateOTP(encrToken_b64);
			OTPDemoActivity.OTP = otpRes.otp;
		}
	}


	private void displayOTP() {
		if (OTPDemoActivity.OTP != null) {
			String otpStr = "";
			for (int k = 0; k < otpRes.otp.length; k++) {
				otpStr += ((char) otpRes.otp[k]);
			}
			tvOTPGenerate.setText("OTP generated: " + otpStr);

			// Check if token also updated during OTP generation and update
			// the database if needed
			if (otpRes.token_b64 != null) {
				Log.v(LOG_TAG, "Token modified during OTP generation.");
				Log.v(LOG_TAG, "Toke value: " + otpRes.token_b64);
				OtpDBHelper db = new OtpDBHelper(
						OTPDemoActivity.contextGlobal);
				db.addIPTData(new OTPData(OTPDemoActivity.tokenDBHandle,
						token_id, otpRes.token_b64, tokenInfo));
				Log.v(LOG_TAG, "Updated token with generated OTP.");
			} else {
				Log.v(LOG_TAG,
						"Token remained unchanged during OTP generation.");
			}
		} else {
			tvOTPGenerate.setText("OTP generation failed.");
		}
	}

	boolean invokeResyncGenerateOTP(IPTWrapper obj) {
		try {
			// Send request to the server for resync message and process
			// the received resync message
			InvokeIPTResync ipt_obj = new InvokeIPTResync();
			boolean status = ipt_obj.execute().get();
			if (status) {

				// Processes the server resync message
				obj.ProcessResyncMessage(encrToken_b64, serverResyncMessage);

				// Invoke OTP generation again
				// Check if token is of type OCRA
				if (tokenInfo
						.equalsIgnoreCase(OTPDemoActivity.OCRA_TOKEN_INFO)) {
					invokeGenerateOTP(obj, true);
				} else {
					invokeGenerateOTP(obj, false);
				}
				displayOTP();
				progressDialog.dismiss();
			} else {
				String error = "Receive server resync message failed.";
				tvOTPGenerate.setText(error);
				progressDialog.dismiss();
				OTPDemoActivity.OTP = null;
				return false;
			}
		} catch (IhaException e) {
			String error = "OTP generation failed. Message: "
					+ e.getLocalizedMessage() + " Error code: " + e.GetError();
			tvOTPGenerate.setText(error);
			progressDialog.dismiss();
			OTPDemoActivity.OTP = null;
			return false;
		} catch (Exception e) {
			String error = "OTP generation failed: " + e.getClass().getName()
					+ ": " + e.getLocalizedMessage();
			tvOTPGenerate.setText(error);
			progressDialog.dismiss();
			OTPDemoActivity.OTP = null;
			return false;
		}
		return true;
	}

	private class InvokeIPTResync extends AsyncTask<Void, Void, Boolean> {

		@Override
		protected Boolean doInBackground(Void... params) {
			boolean status = true;
			ChaabiResync resync_obj = new ChaabiResync();
			try {
				status = resync_obj.execute().get(SERVER_RESYNC_MSG_TIMEOUT,
						TimeUnit.MILLISECONDS);
			} catch (InterruptedException e) {
				status = false;
				e.printStackTrace();
			} catch (ExecutionException e) {
				status = false;
				e.printStackTrace();
			} catch (TimeoutException e) {
				status = false;
				e.printStackTrace();
			}
			return status;
		}

	}

	private class ChaabiResync extends AsyncTask<Void, Void, Boolean> {

		@Override
		protected Boolean doInBackground(Void... params) {
			Log.v(LOG_TAG, "Send the resync request to the server.");
			String url = OTPDemoActivity.SERVER_URL + "tokens/" + token_id
					+ "/resync.json";
			serverResyncMessage = getServerRsyncMessage(url, token_id);
			if (serverResyncMessage != null)
				return true;
			else
				return false;
		}

		@Override
		protected void onPostExecute(Boolean res) {
			if (res) {
				Log.v(LOG_TAG, "Received the resync message from the server.");
				Log.v(LOG_TAG, "Server Resync Message: " + serverResyncMessage);
			} else {
				Log.v(LOG_TAG,
						"Failed while receiving resync message from the server.");
			}
		}

		private String getServerRsyncMessage(String resyncURL, String tokenId) {
			String serverResyncMsg = "";
			boolean status = false;
			HttpClient client = new DefaultHttpClient();
			Log.v(LOG_TAG,
					"Starting HTTP post request for getting resync message from server.");
			Log.v(LOG_TAG, "URL: " + resyncURL);
			HttpPost request = new HttpPost(resyncURL);
			JSONObject holder = new JSONObject();
			String signature_b64 = "";
			try {
				holder.put("developer_key_id",
						OTPDemoActivity.developer_key_id);
				signature_b64 = DigitalSigning.signResync(tokenId);
				StringEntity se = null;
				se = new StringEntity(holder.toString());
				request.setEntity(se);
				request.setHeader("Accept", "application/json");
				request.setHeader("Content-type", "application/json");
				request.addHeader("X-IPDS-Signature", signature_b64);
				ResponseHandler<String> resp = new BasicResponseHandler();
				String respStr = null;
				Log.v(LOG_TAG, "Execute HTTP post request for resync message.");
				respStr = client.execute(request, resp);
				Log.v(LOG_TAG, "Response: " + respStr);
				JSONObject outputHolder;
				outputHolder = new JSONObject(respStr);
				status = (Boolean) outputHolder.get("success");
				Log.v(LOG_TAG, "Status: " + status);
				serverResyncMsg = outputHolder.getString("resync_data_b64");
				Log.v(LOG_TAG, "Server Resync Message: " + serverResyncMsg);
				if (status) {
					Log.v(LOG_TAG, "Response status: " + status);
				} else {
					Log.v(LOG_TAG, "Response status: Failed");
				}
			} catch (JSONException e) {
				serverResyncMsg = null;
				e.printStackTrace();
			} catch (UnsupportedEncodingException e) {
				serverResyncMsg = null;
				e.printStackTrace();
			} catch (ClientProtocolException e) {
				serverResyncMsg = null;
				e.printStackTrace();
			} catch (IOException e) {
				serverResyncMsg = null;
				e.printStackTrace();
			}
			return serverResyncMsg;
		}
	}

}
