package com.example.teju_chi.androidproject;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by Teju-Chi on 12/25/2015.
 */
public class DatabaseHelper extends SQLiteOpenHelper
{
    public static int database_version = 18;
    private static final String SQLUSER_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tuser " ;

    public String tuser =" CREATE TABLE `tuser` (user_id INTEGER PRIMARY KEY AUTOINCREMENT," +
            "user_name TEXT, email_id TEXT,password TEXT ); ";

    public DatabaseHelper(Context context) {
        super(context, "doctor_db", null, database_version);

    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL(tuser);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL(SQLUSER_DELETE_ENTRIES);
    }

    public void putUserInformation(DatabaseHelper dbh,String user_name,String email_id,String password){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("user_name",user_name);
        cv.put("email_id",email_id);
        cv.put("password",password);
        sq.insert("tuser",null,cv);
        Log.d("inserted",tuser);
    }

    public List<DataBase> getdbPassword(String email_id ) {
        Log.d("getAllStLocGroup ","hiiiii");

        List<DataBase> locationList = new ArrayList<DataBase>();
        String selectQuery = "SELECT email_id, password,user_name From tuser where email_id = '"+email_id+"'"  ;
        Log.d("getAllStLocGroup ",selectQuery);

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);
        // looping through all rows and adding to list
        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setEmailId(cursor.getString(0));
                contact.setpassword(cursor.getString(1));
                contact.setName(cursor.getString(2));
                locationList.add(contact);
            } while (cursor.moveToNext());
        }
        return locationList;
    }

    public List<DataBase> getAllEmail( ) {
        List<DataBase> locationList = new ArrayList<DataBase>();
        String selectQuery = "SELECT email_id From tuser "  ;

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);
        // looping through all rows and adding to list
        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setAllEmailId(cursor.getString(0));
                locationList.add(contact);
            } while (cursor.moveToNext());
        }
        return locationList;
    }
}
