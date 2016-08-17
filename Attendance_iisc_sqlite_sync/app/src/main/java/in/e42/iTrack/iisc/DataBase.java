package in.e42.iTrack.iisc;

import android.app.Activity;
import android.provider.BaseColumns;

/**
 * Created by CN92 on 6/19/2015.
 */
public class DataBase extends Activity {

    public static final String Database_name ="sam_student_database" ;

    public static  String Student_name ="student_name" ;
    public static  String Studentlog_id ="Studentlog_id" ;
    public static  String Id_Number ="id_number" ;
    public static  String Phone_No ="phone" ;
    public static  String Email ="email" ;
    public static String img ="photo";
    public static int student_id =0;
    public static int travel_id =0;
    public static int is_capture_image =0;
    public static String photo ="photo";
    private String value;
    private String image;
    private int sis_active;
    private int suis_active;
    private String exp_dt;
    private String imei_location_id;
    private int group_id;
    private int student_group_id;
    public static String created_dt ="created_dt";
    private String iisclog_id;
    private String count;
    private String sphoto;
    private String loc_name;
    private String group_name;
    private String sgroup_name;

    public DataBase()
    {

    }

    // Student details
    public String getStudentName(){return this.Student_name;}
    public void setName(String student_name){this.Student_name = student_name;}

    public String getIdNumber(){
        return this.Id_Number;
    }
    public void setIdNumber(String id_number){
        this.Id_Number = id_number;
    }

    public String getPhoneNumber(){
        return this.Phone_No;
    }
    public void setPhoneNumber(String phone){
        this.Phone_No = phone;
    }

    public String getEmail(){
        return this.Email;
    }
    public void setEmail(String email){
        this.Email = email;
    }

    public String getImage(){
        return this.img;
    }
    public void setImage(String img){
        this.img = img;
    }

    public int getStudentId(){
        return this.student_id;
    }
    public void setStudentId(int student_id){
        this.student_id = student_id;
    }

    public int getTravelId(){
        return this.travel_id;
    }
    public void setTravelId(int travel_id){
        this.travel_id = travel_id;
    }

    public int getIsCaptureImage(){
        return this.is_capture_image;
    }
    public void setIsCaptureImage(int is_capture_image){
        this.is_capture_image = is_capture_image;
    }

    public void setValue(String value) {
        this.value = value;
    }
    public String getValue(){
        return this.value;
    }

    public void setSettingCheckInImage(String image) {
        this.image = image;
    }
    public String getSettingCheckInImage(){
        return this.image;
    }

    public void setExpDate(String exp_dt) {
        this.exp_dt = exp_dt;
    }
    public String getExpDate(){
        return this.exp_dt;
    }

    public void setStudentIsActive(int sis_active) {
        this.sis_active = sis_active;
    }
    public int getStudentIsActive(){
        return this.sis_active;
    }

    public void setSupervisorIsActive(int suis_active) {
        this.suis_active = suis_active;
    }
    public int getSupervisorIsActive(){
        return this.suis_active;
    }

    public String getiisclog_id(){
        return this.iisclog_id;
    }
    public void setiisclog_id(String iisclog_id){
        this.iisclog_id = iisclog_id;
    }

    public int getGroupId(){
        return this.group_id;
    }
    public void setGroupId(int group_id){
        this.group_id = group_id;
    }

    public String getCreatedDate(){
        return this.created_dt;
    }
    public void setCreatedDate(String created_dt){
        this.created_dt = created_dt;
    }

    public int getStudentGroupId(){
        return this.student_group_id;
    }
    public void setStudentGroupId(int student_group_id){
        this.student_group_id = student_group_id;
    }

    public String getImeiLocationId(){
        return this.imei_location_id;
    }
    public void setImeiLocationId(String imei_location_id){
        this.imei_location_id = imei_location_id;
    }

    public void setStudentImage(String sphoto) {
        this.sphoto = sphoto;
    }
    public String getStudentImage(){
        return this.sphoto;
    }

    public String getlocationName(){
        return this.loc_name;
    }
    public void setlocationName(String loc_name){
        this.loc_name = loc_name;
    }

    public String getGroupName(){
        return this.group_name;
    }
    public void setGroupName(String group_name){
        this.group_name = group_name;
    }

    public String getStudentGroupName(){
        return this.sgroup_name;
    }
    public void setStudentGroupName(String sgroup_name){
        this.sgroup_name = sgroup_name;
    }
}
