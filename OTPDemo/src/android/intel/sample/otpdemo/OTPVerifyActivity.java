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
import com.intel.android.ipt.wrapper.Base64Util;

public class OTPVerifyActivity extends Activity {

	private static final String LOG_TAG = "OTP_DEMO";
	private static final String PROGRESS_BAR_MSG = "In progress...";
	private static final int OTP_VERIFY_TIMEOUT = 10000;
	private static final int MAX_QUESTION_LEN = 128;
	private static final String VERIFY_OCRA_SIGN = "verify_ocra.json";
	private static final String VERIFY_TOTP_SIGN = "verify_totp.json";
	private TextView tvOTPVerify = null;
	private ProgressDialog progressDialog = null;
	private String PIN = null;
	private String token_id = null;
	private String encrToken_b64 = null;
	private String tokenInfo = null;
	private byte[] questionBytes = null;
	private boolean verifyResult = false;

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
		setContentView(R.layout.otp_verify);
		tvOTPVerify = (TextView) findViewById(R.id.OTP_VER_MSG);

		// Read token if exists
		if (!readData()) {
			tvOTPVerify
					.setText("OTP verification failed: No token exists. Please do provisioning.");
			return;
		}

		if (tokenInfo.equalsIgnoreCase(OTPDemoActivity.OCRA_TOKEN_INFO)
				&& !OTPDemoActivity.selectOCRA.isChecked()) {
			tvOTPVerify
					.setText("OTP verification failed: Please select OCRA option.");
			return;
		} else if (tokenInfo
				.equalsIgnoreCase(OTPDemoActivity.TOTP_TOKEN_INFO)
				&& OTPDemoActivity.selectOCRA.isChecked()) {
			tvOTPVerify
					.setText("OTP verification failed: Please uncheck OCRA option.");
			return;
		}

		progressDialog = new ProgressDialog(this);
		progressDialog.setCancelable(true);
		progressDialog.setMessage(PROGRESS_BAR_MSG);
		progressDialog.setProgressStyle(ProgressDialog.STYLE_SPINNER);
		progressDialog.setProgress(0);
		progressDialog.setMax(100);
		progressDialog.show();

		int questionLen = OTPDemoActivity.question.getText().toString()
				.trim().length();

		// Trim or pad question to a defined length
		questionBytes = new byte[MAX_QUESTION_LEN];
		System.arraycopy(OTPDemoActivity.question.getText().toString()
				.trim().getBytes(), 0, questionBytes, 0,
				questionLen < MAX_QUESTION_LEN ? questionLen : MAX_QUESTION_LEN);
		Log.v(LOG_TAG, "Question: "
				+ OTPDemoActivity.question.toString().trim());
		Log.v(LOG_TAG, "Question length: " + questionLen);
		Log.v(LOG_TAG, "Final question length: " + questionBytes.length);
		PIN = OTPDemoActivity.pin.getText().toString().trim();

