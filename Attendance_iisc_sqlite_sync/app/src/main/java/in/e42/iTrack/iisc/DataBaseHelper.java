package in.e42.iTrack.iisc;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;

public class DataBaseHelper extends SQLiteOpenHelper {
    public static int database_version = 18;

    private static final String SQL_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tStudent " ;
    private static final String SQLIISCLOG_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tIISCLog " ;
    private static final String SQLSUPERVISOR_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tSupervisor " ;
    private static final String SQLNFCTAG_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tNFCTag " ;
    private static final String SQLSETTING_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tSetting " ;
    private static final String SQLATTENDANCELOG_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tAttendanceLog " ;
    private static final String SQLGROUP_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tGroup " ;
    private static final String SQLLOCATION_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tLocation " ;

    public String tStudent =" CREATE TABLE `tStudent` (student_id INTEGER UNIQUE  ,student_name TEXT, "+
            " id_number TEXT, phone TEXT, email_id TEXT, photo TEXT DEFAULT 'no_image.jpg', " +
            "travel_id INTEGER, is_active INTEGER,exp_dt Date ); ";
    public String tIISCLog = "CREATE TABLE tIISCLog (iisclog_id INTEGER  PRIMARY KEY AUTOINCREMENT," +
            " student_id INTEGER, location TEXT,image TEXT DEFAULT 'no_image.jpg', log_dt DATE, " +
            "travel_id INTEGER,is_sync INTEGER DEFAULT 0);";
    public String tSupervisor = "CREATE TABLE tSupervisor (supervisor_id INTEGER UNIQUE , imei TEXT , " +
            "travel_id INTEGER,is_active INTEGER);";
    public String tNFCTag = "CREATE TABLE tNFCTag (nfc_tag_id INTEGER UNIQUE ,id_number TEXT, type TEXT , " +
            "travel_id INTEGER );";
    public String tSetting = "CREATE TABLE tSetting (setting_id INTEGER UNIQUE , name TEXT , value TEXT, " +
            "travel_id INTEGER);";
    public String tAttendanceLog = "CREATE TABLE tAttendanceLog (attendancelog_id  INTEGER  PRIMARY KEY AUTOINCREMENT , imei TEXT ," +
            "nfc_tag_id TEXT , id_number TEXT, student_id INTEGER, latitude Double , longitude Double,address TEXT," +
            "comments TEXT, log_dt Date,travel_id INTEGER,is_sync INTEGER DEFAULT 0);";
    private static final String SQLIMEILOCATION_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tImeiLocation ";
    private static final String SQLSTLOCGROUP_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tStLocGroup ";
    private static final String SQLSTUDENTGROUP_DELETE_ENTRIES =
            "DROP TABLE IF EXISTS tStudentGroup ";
    public String tImeiLocation=" Create table tImeiLocation(location_id TEXT,imei TEXT," +
            "travel_id INTEGER)";
    public String tStLocGroup=" Create table tStLocGroup(stlocgrp_id INTEGER UNIQUE,location_id " +
            "INTEGER,group_id INTEGER, travel_id INTEGER)";
    public String tStudentGroup=" Create table tStudentGroup(stgrp_id INTEGER UNIQUE,student_id " +
            "INTEGER,group_id INTEGER, travel_id INTEGER)";
    public String tGroup=" Create table tGroup(group_id INTEGER UNIQUE,group_name TEXT,is_active " +
            "INTEGER, created_dt Date, travel_id INTEGER)";
    public String tLocation=" Create table tLocation(location_id INTEGER UNIQUE,location_name TEXT, " +
            "created_dt Date, travel_id INTEGER)";


