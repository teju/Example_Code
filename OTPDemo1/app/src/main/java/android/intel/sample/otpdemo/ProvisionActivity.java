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

import java.util.HashMap;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.TimeoutException;

import android.app.Activity;
import android.app.ProgressDialog;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.widget.TextView;

import android.intel.sample.otpdemo.R;
import com.intel.android.ipt.wrapper.IPTWrapper;
import com.intel.android.ipt.wrapper.ProvisionResult;
import com.intel.host.ipt.iha.IhaException;

public class ProvisionActivity extends Activity {

	private static final String LOG_TAG = "OTP_DEMO";
	private static final String PROGRESS_BAR_MSG = "In progress...";
	private static final String HARDWARE_TYPE = "production";
	private static final int PROV_MAX_TIMEOUT = 40000;
	private TextView tvProvisioning = null;
	private ProgressDialog progressDialog = null;
	private String tokenInfo = OTPDemoActivity.TOTP_TOKEN_INFO;

	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.provision);

		tvProvisioning = (TextView) findViewById(R.id.PROV_MSG);
		progressDialog = new ProgressDialog(this);
		progressDialog.setCancelable(true);
		progressDialog.setMessage(PROGRESS_BAR_MSG);
		progressDialog.setProgressStyle(ProgressDialog.STYLE_SPINNER);
		progressDialog.setProgress(0);
		progressDialog.setMax(100);
		progressDialog.show();

		// Provision
		InvokeIPTProv ipt_obj = new InvokeIPTProv();
		ipt_obj.execute();
	}

	private class InvokeIPTProv extends AsyncTask<Void, Void, Void> {

		String error = "";

		@Override
		protected Void doInBackground(Void... params) {
			ChaabiProvision prov = new ChaabiProvision();
			try {
				prov.execute().get(PROV_MAX_TIMEOUT, TimeUnit.MILLISECONDS);
			} catch (InterruptedException e) {
				error = "Provisioning failed: " + e.getClass().getName() + ": "
						+ e.getLocalizedMessage();
				e.printStackTrace();
			} catch (ExecutionException e) {
				error = "Provisioning failed: " + e.getClass().getName() + ": "
						+ e.getLocalizedMessage();
				e.printStackTrace();
			} catch (TimeoutException e) {
				error = "Provisioning failed: " + e.getClass().getName() + ": "
						+ e.getLocalizedMessage();
				e.printStackTrace();
			}
			return null;
		}

		@Override
		protected void onPostExecute(Void res) {
			progressDialog.dismiss();
			if (!error.equalsIgnoreCase("")) {
				tvProvisioning.setText(error);
				Log.v(LOG_TAG, "Provisioning Failed.");
			}
		}
	}

	private class ChaabiProvision extends AsyncTask<Void, Void, Boolean> {

		ProvisionResult res_prov;
		String error;

		@Override
		protected Boolean doInBackground(Void... params) {
			String serverUrl = OTPDemoActivity.SERVER_URL + "tokens";
			String setAlgoUrl = null;

			HashMap<String, String> m1_params = new HashMap<String, String>();
			HashMap<String, String> m3_params = null;
			HashMap<String, String> set_algo_params = null;

			// Signing stuff for AWS
			SignatureResult sigObj = DigitalSigning.signProvision();
			m1_params.put("token_id", sigObj.token_id);
			m1_params.put("signature_b64", sigObj.signature_b64);
			m1_params.put("hardware_type", HARDWARE_TYPE);
			Log.v(LOG_TAG, "Provisioning Input: " + m1_params.toString());
			IPTWrapper prov = new IPTWrapper();
			try {
				res_prov = prov.ProvisionToken(serverUrl, m1_params, m3_params,
						setAlgoUrl, set_algo_params);
			} catch (IhaException e) {
				error = "Provisioning failed. Message: "
						+ e.getLocalizedMessage() + " Error code: "
						+ e.GetError();
				Log.v(LOG_TAG, error);
				return false;
			} catch (Exception e) {
				error = "Provisioning failed: " + e.getClass().getName() + ": "
						+ e.getLocalizedMessage();
				Log.v(LOG_TAG, error);
				return false;
			}
			if (OTPDemoActivity.selectOCRA.isChecked()) {
				tokenInfo = OTPDemoActivity.OCRA_TOKEN_INFO;
			}
			Log.v(LOG_TAG, "Provisioning Completed.");
			return true;
		}

		@Override
		protected void onPostExecute(Boolean res) {

			// Write to database
			if (res) {
				OtpDBHelper db = new OtpDBHelper(
						OTPDemoActivity.contextGlobal);
				db.addIPTData(new OTPData(OTPDemoActivity.tokenDBHandle,
						res_prov.tokenId, res_prov.token_b64, tokenInfo));
				Log.v(LOG_TAG,
						"Wrote token id, token and token info to database.");

				progressDialog.dismiss();
				tvProvisioning.setText("Provisioning status: Completed.");
				Log.v(LOG_TAG, "Provisioning Completed.");
			} else {
				progressDialog.dismiss();
				tvProvisioning.setText(error);
				Log.v(LOG_TAG, "Provisioning Failed.");
			}
		}
	}
}
