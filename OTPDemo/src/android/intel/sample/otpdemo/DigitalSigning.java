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

import java.security.KeyFactory;
import java.security.MessageDigest;
import java.security.PrivateKey;
import java.security.Signature;
import java.security.spec.PKCS8EncodedKeySpec;

import org.apache.http.client.HttpClient;
import org.apache.http.client.ResponseHandler;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.BasicResponseHandler;
import org.apache.http.impl.client.DefaultHttpClient;
import org.json.JSONObject;

import com.intel.android.ipt.wrapper.Base64Util;

import android.util.Log;


public class DigitalSigning {

	private static final String LOG_TAG = "OTP_DEMO";

	public static SignatureResult signProvision() {
		SignatureResult sigObj = new SignatureResult();
		try {
			HttpClient client = new DefaultHttpClient();
			String url = OTPDemoActivity.SERVER_URL + "provision.json";
			Log.v(LOG_TAG, "URL: " + url);
			HttpPost request = new HttpPost(url);
			MessageDigest md = MessageDigest.getInstance("SHA-256");
			String request_data = "{\"developer_key_id\":\""
					+ OTPDemoActivity.developer_key_id + "\"}";
			md.update(request_data.getBytes());
			String content_sha256 = Base64Util.encode(md.digest());
			String signature_string = "verb=post|path=/ipt/otp/provision.json|content-type=application/json|content-sha256="
					+ content_sha256;
			Log.v(LOG_TAG, "Sign string: " + signature_string);
			String signature_b64 = DigitalSigning.signMessage(signature_string);
			Log.v(LOG_TAG, "Signed string: " + signature_b64);
			JSONObject holder = new JSONObject();
			holder.put("developer_key_id", OTPDemoActivity.developer_key_id);
			StringEntity se = null;
			se = new StringEntity(holder.toString());
			request.setEntity(se);
			request.setHeader("Accept", "application/json");
			request.setHeader("Content-type", "application/json");
			request.addHeader("X-IPDS-Signature", signature_b64);
			ResponseHandler<String> resp = new BasicResponseHandler();
			String respStr = null;
			Log.v(LOG_TAG,
					"Execute HTTP post request for Signing before provisioning.");
			respStr = client.execute(request, resp);
			Log.v(LOG_TAG, "Response: " + respStr);
			JSONObject outputHolder;
			outputHolder = new JSONObject(respStr);
			sigObj.token_id = outputHolder.getString("token_id");
			Log.v(LOG_TAG, "TOKEN ID: " + sigObj.token_id);
			sigObj.signature_b64 = DigitalSigning.signMessage("token_id="
					+ sigObj.token_id);
		} catch (Exception e) {
			e.printStackTrace();
		}
		return sigObj;
	}

	public static String signVerify(String urlToSign, String tokenId,
			String otp_b64, String question_b64, String pin_b64) {
		String signature_b64 = "";
		try {
			MessageDigest md = MessageDigest.getInstance("SHA-256");
			String request_data;
			if (question_b64 == null) {
				request_data = "{\"developer_key_id\":\""
						+ OTPDemoActivity.developer_key_id
						+ "\",\"otp_b64\":\"" + otp_b64 + "\"}";
			} else if (question_b64 != null && pin_b64 == null) {
				request_data = "{\"question_b64\":\"" + question_b64
						+ "\",\"developer_key_id\":\""
						+ OTPDemoActivity.developer_key_id
						+ "\",\"otp_b64\":\"" + otp_b64 + "\"}";
			} else {
				request_data = "{\"question_b64\":\"" + question_b64
						+ "\",\"developer_key_id\":\""
						+ OTPDemoActivity.developer_key_id
						+ "\",\"otp_b64\":\"" + otp_b64 + "\",\"pin_b64\":\""
						+ pin_b64 + "\"}";

			}
			Log.v(LOG_TAG, "Request data: " + request_data);
			md.update(request_data.getBytes());
			String content_sha256 = Base64Util.encode(md.digest());
			String signature_string = "verb=post|path=/ipt/otp/tokens/"
					+ tokenId + "/" + urlToSign
					+ "|content-type=application/json|content-sha256="
					+ content_sha256;
			Log.v(LOG_TAG, "Sign string: " + signature_string);
			signature_b64 = DigitalSigning.signMessage(signature_string);
			Log.v(LOG_TAG, "Signed string: " + signature_b64);
			Log.v(LOG_TAG, "Signed string: " + signature_b64);
		} catch (Exception e) {
			e.printStackTrace();
		}
		return signature_b64;
	}

	public static String signResync(String tokenId) {
		String signature_b64 = "";
		try {
			MessageDigest md = MessageDigest.getInstance("SHA-256");
			String request_data;
			request_data = "{\"developer_key_id\":\""
					+ OTPDemoActivity.developer_key_id + "\"}";
			Log.v(LOG_TAG, "Request data: " + request_data);
			md.update(request_data.getBytes());
			String content_sha256 = Base64Util.encode(md.digest());
			String signature_string = "verb=post|path=/ipt/otp/tokens/"
					+ tokenId
					+ "/resync.json|content-type=application/json|content-sha256="
					+ content_sha256;
			Log.v(LOG_TAG, "Sign string: " + signature_string);
			signature_b64 = DigitalSigning.signMessage(signature_string);
			Log.v(LOG_TAG, "Signed string: " + signature_b64);
			Log.v(LOG_TAG, "Signed string: " + signature_b64);
		} catch (Exception e) {
			e.printStackTrace();
		}
		return signature_b64;
	}

	private static String signMessage(String signature_string) {
		byte[] signature = null;
		try {
			byte[] msg = signature_string.getBytes();
			byte[] encoded = Base64Util.decode(OTPDemoActivity.private_key);
			PKCS8EncodedKeySpec keySpec = new PKCS8EncodedKeySpec(encoded);
			KeyFactory kf = KeyFactory.getInstance("RSA", "BC");
			PrivateKey privKey = kf.generatePrivate(keySpec);
			Signature instance = Signature.getInstance("SHA256withRSA");
			instance.initSign(privKey);
			instance.update(msg);
			signature = instance.sign();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return Base64Util.encode(signature);
	}
}

class SignatureResult {
	String token_id;
	String signature_b64;

	SignatureResult() {
		token_id = null;
		signature_b64 = null;
	}
}