    public DataBaseHelper(Context context) {
        super(context, "iisc_attendance",null, database_version);
        Log.d("database",tStudent);

    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL(tStudent);
        db.execSQL(tIISCLog);
        db.execSQL(tSupervisor);
        db.execSQL(tNFCTag);
        db.execSQL(tSetting);
        db.execSQL(tAttendanceLog);
        db.execSQL(tImeiLocation);
        db.execSQL(tStLocGroup);
        db.execSQL(tStudentGroup);
        db.execSQL(tGroup);
        db.execSQL(tLocation);
        Log.d("TABLE","TABLE created");
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL(SQL_DELETE_ENTRIES);
        db.execSQL(SQLIISCLOG_DELETE_ENTRIES);
        db.execSQL(SQLSUPERVISOR_DELETE_ENTRIES);
        db.execSQL(SQLNFCTAG_DELETE_ENTRIES);
        db.execSQL(SQLSETTING_DELETE_ENTRIES);
        db.execSQL(SQLATTENDANCELOG_DELETE_ENTRIES);
        db.execSQL(SQLIMEILOCATION_DELETE_ENTRIES);
        db.execSQL(SQLSTLOCGROUP_DELETE_ENTRIES);
        db.execSQL(SQLSTUDENTGROUP_DELETE_ENTRIES);
        db.execSQL(SQLLOCATION_DELETE_ENTRIES);
        db.execSQL(SQLGROUP_DELETE_ENTRIES);
        onCreate(db);
    }

    public void putStudentInformation(DataBaseHelper dbh,int student_id,String name,
                                      String id_number,String phone ,String email,String
                                      photo,int travel_id,int is_active,String exp_dt){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("student_name",name);
        cv.put("id_number",id_number);
        cv.put("phone",phone);
        cv.put("email_id",email);
        cv.put("photo",photo);
        cv.put("travel_id",travel_id);
        cv.put("student_id",student_id);
        cv.put("is_active",is_active);
        cv.put("exp_dt",exp_dt);
        sq.insert("tStudent",null,cv);
        Log.d("inserted",tStudent);
    }

    public void putIISCLogInfo(DataBaseHelper dbh,int student_id,String location,int travel_id,
                               String log_dt){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("student_id",student_id);
        cv.put("location",location);
        cv.put("travel_id",travel_id);
        cv.put("log_dt",log_dt);
        sq.insert("tIISCLog",null,cv);
        Log.d("tIISCLog1234",tIISCLog);
    }

    public void putAttendanceLogInfo(DataBaseHelper dbh,String imei,String nfc_tag_id,String id_number
            ,int student_id,double latitude,double longitude,String address, String comments,
                                  String log_dt,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("imei",imei);
        cv.put("nfc_tag_id",nfc_tag_id);
        cv.put("id_number",id_number);
        cv.put("student_id",student_id);
        cv.put("latitude",latitude);
        cv.put("longitude",longitude);
        cv.put("address",address);
        cv.put("comments",comments);
        cv.put("log_dt",log_dt);
        cv.put("travel_id",travel_id);
        sq.insert("tAttendanceLog",null,cv);
        Log.d("tAttendanceLog",tAttendanceLog);
    }

    public void putSettingInfo(DataBaseHelper dbh,int setting_id,String name,String value,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("setting_id",setting_id);
        cv.put("name",name);
        cv.put("value",value);
        cv.put("travel_id",travel_id);
        sq.insert("tSetting",null,cv);
        Log.d("tSetting",tSetting);
    }

    public void putNFCTagInfo(DataBaseHelper dbh,String nfc_tag_id,String id_number,String type,
                              int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("nfc_tag_id",nfc_tag_id);
        cv.put("id_number",id_number);
        cv.put("type",type);
        cv.put("travel_id",travel_id);
        sq.insert("tNFCTag",null,cv);
        Log.d("tNFCTag",tNFCTag);
    }

    public void putSupervisorInfo(DataBaseHelper dbh,int supervisor_id,String imei,
                                  int travel_id,int is_active){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("supervisor_id",supervisor_id);
        cv.put("imei",imei);
        cv.put("travel_id",travel_id);
        cv.put("is_active",is_active);
        sq.insert("tSupervisor",null,cv);
        Log.d("tSupervisor",tSupervisor);
    }

    public void putImeiLocationInfo(DataBaseHelper dbh,String location_id,String imei,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("location_id",location_id);
        cv.put("imei",imei);
        cv.put("travel_id",travel_id);
        sq.insert("tImeiLocation",null,cv);
        Log.d("tImeiLocationcreateinsert",tImeiLocation);
    }

    public void putStLocGroupInfo(DataBaseHelper dbh,String stlocgrp_id,String location_id,
                                  String group_id,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("stlocgrp_id",stlocgrp_id);
        cv.put("location_id",location_id);
        cv.put("group_id",group_id);
        cv.put("travel_id",travel_id);
        sq.insert("tStLocGroup",null,cv);
        Log.d("tStLocGroup1234",tStLocGroup);
    }