		// Verify OTP
		InvokeIPTVerify ipt_obj = new InvokeIPTVerify();
		ipt_obj.execute();
	}

	private class InvokeIPTVerify extends AsyncTask<Void, Void, Void> {

		String error = "";

		@Override
		protected Void doInBackground(Void... params) {
			ChaabiOTPVerify otp_ver = new ChaabiOTPVerify();
			try {
				otp_ver.execute()
						.get(OTP_VERIFY_TIMEOUT, TimeUnit.MILLISECONDS);
			} catch (InterruptedException e) {
				error = "OTP verification failed: " + e.getClass().getName()
						+ ": " + e.getLocalizedMessage();
				e.printStackTrace();
			} catch (ExecutionException e) {
				error = "OTP verification failed: " + e.getClass().getName()
						+ ": " + e.getLocalizedMessage();
				e.printStackTrace();
			} catch (TimeoutException e) {
				error = "OTP verification failed: " + e.getClass().getName()
						+ ": " + e.getLocalizedMessage();
				e.printStackTrace();
			}
			return null;
		}

		@Override
		protected void onPostExecute(Void res) {
			progressDialog.dismiss();
			if (!error.equalsIgnoreCase("")) {
				tvOTPVerify.setText(error);
				Log.v(LOG_TAG, error);
			}
		}
	}

	/**
	 * Verified OTP
	 */
	private class ChaabiOTPVerify extends AsyncTask<Void, Void, Boolean> {
		String error = "";

		@Override
		protected Boolean doInBackground(Void... params) {
			if (OTPDemoActivity.OTP == null) {
				error = "OTP is null.";
				return false;
			} else if (token_id == null) {
				error = "token_id is null.";
				return false;
			}
			verifyResult = verifyOTP(token_id, OTPDemoActivity.OTP);
			return verifyResult;
		}

		@Override
		protected void onPostExecute(Boolean res) {
			progressDialog.dismiss();
			if (res) {
				Log.v(LOG_TAG, "OTP verification successful.");
				tvOTPVerify
						.setText("OTP Verification status: Completed sucessfully.");
			} else {
				Log.v(LOG_TAG, "OTP verification failed.");
				tvOTPVerify.setText("OTP verification failed: " + error);
			}
		}

		private boolean verifyOTP(String tokenId, byte[] otp) {
			boolean status = false;
			String otp_b64 = Base64Util.encode(otp);
			HttpClient client = new DefaultHttpClient();
			Log.v(LOG_TAG, "Starting HTTP post request for OATH verification.");
			String url = null;
			if (!OTPDemoActivity.selectOCRA.isChecked()) {
				url = OTPDemoActivity.SERVER_URL + "tokens/" + tokenId
						+ "/" + VERIFY_TOTP_SIGN;
			} else {
				url = OTPDemoActivity.SERVER_URL + "tokens/" + tokenId
						+ "/" + VERIFY_OCRA_SIGN;
			}
			HttpPost request = new HttpPost(url);
			String signature_b64 = "";
			JSONObject holder = new JSONObject();
			try {
				// Check for OCRA token
				if (OTPDemoActivity.selectOCRA.isChecked()) {
					String pin_b64;
					if (!PIN.equalsIgnoreCase("")) {
						pin_b64 = Base64Util.encode(PIN.getBytes());
					} else {
						pin_b64 = null;
					}
					holder.put("question_b64", Base64Util.encode(questionBytes));
					holder.put("developer_key_id",
							OTPDemoActivity.developer_key_id);
					if (pin_b64 != null) {
						holder.put("pin_b64", pin_b64);
					}
					holder.put("otp_b64", otp_b64);
					signature_b64 = DigitalSigning.signVerify(VERIFY_OCRA_SIGN,
							tokenId, otp_b64, Base64Util.encode(questionBytes),
							pin_b64);
				} else {
					holder.put("developer_key_id",
							OTPDemoActivity.developer_key_id);
					holder.put("otp_b64", otp_b64);
					signature_b64 = DigitalSigning.signVerify(VERIFY_TOTP_SIGN,
							tokenId, otp_b64, null, null);
				}
				Log.v(LOG_TAG, "HTTP Body (Verify): " + holder.toString());
				StringEntity se = null;
				se = new StringEntity(holder.toString());
				request.setEntity(se);
				request.setHeader("Accept", "application/json");
				request.setHeader("Content-type", "application/json");
				request.addHeader("X-IPDS-Signature", signature_b64);
				ResponseHandler<String> resp = new BasicResponseHandler();
				String respStr = null;
				Log.v(LOG_TAG,
						"Execute HTTP post request for OATH verification.");
				Log.v(LOG_TAG, "URL: " + url);
				respStr = client.execute(request, resp);
				Log.v(LOG_TAG, "Response: " + respStr);
				JSONObject outputHolder;
				outputHolder = new JSONObject(respStr);
				status = (Boolean) outputHolder.get("success");
				Log.v(LOG_TAG, "Status: " + status);
			} catch (JSONException e) {
				error = e.getClass().getName() + ": " + e.getLocalizedMessage();
				status = false;
				e.printStackTrace();
			} catch (UnsupportedEncodingException e) {
				error = e.getClass().getName() + ": " + e.getLocalizedMessage();
				status = false;
				e.printStackTrace();
			} catch (ClientProtocolException e) {
				error = e.getClass().getName() + ": " + e.getLocalizedMessage();
				status = false;
				e.printStackTrace();
			} catch (IOException e) {
				error = e.getClass().getName() + ": " + e.getLocalizedMessage();
				status = false;
				e.printStackTrace();
			}
			Log.v(LOG_TAG, "Return results: " + status);
			return status;
		}
	}
}
