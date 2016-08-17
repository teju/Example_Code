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

import android.content.ContentValues;
import android.database.Cursor;
import android.util.Log;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.content.Context;

public class OtpDBHelper extends SQLiteOpenHelper {

	private static final String LOG_TAG = "OTP_Demo";
	private static int db_version = 1;
	private static String db_name = "ipt.db";
	private static String table_name = "iptdata";
	private static String kid = "id";
	private static String token_id = "token_id";
	private static String token = "token";
	private static String token_info = "token_info";

	public OtpDBHelper(Context context) {
		super(context, db_name, null, db_version);
	}

	@Override
	public void onCreate(SQLiteDatabase db) {
		String sqlstring = "CREATE TABLE " + table_name + "(" + kid
				+ " INTEGER PRIMARY KEY, " + token_id + " TEXT, " + token
				+ " TEXT, " + token_info + " TEXT);";
		db.execSQL(sqlstring);
	}

	@Override
	public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
		db.execSQL("DROP TABLE IF EXISTS " + table_name);
		onCreate(db);
	}


	void addIPTData(OTPData iptData) {
		SQLiteDatabase db = this.getWritableDatabase();
		Cursor cursor = db.query(table_name, new String[] { kid, token_id,
				token, token_info }, kid + "=?", new String[] { String
				.valueOf(OTPDemoActivity.tokenDBHandle) }, null, null,
				null, null);
		ContentValues values = new ContentValues();
		values.put(kid, iptData._id);
		values.put(token_id, iptData._tokenId);
		values.put(token, iptData._token);
		values.put(token_info, iptData._tokenInfo);
		if (cursor.moveToFirst()) {
			Log.v(LOG_TAG, "Update row.");
			db.update(table_name, values, "id=?", new String[] { String
					.valueOf(OTPDemoActivity.tokenDBHandle) });
		} else {
			Log.v(LOG_TAG, "Insert row.");
			db.insert(table_name, null, values);
		}
		db.close();
	}

	OTPData getIPTData(int id) {
		SQLiteDatabase db = this.getReadableDatabase();
		Cursor cursor = db.query(table_name, new String[] { kid, token_id,
				token, token_info }, id + "=?",
				new String[] { String.valueOf(id) }, null, null, null, null);

		if (cursor.moveToFirst()) {
			Log.v(LOG_TAG, "Read row.");
			OTPData iptdata = new OTPData(
					Integer.parseInt(cursor.getString(0)), cursor.getString(1),
					cursor.getString(2), cursor.getString(3));
			return iptdata;
		} else {
			Log.v(LOG_TAG, "Return null/no read.");
			return null;
		}
	}
}