    public void putStudentGroupInfo(DataBaseHelper dbh,String stgrp_id,String student_id,
                                    String group_id,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("stgrp_id",stgrp_id);
        cv.put("student_id",student_id);
        cv.put("group_id",group_id);
        cv.put("travel_id",travel_id);
        sq.insert("tStudentGroup",null,cv);
        Log.d("tStudentGroup",tStudentGroup);
    }

    public void putGroupInfo(DataBaseHelper dbh,String group_id,String group_name,
                                    String is_active, String created_dt ,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("group_id",group_id);
        cv.put("group_name",group_name);
        cv.put("is_active",is_active);
        cv.put("created_dt",created_dt);
        cv.put("travel_id",travel_id);
        sq.insert("tGroup",null,cv);
        Log.d("tGroup",tGroup);
    }

    public void putLocationInfo(DataBaseHelper dbh,String location_id,String location_name,
                              String created_dt ,int travel_id){
        SQLiteDatabase sq=dbh.getWritableDatabase();
        ContentValues cv= new ContentValues();
        cv.put("location_id",location_id);
        cv.put("location_name",location_name);
        cv.put("created_dt",created_dt);
        cv.put("travel_id",travel_id);
        sq.insert("tLocation",null,cv);
        Log.d("tLocation",tLocation);
    }

