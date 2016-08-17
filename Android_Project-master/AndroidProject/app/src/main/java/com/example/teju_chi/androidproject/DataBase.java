package com.example.teju_chi.androidproject;

/**
 * Created by Teju-Chi on 12/25/2015.
 */
public class DataBase {

    private String password;
    private String emailId;
    private String allEmailId;
    private String name;

    public String getpassword(){return this.password;}
    public void setpassword(String password){this.password = password;}

    public void setEmailId(String emailId) {
        this.emailId = emailId;
    }
    public String getEmailId(){return this.emailId;}

    public void setAllEmailId(String allEmailId) {
        this.allEmailId = allEmailId;
    }    public String getAllEmailId(){return this.allEmailId;}

    public void setName(String name) {
        this.name = name;
    }
    public String getName(){return this.name;}

}
