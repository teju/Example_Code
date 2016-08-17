<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_user_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_user_list(): START GET=".print_r($_GET,true));

	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$photo=get_arg($_GET,"photo");
	$email_id=get_arg($_GET,"email_id");
	$password=get_arg($_GET,"password");
	$user_type=get_arg($_GET,"user_type");
	$is_active=get_arg($_GET,"is_active");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_user_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,100,"varchar") ||
		!validate("Photo",$photo,0,100,"varchar") ||
		!validate("Email Id",$email_id,0,100,"varchar") ||
		!validate("Password",$password,0,50,"varchar") ||
		!validate("User Type",$user_type,0,"'PUBLIC','LICENSED','ISSUER','ADMIN''","enum") ||
		!validate("Is Active",$is_active,0,1,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_user_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_user_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="
		name=$name&
		photo=$photo&
		email_id=$email_id&
		password=$password&
		user_type=$user_type&
		is_active=$is_active&
		created_dt=$created_dt	";


	$ROW=db_user_select(
		"",
			$name,
			$photo,
			$email_id,
			$password,
			"",
			$user_type,
			$is_active,
			$created_dt	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Users. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=user&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Users found! <br />Click on <strong>Add User</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_user_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_user_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_user_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the User ID
                $user_id=get_arg($_GET,"user_id");
		if ( $user_id == '' ) $user_id=$_SESSION['user_id'];

		// Validate the ID
		if ( !validate("User ID",$user_id,1,11,"int") ) { 
			LOG_MSG('ERROR',"go_user_view(): Invalid User ID [$user_id]!");
			return;
		}

		// Get from DB
		$ROW=db_user_select($user_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the User. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Users found! <br />Click on <strong>Add User</strong> to create a one.<br /><br />"); 
			return;
		}

		// Get IMEI from DB
		$userimei_row=db_userimei_select($user_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the User IMEI. Please try again later. <br/>");
			return;
		}
	}

	$disabled="";
	// Setup display parameters
	switch($mode) {
		case "ADD":
				if ( !isset($ROW[0]) ) {
					$ROW[0]=array();
					$ROW[0]['photo']='no_image.jpg';
				}
				$_do="add";
				break;
		case "EDIT":
				$_do="save";
				break;
		case "DELETE":
				$_do="remove";
				$disabled="disabled";
				break;
		case "VIEW":
		default:
				$disabled="disabled";
				break;
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_user_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_user_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_user_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_user_save(): START FILES=".print_r($_FILES,true));

	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$user_id=get_arg($_POST,"user_id");
	$name=get_arg($_POST,"name");
	$photo=get_arg($_POST,"photo");
	$email_id=get_arg($_POST,"email_id");
	$password=get_arg($_POST,"password");
	$user_type=get_arg($_POST,"user_type");
	$is_active=get_arg($_POST,"is_active") == 'on' ? 1 : 0;
	LOG_MSG('DEBUG',"do_user_save(): Got args");

	if ( $_FILES['photo']['name'] != '' ) {
		$temp_image=$_FILES['photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}

		if ( !upload_image("photo",IMG_DIR."org/".$_SESSION['org_id']."/user/$photo") ) {
			add_msg("ERROR","Error while uploading the image");
			return;
		}
	}

	// Validate parameters
	if (
		!validate("Name",$name,1,100,"varchar") ||
		!validate("Photo",$photo,0,100,"varchar") ||
		!validate("Email Id",$email_id,1,100,"varchar") ||
		!validate("Password",$password,0,50,"varchar") ||
		!validate("User Type",$user_type,1,"'PUBLIC','LICENSED','ISSUER','ADMIN'","enum") ||
		!validate("Is Active",$is_active,1,1,"tinyint") ){
		LOG_MSG('ERROR',"do_user_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_user_save(): Validated args");

	// Encrypt password
	$encr_password='';
	if ( $password != '' ) $encr_password=encrypt_pass($password);

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_user_insert(
								$name,
								$photo,
								$email_id,
								$encr_password,
								$user_type,
								$is_active);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The User <strong>$email_id</strong> is already in use. Please enter a different User<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the User <strong>$email_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_user_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New User <strong>$user_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate user_id
			if ( !validate("User Id",$user_id,1,11,"int") ) { 
				LOG_MSG('ERROR',"do_user_save(): Failed to validate PK");
				return;
			}

			$ROW=db_user_update(
								$user_id,
								$name,
								$photo,
								$email_id,
								$encr_password,
								$user_type,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the User <strong>$user_id</strong> .");
				return;
			}
			add_msg("SUCCESS","User <strong>$user_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_user_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_user_delete_json() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_user_delete_json(): START POST=".print_r($_POST,true));

	$user_id=get_arg($_POST,"id");

	// Validate user_id
	if ( !validate("User Id",$user_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_user_delete($user_id,$_SESSION['org_id']);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the Wallet';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_delete_json(): Delete row failed");
		exit;
	}

	$json_response['status']='OK';
	$json_response['id']=$user_id;

	//Send json response to response handler 
	echo json_encode($json_response);

	add_msg("SUCCESS","User <strong>$user_id</strong> has been removed.");
	LOG_MSG('INFO',"do_user_delete_json(): END");
	exit;

}

function do_user_register() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"********************* REGISTER NEW user ************************");
	LOG_MSG('INFO',"do_user_register(): START");

	LOG_ARR('INFO','GOT POST',$_POST);
	$name=get_arg($_POST,'name');
	$email_id=get_arg($_POST,'email_id');
	$password=get_arg($_POST,'password');

	// On ERROR return null name
	$json=array();
	$json['status']="ERROR";

	if (!validate("Your name",$name,3,50,'varchar') || 
		!validate("Email ID",$email_id,5,50,'EMAIL') ||
		!validate("Password",$password,5,50,'varchar') ){
		LOG_MSG('DEBUG',"do_user_register(): VALIDATE ARGS FAILED name:[$name] email_id:[$email_id] pass:[$password] !");
		$json['message']=$ERROR_MESSAGE;
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}
	LOG_MSG('INFO',"do_user_register(): Validated name:[$name] email_id:[$email_id] pass:[$password]");

	/******************************************************************/
	/* INSERT INTO DB                                                 */
	/******************************************************************/
	$is_active=0;
	$encr_password=encrypt_pass($password);

	db_transaction_start();
	$ROW=db_user_insert(
				$name,
				'no_image.jpg',
				$email_id,
				$encr_password,
				'PUBLIC',	// User Type
				$is_active);
	if ( $ROW['STATUS'] !== 'OK' ) {
		switch ($ROW["SQL_ERROR_CODE"]) {
			case 1062: // unique key
				add_msg("ERROR","You seem to already registered with us. Please login using your existing password.");
				break;
			default:
				add_msg("ERROR","There was an error registering you.".CONTACT_SUPPORT_MSG);
				break;
		}
		LOG_MSG('ERROR',"do_user_register(): Error registering new user name:[$name] email_id:[$email_id] pass:[$password] encr_password:[$encr_password]");
		$json['message']=$ERROR_MESSAGE;
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}
	$user_id=$ROW['INSERT_ID'];

	/******************************************************************/
	/* Send EMAIL
	/******************************************************************/
	do_user_send_activation_email($email_id,$name);

	// Success
	$json=array();
	$json['status']='OK';
	$json['message']="Thank you for registering. We have sent a mail to $email_id. Please click on the link in the mail to activate your account. If you don't see the mail in your inbox, please check your Spam folder";
	$encoded_resp=json_encode($json);

	LOG_MSG('INFO',"do_user_register(): END ");

	db_transaction_end();
	die($encoded_resp);
}

function do_user_send_activation_email($email_id,$name="") {

	$email_key=base64_encode(gen_hash($email_id,false));
	$activate_url=BASEURL."/activate/$email_id/$email_key";
	LOG_MSG('INFO',"do_user_send_activation_email(): email_key=[$email_key] activate_url=[$activate_url]");

	/******************************************************************/
	/* SEND EMAIL                                                     */
	/******************************************************************/
	$subject="Please confirm your email account";
	ob_start();
	include('emails/activate_account.html');
	$message = ob_get_contents();
	ob_get_clean();

	if (!send_email($email_id,SUPPORT_EMAIL_STR,'',SUPPORT_EMAIL,$subject,$message)) {
		LOG_MSG('DEBUG',"do_user_send_activation_email(): Failed to send email:\n subject=[$subject]");
		$json['message']="There was an error processing your request. Please try after sometime";
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}
	LOG_MSG('DEBUG',"do_user_send_activation_email(): Sent email");
}

function do_user_activate(){

	global $SHOP;

	LOG_MSG('INFO',"********************* ACTIVATE user ************************");
	LOG_MSG('INFO',"do_user_activate(): START");

	LOG_ARR('INFO','GET',$_GET);
	$email_id=get_arg($_GET,'email_id');
	$user_email_key=base64_decode(get_arg($_GET,'key'));
	$int_email_key=crypt(HASH_KEY.$email_id,$user_email_key);
	$is_matched=( $int_email_key === $user_email_key);

	LOG_MSG('INFO',"do_user_activate(): \nemail_id = [$email_id] \nuser_email_key = [$user_email_key] \nint_email_key =  [$int_email_key] \nis_matched = [$is_matched]");


	if ($is_matched) {
		$ROW=db_user_activate($email_id);
		if ( $ROW['STATUS'] !== 'OK' ) {
			clear_msgs();
			LOG_MSG('ERROR',"do_user_register(): Error activating new user name:[$email_id]");
			add_msg('ERROR',"There seems to be an error activating your account.<br/>".CONTACT_SUPPORT_MSG );
		} else {
			add_msg('SUCCESS','Congratulations! Your account has been activated.');
			do_user_login($email_id);
		}

		/******************************************************************/
		/* SEND EMAIL 2 user                                              */
		/******************************************************************/
		$subject="Your account is now active";
		ob_start();
		include('emails/account_activated.html');
		$message = ob_get_contents();
		ob_get_clean();

		if ( !send_email($email_id,SUPPORT_EMAIL_STR,'',SUPPORT_EMAIL,$subject,$message) ) {
			LOG_MSG('DEBUG',"do_user_activate(): Failed to send email: subject=[$subject]");
		}
		LOG_MSG('DEBUG',"do_user_activate(): Sent email 2 user");
	} else {
		add_msg('ERROR',"Invalid activation code! <br/>Please recheck your link or ".strtolower(CONTACT_SUPPORT_MSG));
		return;
	}

	// Auto login if activated successfully
	do_user_login($email_id);

	header('Location: '.BASEURL);

	return;

}

// auto_login : is the email ID to be used to 
// automatically login the user from a function 
// eg: do_user_activate(). 
function do_user_login($auto_login=false) {

	global $ERROR_MESSAGE;

	_user_logout();

	LOG_MSG('INFO',"*********************LOGGIN IN user ************************");
	LOG_MSG('INFO',"do_user_login(): START auto_login=[$auto_login]");
	if ( !$auto_login ) {
		LOG_ARR('INFO','GOT POST',$_POST);
		$email_id=get_arg($_POST,'email_id');
		$password=get_arg($_POST,'pass');
		$json=array();
		$json['status']="ERROR";
	} else {
		$email_id=$auto_login;
		$password="";
		$auto_login=true;
	}

	if (!validate("Email ID",$email_id,5,50,'EMAIL') || 
		(!$auto_login && !validate("Password",$password,1,50,"varchar")) ){
		LOG_MSG('DEBUG',"do_user_login(): VALIDATE ARGS FAILED email=[$email_id] pass=[$password] auto[$auto_login]!");
		if ( $auto_login) return false;
		$json['message']=$ERROR_MESSAGE;
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}
	LOG_MSG('DEBUG',"do_user_login(): Validated email[$email_id] pass[$password] auto[$auto_login]");

	/******************************************************************/
	/* FETCH FROM DB                                                  */
	/******************************************************************/
	$encr_password=encrypt_pass($password);
	$ROW=db_get_user($email_id,$encr_password,$auto_login);
	// Database error
	if ( $ROW[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_user_login(): Error logging in user with email_id=[$email_id] password=[$password] encr_password=[$encr_password] auto_login=[$auto_login].");
		if ( $auto_login) return false;
		$json['message']="Incorrect email ID/password. Please try again.";
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}

	// No user found
	if ( $ROW[0]['NROWS'] !== 1 ) {
		LOG_MSG('ERROR',"do_user_login(): No rows returned for email[$email_id] pass[$password] encr_password=[$encr_password] auto[$auto_login].");
		if ( $auto_login) return false;
		$json['message']="Incorrect email ID/password. Please try again.";
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}
	// user not active
	if ( $ROW[0]['is_active'] != 1 ) {
		LOG_MSG('ERROR',"do_user_login(): email_id [$email_id] is not active.");
		$json['message']="Your account has not been activated.<br/>Please activate using the link in your registration email. <br/><a href='".BASEURL."/show/contact-us'><b>Contact us to resend link</b></a>";
		$err_encoded_resp=json_encode($json);
		die($err_encoded_resp);
	}

	// Set Session cookie
	$_SESSION['logged_in']=true;
	$_SESSION['user_id']=$ROW[0]['user_id'];
	$_SESSION['name']=$ROW[0]['name'];
	$_SESSION['photo']=$ROW[0]['photo'];
	$_SESSION['email_id']=$ROW[0]['email_id'];
	$_SESSION['user_type']=$ROW[0]['user_type'];
	$_SESSION['org_name']=$ROW[0]['org_name'];
	$_SESSION['org_logo']=$ROW[0]['org_logo'];

	if ( $ROW[0]['org_id'] == "-1" ) $_SESSION['is_superuser']=1;
	else $_SESSION['org_id']=$ROW[0]['org_id'];

	// Autologin
	if ( $auto_login) return true;

	// Not auto login - return the user's name (required for login dialog)
	// and other details (required for checkout login)
	$json=$ROW[0];
	unset($json['STATUS']);
	unset($json['NROWS']);

	$json['status']='OK';
	$json['message']="Login successful.<br/>Please wait while we take you back....";
	$encoded_resp=json_encode($json);
	LOG_ARR('INFO','JSON response',$json);
	LOG_MSG('INFO',"do_user_login(): END ");
	die($encoded_resp);

}

function _user_logout() {
	unset($_SESSION['logged_in']);
	unset($_SESSION['is_superuser']);
	unset($_SESSION['name']);
	unset($_SESSION['email_id']);
	unset($_SESSION['photo']);
	unset($_SESSION['user_type']);
	unset($_SESSION['user_id']);
	unset($_SESSION['org_id']);
	unset($_SESSION['org_name']);
	unset($_SESSION['org_logo']);
}

function do_user_logout() {

	_user_logout();

	LOG_MSG('INFO',"do_user_logout(): END [SESSION]".print_r($_SESSION,true));
	add_msg('INFO','You have been logged out successfully');

	// Since Open ID login will refresh the page will call logout again
	// So refresh the page
	//header('Location: '.BASEURL);
	//go_home();
}

// IS LOGGEDIN?
function is_loggedin() {

	$is_loggedin=(isset($_SESSION["logged_in"]) && 
			$_SESSION['logged_in'] ) ? 1: 0;
	LOG_MSG('DEBUG',"is_loggedin(): [$is_loggedin]");
	return $is_loggedin;
}

// IS SUPERUSER?
function is_superuser() {

	// both logged in and is super user check
	$is_superuser=(isset($_SESSION["logged_in"]) && 
			isset($_SESSION["is_superuser"]) && 
			$_SESSION['logged_in'] && 
			$_SESSION['is_superuser']) ? 1 :0;
	LOG_MSG('DEBUG',"is_superuser(): [$is_superuser]");
	return $is_superuser;
}


/**********************************************************************/
/*                      USER PERMISSION CHECK                         */
/**********************************************************************/
function has_user_permission($function,$mode="",$record_shopactivity=true) {
	
	//if( is_superuser() ) 
	return true;	

	$return=true;
	$user_id=get_arg($_SESSION['admin'],'user_id');	//SNT0098: To prevent cross session issue with shop
	$email_id=get_arg($_SESSION['admin'],'email_id');
	$domain=get_arg($_SESSION['admin']['shop'],'domain');

	// Check the permission details of a user and retrieves the permission name
	$user_permission_row=db_permission_select($user_id,$function,$mode);
	if ( $user_permission_row[0]['STATUS'] != 'OK' || $user_permission_row[0]['NROWS'] != 1 ) {
		add_msg("ERROR", "Sorry! You do not have the permission to perform this action.");
		$return=false;
	}

	// Inserts the shop activity details into the database
	if( $record_shopactivity ) {
		if ( $mode != "" ) $permission_name=$user_permission_row[0]['perm_name'].'('.$mode.')';
		else $permission_name=$user_permission_row[0]['perm_name'];
		$useractivity_resp=db_useractivity_insert_update($domain,$email_id,$permission_name);
		if ( $useractivity_resp['STATUS'] != 'OK' ) {
			LOG_MSG('ERROR',"has_user_permission(): Error while inserting in User Activity for domain=[$domain], email_id=[$email_id] and activity=[$permission_name] ");
		}
	}
	
	LOG_MSG("INFO","has_user_permission(): user_id=[$user_id] function=[$function] mode=[$mode] has_premission=[$return] record_shopactivity=[$record_shopactivity]");
	return $return;
}

// Remove image json
function do_user_remove_image_json() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if ( !has_user_permission(__FUNCTION__) ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_user_remove_image_json(): START POST=".print_r($_POST,true));

	$user_id=get_arg($_POST,"user_id");
	$photo=get_arg($_POST,"photo");

	// Validate user_id
	if ( !validate("User Id",$user_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	db_transaction_start();

	// 1. Get the image name
	$user_row=db_user_select($user_id);
	if ( $user_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the image';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_remove_image_json(): There was an error fetching the user table");
		exit;
	}

	// 2. Update row
	$ROW=db_user_update($user_id,'','no_image.jpg','','','','',true);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the image';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_remove_image_json(): There was an error while updating the user table");
		exit;
	}

	// 3. Unlink the image
	if ( !unlink(IMG_DIR."org/".$_SESSION['org_id']."/user/".$user_row[0]['photo']) ) { 
		$json_response['message']='There was an error removing the image';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_remove_image_json(): Error while unlink the image from [".IMG_DIR."org/".$_SESSION['org_id']."/user/".$user_row[0]['photo']."]");
		exit;
	}

	db_transaction_end();

	$json_response['status']='OK';
	$json_response['id']=$user_id;

	//Send json response to response handler 
	echo json_encode($json_response);

	add_msg("SUCCESS","User image for <strong>$user_id</strong> has been removed.");
	LOG_MSG('INFO',"do_user_remove_image_json(): END");
	exit;

}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_user_forgot_pass_otp_json() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_user_forgot_pass_otp_json(): START POST=".print_r($_POST,true));

	$email_id=get_arg($_POST,"email_id");

	// Validate user_id
	if ( !validate("Email Id",$email_id,1,200,"EMAIL") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	$cust_otp=rand(100000,999999);

	// Store otp along with time 10 minutes from now 
	// to make sure the otp is valid for only 10 minutes
	$otp=$cust_otp.strtotime("+ 10 minute");

	$user_row=db_user_select("","","",$email_id);
	if ( $user_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error reseting password';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Error while fetching the user details");
		exit;
	}

	if ( $user_row[0]['NROWS'] == 0 ) {
		$json_response['message']="$email_id not yet registered. Please register";
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Error while fetching the user details");
		exit;
	}

	$resp=db_user_update($user_row[0]['user_id'],"","","","",$otp);
	if ( $resp['STATUS'] != "OK" ) {
		$json_response['message']='There was an error reseting the password. Please try later';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Delete row failed");
		exit;
	}

	/******************************************************************/
	/* SEND EMAIL                                                     */
	/******************************************************************/
	$subject=" Account - $cust_otp is your verification code for secure access";
	ob_start();
	include('emails/forgot_pass.html');
	$message = ob_get_contents();
	ob_get_clean();

	if (!send_email($email_id,SUPPORT_EMAIL_STR,'',SUPPORT_EMAIL,$subject,$message)) {
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Failed to send email:\n subject=[$subject]");
		$json_response['message']="There was an error processing your request. Please try after sometime";
		echo json_encode($json_response);
		exit;
	}

	$json_response['message']="Verification Code sent to $email_id";
	$json_response['status']='OK';

	//Send json response to response handler 
	echo json_encode($json_response);

	add_msg("SUCCESS","OTP updated success.");
	LOG_MSG('INFO',"do_user_forgot_pass_otp_json(): END");
	exit;

}


/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_user_verify_otp() {

	global $GO, $ROW, $ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	LOG_MSG('INFO',"do_user_verify_otp(): START POST=".print_r($_POST,true));

	$email_id=get_arg($_POST,"email_id");
	$otp=get_arg($_POST,"otp");
	$password=get_arg($_POST,"password");

	// Validate subscriber_id
	if (
		!validate("Email ID",$email_id,1,100,"EMAIL") ||  
		!validate("OTP",$otp,1,6,"int") ||  
		!validate("Password",$password,1,20,"varchar") ) { 
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			LOG_MSG('ERROR',"do_user_verify_otp(): Validate Arguments Failed! ".print_r($_POST,true));
			exit; 
	}

	$user_row=db_user_select("","","",$email_id);
	if ( $user_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error reseting password';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Error while fetching the user details");
		exit;
	}

	$otp_arr=str_split($user_row[0]['otp'],6);

	$valid_otp=$otp_arr[0];

	if ( $valid_otp != $otp ) {
		$json_response['message']='Verification Code is not valid';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Invalid verification code otp=[$otp]");
		exit;
	}

	if ( $otp.strtotime(date("Y-m-d H:i:s")) > $user_row[0]['otp'] ) {
		$json_response['message']='Verification Code has expired';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_forgot_pass_otp_json(): Verification code has expired otp=[".$otp.strtotime(date("Y-m-d H:i:s"))."]");
		LOG_ARR('ERROR',"do_user_forgot_pass_otp_json(): ROW",$user_row);
		exit;
	}

	// Encrypt the password
	$password=encrypt_pass($password);

	##################################################
	#                 DB UPDATE                      #
	##################################################
	$ROW=db_user_update($user_row[0]["user_id"],"","","",$password);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']="There was an error resetting password. Please contact customer care";
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_verify_otp(): Error while updating into tUser ".$json_response['message']);
		exit;
	}

	$json_response['status']='OK';
	$json_response['message']="Your New password has been reset successfully";

	// Send json response to response handler 
	echo json_encode($json_response);
	LOG_MSG('INFO',"do_user_verify_otp(): ".$json_response['message']);
	LOG_MSG('INFO',"do_user_verify_otp(): END");
	exit;

}



?>