    public int getContactsCount(String nfc_tag_id, int travel_id) {
        String countQuery = "SELECT n.nfc_tag_id FROM tStudent s LEFT OUTER JOIN tNFCTag n " +
                "ON(s.id_number=n.id_number AND s.travel_id=n.travel_id) WHERE n.nfc_tag_id = "+
                "'" + nfc_tag_id + "' AND s.travel_id = "+travel_id;
        Log.d("countQuery",countQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getStLocGroupCount(String stlocgrp_id, int travel_id) {
        String countlogQuery = "SELECT * FROM tStLocGroup WHERE stlocgrp_id  = " + stlocgrp_id +
                " AND travel_id = "+travel_id  ;
        Log.d("getStLocGroupCount",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getGroupCount(String group_id, int travel_id) {
        String countlogQuery = "SELECT * FROM tGroup WHERE group_id  = " + group_id +
                " AND travel_id = "+travel_id  ;
        Log.d("geGroupCount",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getLocationCount(String location_id, int travel_id) {
        String countlogQuery = "SELECT * FROM tLocation WHERE location_id  = " + location_id +
                " AND travel_id = "+travel_id  ;
        Log.d("getLocationCount",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getStudentGroupCount(String stgrp_id,int travel_id) {
        String countlogQuery = "SELECT * FROM tStudentGroup WHERE stgrp_id  = " + stgrp_id +
                " AND travel_id = "+travel_id  ;
        Log.d("countlogQuery",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getImeiLocationCount(String imei,int travel_id ) {
        String countlogQuery = "SELECT * FROM tImeiLocation where imei = '"+imei+"' " +
                "AND travel_id = "+travel_id  ;
        Log.d("countlogQuery",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public List<DataBase> getAllStLocGroup(String location_id,int travel_id ) {
        List<DataBase> locationList = new ArrayList<DataBase>();
        String selectQuery = "SELECT st.group_id,count(stlocgrp_id),g.group_name group_name  FROM " +
                "tStLocGroup st LEFT OUTER JOIN tGroup g ON(st.group_id=g.group_id AND " +
                "st.travel_id=g.travel_id) WHERE st.location_id = " +location_id +" " +
                "AND st.travel_id = "+travel_id  ;
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);
        System.out.println("getAllStLocGroup "+selectQuery);
        // looping through all rows and adding to list
        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setGroupId(cursor.getInt(0));
                contact.setGroupName(cursor.getString(2));
                //contact.setGroupIdcount(cursor.getString(1));
                locationList.add(contact);
            } while (cursor.moveToNext());
        }
        return locationList;
    }

    public List<DataBase> getAllStudentGroup(int student_id,int travel_id) {
        List<DataBase> locationList = new ArrayList<DataBase>();
        String selectQuery = "SELECT  sg.group_id, count(stgrp_id), g.group_name group_name FROM tStudentGroup sg  " +
                "LEFT OUTER JOIN tGroup g ON(sg.group_id=g.group_id AND sg.travel_id=g.travel_id) " +
                " WHERE student_id ="+student_id +" AND sg.travel_id = "+travel_id  ;
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);
        System.out.println("getAllStudentGroupselectquery" +selectQuery );

        // looping through all rows and adding to list
        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setStudentGroupId(cursor.getInt(0));
                contact.setStudentGroupName(cursor.getString(2));
                locationList.add(contact);
                System.out.println("locationList "+contact.toString());

            } while (cursor.moveToNext());
        }
        return locationList;
    }

    public int getStudentCount(int student_id,int travel_id) {
        String countQuery = "SELECT * FROM tStudent  WHERE travel_id ="+travel_id +
                " AND student_id  = " +student_id + " AND is_active =1" ;
        Log.d("countQuery",countQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getSupervisorCount(String imei,int travel_id ) {
        String countQuery = "SELECT * FROM tSupervisor WHERE travel_id ="+travel_id+" AND" +
                " imei = '" +imei + "' AND is_active = 1 ";
        Log.d("countQuery",countQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getNFCTagCount(String nfc_tag_id,int travel_id ) {
        String countQuery = "SELECT * FROM tNFCTag WHERE travel_id = "+travel_id +" AND  " +
                "nfc_tag_id = " + "'"+nfc_tag_id + "'";
        Log.d("countQueryNFCTag",countQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getSettingCount(int setting_id,int travel_id) {
        String countQuery = "SELECT * FROM tSetting WHERE travel_id ="+travel_id+
                " AND setting_id = " + "'"+setting_id + "'";
        Log.d("countQueryNFCTag",countQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public int getLogCount(int student_id,int travel_id) {
        String countlogQuery = "SELECT  student_id FROM tIISCLog WHERE travel_id ="+
                travel_id+" AND student_id = " + student_id  ;
        Log.d("countlogQuery",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        System.out.println("countlogQuery12345 "+count);
        cursor.close();
        return count;
    }

    public int getIISCCount(int student_id) {
        String countlogQuery = "SELECT  * FROM tIISCLog where student_id = "+student_id ;
        Log.d("countlogQuery",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        System.out.println("countlogQuery12345 "+count);
        cursor.close();
        return count;
    }

    public List<DataBase> getAllContacts(String nfc_tag_id,int travel_id) {
        List<DataBase> studentList = new ArrayList<DataBase>();
        String selectQuery = "SELECT s.student_id, s.student_name,s.id_number,s.phone, " +
                "s.email_id,s.photo,s.travel_id,s.is_active,s.exp_dt,count(s.student_id) FROM " +
                "tStudent s LEFT OUTER JOIN tNFCTag n ON(s.id_number=n.id_number AND s.travel_id = n.travel_id) where n.travel_id = "+travel_id+" AND n.nfc_tag_id = '"+ nfc_tag_id + "'";
        Log.d("getAllContacts",selectQuery);

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);

        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setStudentId(cursor.getInt(0));
                contact.setName(cursor.getString(1));
                contact.setIdNumber(cursor.getString(2));
                contact.setPhoneNumber(cursor.getString(3));
                contact.setEmail(cursor.getString(4));
                contact.setImage(cursor.getString(5));
                contact.setTravelId(cursor.getInt(6));
                contact.setStudentIsActive(cursor.getInt(7));
                contact.setExpDate(cursor.getString(8));
                studentList.add(contact);
            } while (cursor.moveToNext());
        }

        return studentList;
    }

    public List<DataBase> getSetting(int travel_id) {
        List<DataBase> settingList = new ArrayList<DataBase>();
        String selectQuery = "SELECT name , value,count(setting_id) From tSetting where travel_id="+travel_id;
        Log.d("getAllSetting",selectQuery);

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);

        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                //contact.setSettingName(cursor.getString(0));
                contact.setValue(cursor.getString(1));
                settingList.add(contact);
            } while (cursor.moveToNext());
        }
        return settingList;
    }

    public List<DataBase> getSettingSelect(int student_id, int attendance_image_capture_range,
                                           int travel_id) {
        List<DataBase> settingList = new ArrayList<DataBase>();
        String selectQuery = "SELECT student_id,image,count(iisclog_id) From tIISCLog where student_id" +
                "="+student_id +" AND travel_id = "+travel_id+" ORDER BY iisclog_id DESC LIMIT "
                +attendance_image_capture_range;
        Log.d("settingListSetting",selectQuery);

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);

        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                //contact.setSettingName(cursor.getString(0));
                contact.setSettingCheckInImage(cursor.getString(1));
                settingList.add(contact);
            } while (cursor.moveToNext());
        }
        return settingList;
    }

    public List<DataBase> getiiscLog(int student_id) {
        List<DataBase> studentlog = new ArrayList<DataBase>();
        String selectQuery = "SELECT iisclog_id, log_dt,count(iisclog_id) FROM tIISCLog Where student_id= " +
                student_id + " ORDER BY iisclog_id DESC limit 1";
        Log.e("iiscstudentList",selectQuery);
        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);
        if (cursor.moveToFirst()) {
            do {
                DataBase log = new DataBase();
                log.setiisclog_id(cursor.getString(0));
                log.setCreatedDate(cursor.getString(1));
                studentlog.add(log);
            } while (cursor.moveToNext());
        }
        int count =cursor.getCount();
        Log.d("getiisclogCount", selectQuery);
        return studentlog;
    }

    public void updateIISCLog (String image,String studentlog_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tIISCLog set image ='"+ image +"' where iisclog_id = "
                + studentlog_id + " AND travel_id = "+travel_id;
        Log.d("updateIISCLogquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updateIISCLogoncomplete","update is complete");
        database.close();

    }

    public List<DataBase> getSupervisor(String imei,int travel_id) {
        List<DataBase> supervisorList = new ArrayList<DataBase>();
        String selectQuery = "SELECT count(supervisor_id), is_active FROM tSupervisor  " +
                " WHERE travel_id = "+travel_id+" AND imei = '"+ imei +"'";

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);

        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setSupervisorIsActive(cursor.getInt(0));
                supervisorList.add(contact);
            } while (cursor.moveToNext());
        }
        Log.d("selectQuerySupervisorCount", selectQuery);
        return supervisorList;
    }

    public List<DataBase> getAllImeiLocationCount(String imei, int travel_id) {
        List<DataBase> locationList = new ArrayList<DataBase>();
        String selectQuery = "SELECT  i.location_id,count(imei),l.location_name location_name FROM " +
                "tImeiLocation i LEFT OUTER JOIN tLocation l ON(i.location_id=l.location_id AND " +
                "i.travel_id=l.travel_id)  WHERE imei = " + "'"+imei +"' AND i.travel_id = " +
                ""+travel_id;
        SQLiteDatabase db = this.getWritableDatabase();
        System.out.println("getAllImeiLocationCount "+selectQuery);
        Cursor cursor = db.rawQuery(selectQuery, null);

        // looping through all rows and adding to list
        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setImeiLocationId(cursor.getString(0));
                contact.setlocationName(cursor.getString(2));
                locationList.add(contact);
            } while (cursor.moveToNext());
        }
        return locationList;
    }

    public Object[] getAllIISCLogDetails() {
        ArrayList<HashMap<String, String>> logList;
        logList = new ArrayList<>();
        String selectQuery = "SELECT  * FROM tIISCLog  ";
        SQLiteDatabase database = this.getWritableDatabase();
        Cursor cursor = database.rawQuery(selectQuery, null);

            int count=0;
            if (cursor.moveToFirst()) {
                do {
                    HashMap<String, String> map = new HashMap<String, String>();
                    map.put("iisclog_id", cursor.getString(0));
                    map.put("student_id", cursor.getString(1));
                    map.put("location", cursor.getString(2));
                    map.put("image", cursor.getString(3));
                    map.put("log_dt", cursor.getString(4));
                    logList.add(count, map);
                    System.out.println("index1234 " + 0 + " map value " + map);

                    System.out.println("listing log for sync " + selectQuery);
                    count++;
                } while (cursor.moveToNext());
            }
        database.close();
        return logList.toArray();
    }
    public List<DataBase> getStudent(int student_id,int travel_id) {
        List<DataBase> studentList = new ArrayList<DataBase>();
        String selectQuery = "SELECT s.student_id, s.student_name,s.id_number,s.phone, " +
                "s.email_id,s.photo,s.travel_id,s.is_active,s.exp_dt,count(s.student_id) FROM " +
                "tStudent s  where s.travel_id = "+travel_id+" AND s.student_id = '"+ student_id + "'";

        SQLiteDatabase db = this.getWritableDatabase();
        Cursor cursor = db.rawQuery(selectQuery, null);

        if (cursor.moveToFirst()) {
            do {
                DataBase contact = new DataBase();
                contact.setStudentImage(cursor.getString(5));
                studentList.add(contact);
            } while (cursor.moveToNext());
        }
        Log.d("getAllContacts",selectQuery);
        return studentList;
    }

    public Object[] getAllAttendanceLogDetails() {
        ArrayList<HashMap<String, String>> logList;
        logList = new ArrayList<>();
        String selectQuery = "SELECT  * FROM tAttendanceLog where is_sync = 0 ";
        SQLiteDatabase database = this.getWritableDatabase();
        Cursor cursor = database.rawQuery(selectQuery, null);

        int count=0;
        if (cursor.moveToFirst()) {
            do {
                HashMap<String, String> map = new HashMap<String, String>();
                map.put("attendancelog_id", cursor.getString(0));
                map.put("imei", cursor.getString(1));
                map.put("nfc_tag_id", cursor.getString(2));
                map.put("id_number", cursor.getString(3));
                map.put("student_id", cursor.getString(4));
                map.put("latitude", cursor.getString(5));
                map.put("longitude", cursor.getString(6));
                map.put("address", cursor.getString(7));
                map.put("comments", cursor.getString(8));
                map.put("log_dt", cursor.getString(9));
                logList.add(count, map);
                System.out.println("index1234 " + 0 + " map value " + map);

                System.out.println("listing log for sync " + selectQuery);
                count++;
            } while (cursor.moveToNext());
        }
        database.close();
        return logList.toArray();
    }

    public void updateStudent (int student_id,String name,
                                     String id_number,String phone,
                                     String email_id, String student_photo,
                                     int travel_id,int is_active,String exp_dt) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tStudent set student_name = '"
                +name+"', id_number = '"+id_number + "' , phone = '"+ phone + "', email_id =' "
                +email_id+"', photo = '"+student_photo+ "', travel_id = "+
                travel_id+" ,is_active =" +is_active+"  ,exp_dt = '"+
                exp_dt+"'   where student_id="+ student_id +" AND travel_id = "+travel_id;
        Log.d("query",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updatedis_sync123456","update is complete");
        database.close();
    }

    public void updateSupervisor (int supervisor_id,String imei, int travel_id,int is_active) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tSupervisor set imei = '"
                +imei+"', travel_id = "+travel_id+ ",is_active ="+is_active+
                " where supervisor_id="+ supervisor_id +" " +
                "AND travel_id = "+travel_id;
        Log.d("supervisorquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("supervisorqueryqwere","update is complete");
        database.close();

    }

    public void updateSetting (int setting_id,String setting_name, String value,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tSetting set setting_id ="+ setting_id +" ,name = '"
                +setting_name+ "', value='"+value+"', travel_id = "+travel_id+"  where setting_id="
                + setting_id +" AND travel_id = "+travel_id;
        Log.d("settingquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updatedsetting_sync123456","update is complete");
        database.close();

    }

    public void updateNFCTag (String nfc_tag_id,String id_number, String type,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tNFCTag set nfc_tag_id ='"+ nfc_tag_id +"' ,id_number = '"
                +id_number+ "', type='"+type+"', travel_id = "+travel_id+"  where nfc_tag_id = '"
                + nfc_tag_id +"' AND travel_id = "+travel_id;
        Log.d("nfctagquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updatednfctag_sync123456","update is complete");
        database.close();

    }

    public void updateGroup (String group_id,String group_name, String is_active,String created_dt,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tGroup set group_id ='"+ group_id +"' ,group_name = '"
                +group_name+ "', is_active='"+is_active+"', created_dt = '"+created_dt+"', travel_id = "+travel_id+"  where group_id = '"
                + group_id +"' AND travel_id = "+travel_id;
        Log.d("Groupquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updatedGroup_sync123456","update is complete");
        database.close();

    }

    public void updateLocation (String location_id,String location_name, String created_dt,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tLocation set location_id ='"+ location_id +"' ,location_name = '"
                +location_name+ "', created_dt = '"+created_dt+"', travel_id = "+travel_id+"  where location_id = '"
                + location_id +"' AND travel_id = "+travel_id;
        Log.d("Locationquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updatedLocation123456","update is complete");
        database.close();

    }

    public void deleteStudent (String student_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tStudent  where student_id="+ student_id +" AND travel_id" +
                " = "+travel_id;
        Log.d("settingdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedsetting_sync123456","delete is complete");
        database.close();

    }

    public void deleteNFCTag (String nfc_tag_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tNFCTag  where nfc_tag_id = '"+ nfc_tag_id +"' AND travel_id " +
                "= "+travel_id;
        Log.d("NFCTagdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedNFCTag_sync123456","delete is complete");
        database.close();

    }
    public void deleteStLocGroup (String location_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tStLocGroup  where location_id = "+ location_id +" AND travel_id " +
                "= "+travel_id;
        Log.d("NFCTagdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedNFCTag_sync123456","delete is complete");
        database.close();

    }

    public void deleteStudentGroup (String group_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tStudentGroup  where group_id = "+ group_id +" AND travel_id " +
                "= "+travel_id;
        Log.d("deleteStudentGroup",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deleteStudentGroupsync123456","delete is complete");
        database.close();
    }

    public void deleteSupervisor (String supervisor_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tSupervisor where supervisor_id="+ supervisor_id +" " +
                "AND travel_id " +
                "= "+travel_id;
        Log.d("Supervisorgdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedSupervisor_sync123456","delete is complete");
        database.close();

    }

    public void deleteImeiSupervisor (String imei) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tSupervisor where imei= "+imei;
        Log.d("Supervisorgimeeideletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedimeiSupervisor_sync123456","delete is complete");
        database.close();
    }

    public void deleteImeiLocation (String imei,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tImeiLocation where imei='"+ imei +"' " +
                "AND travel_id " +
                "= "+travel_id;
        Log.d("ImeiLocationdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedImeiLocation123456","delete is complete");
        database.close();

    }

    public void deleteSetting (String setting_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tSetting  where setting_id="+ setting_id +" " +
                "AND travel_id " +
                "= "+travel_id;
        Log.d("setting_idgdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedsetting_id_sync123456","delete is complete");
        database.close();

    }

    public void deleteLocation (String location_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tLocation  where location_id="+ location_id +" " +
                "AND travel_id " +
                "= "+travel_id;
        Log.d("locationgdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedlocation_sync123456","delete is complete");
        database.close();

    }

    public void deleteGroup (String group_id,int travel_id ) {
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tGroup  where group_id="+ group_id +" " +
                "AND travel_id " +
                "= "+travel_id;
        Log.d("groupgdeletequery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedgroup_id_sync123456","delete is complete");
        database.close();

    }

    public int getCount() {
        String countlogQuery = "SELECT * FROM tIISCLog  ";

        Log.d("countlogQuery",countlogQuery);
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.rawQuery(countlogQuery, null);
        int count=0;
        count=cursor.getCount();
        cursor.close();
        return count;
    }

    public void updateSyncStatus(String id, int status){
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tIISCLog set is_sync ="+ status +"  where iisclog_id="+"'"+ id +"'";
        Log.d("query",updateQuery);
        database.execSQL(updateQuery);
        Log.d("updatedis_sync",Float.toString(status));
        database.close();
    }

    public void updateAttendanceLogSyncStatus(String id, int status){
        SQLiteDatabase database = this.getWritableDatabase();
        String updateQuery = "Update tAttendanceLog set is_sync ="+ status +"  where attendancelog_id="+"'"+ id +"'";
        Log.d("attendancelogquery",updateQuery);
        database.execSQL(updateQuery);
        Log.d("attendancelog_updatedis_sync",Float.toString(status));
        database.close();
    }

    public void deleteLog(){
        SQLiteDatabase database = this.getWritableDatabase();
        String deleteQuery = "Delete from tIISCLog where is_sync=1";
        Log.d("deleteQuery",deleteQuery);
        database.execSQL(deleteQuery);
        Log.d("deletedis_sync","deleted");
        database.close();
    }
}