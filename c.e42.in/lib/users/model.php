<?php

/**********************************************************************/
/*                              LOGIN                                 */
/**********************************************************************/
// LOGIN
function do_login($auto_login=0) {

	global $GO, $ROW, $SAPNA_BRANCHES;

	// Are we already logged in?
	do_logout();

	// Auto login?
	if ( $auto_login ) {	// Happens after registration
		session_regenerate_id(true);

		$_SESSION['admin']['logged_in']=1;
		$_SESSION['admin']['user_id']=$auto_login;
		$_SESSION['admin']['fname']=$ROW["fname"];		// This is the row used for registration
		$_SESSION['admin']['email_id']=$ROW["email_id"];
		$_SESSION['admin']['created_dt']='2014-12-01';//$ROW["created_dt"];
	}
	else if ( get_arg($_POST,"lemail_id") && get_arg($_POST,"lpassword")) {
		// Get parameters
		$_email_id=get_arg($_POST,"lemail_id");
		$_password=get_arg($_POST,"lpassword");
		
		// Validate ALL parameters
		if (!validate("Email ID",$_email_id,5,100,"EMAIL") ||
			!validate("Password",$_password,5,100,"varchar")) {
			add_msg('ERROR',"The email ID or password you entered is incorrect</br>");
			return;
		}
		##################################################
		#                  DB LOGIN                      #
		##################################################
		$ROW=db_do_login($_email_id,$_password);
		LOG_ARR("INFO","ROW",$ROW);
		if ( $ROW[0]['STATUS'] == "OK" && $ROW[0]["NROWS"] == 1) {

			session_regenerate_id(true);
			//Get the Shop details here
			init_shop($ROW[0]['shop_id']);
			//SNT0098: To prevent cross session issue with shop
			$_SESSION['admin']['email_id']=$_email_id;
			$_SESSION['admin']['logged_in']=1;
			$_SESSION['admin']['user_id']=$ROW[0]["user_id"];
			$_SESSION['admin']['fname']=$ROW[0]["fname"];
			$_SESSION['admin']['domain']=$ROW[0]["domain"];
			$_SESSION['admin']['created_dt']=$ROW[0]["created_dt"];
			$_SESSION['admin']['is_admin']=0;
			$_SESSION['admin']['is_superuser']=0;
			$_SESSION['admin']['is_techsupport']=0;
			if ( $ROW[0]["type"] == "A" ) $_SESSION['admin']['is_admin']=1;
			elseif ( $ROW[0]["type"] == "S" ) $_SESSION['admin']['is_superuser']=1;
			elseif ( $ROW[0]["type"] == "T" ) { $_SESSION['admin']['is_techsupport']=1; $_SESSION['admin']['is_admin']=1;}
			add_msg('SUCCESS',"Welcome ".$ROW[0]["fname"]."! </br>");
		}
	}

	// logged_in will not be set if we failed anywhere above
	//SNT0098: To prevent cross session issue with shop	
	if ( !isset($_SESSION['admin']['logged_in']) || !$_SESSION['admin']['logged_in'] ) {
		add_msg('ERROR',"The email ID or password you entered is incorrect</br>");
	}
	LOG_ARR("INFO","SESSION",$_SESSION['admin']);
}

// LOGOUT
function do_logout() {

	$_SESSION['admin']['is_admin']=0;
	$_SESSION['admin']['logged_in']="";
	$_SESSION['admin']['email_id']="";
	$_SESSION['admin']['user_id']="";
	LOG_MSG('INFO',"do_logout(): Logged out user");
}

//IS LOGGEDIN?
function is_loggedin() {

	//SNT0098: To prevent cross session issue with shop
	$is_loggedin=(isset($_SESSION["logged_in"]) && 
			$_SESSION['logged_in'] ) ? 1: 0;
	LOG_MSG('DEBUG',"is_loggedin(): [$is_loggedin]");
	return $is_loggedin;
}

// IS ADMIN?
function is_admin() {

	// both logged in and is employee check
	//SNT0098: To prevent cross session issue with shop
	$is_admin=(isset($_SESSION['admin']["logged_in"]) && 
			isset($_SESSION['admin']["is_admin"]) && 
			$_SESSION['admin']['logged_in'] && 
			$_SESSION['admin']['is_admin'])? 1: 0;
	LOG_MSG('DEBUG',"is_admin(): [$is_admin]");
	return $is_admin;
}

// IS SUPERUSER?
function is_superuser() {

	// both logged in and is employee check
	//SNT0098: To prevent cross session issue with shop
	$is_su=(isset($_SESSION['admin']["logged_in"]) && 
			isset($_SESSION['admin']["is_superuser"]) && 
			$_SESSION['admin']['logged_in'] && 
			$_SESSION['admin']['is_superuser']) ? 1 :0;
	LOG_MSG('DEBUG',"is_superuser(): [$is_su]");
	return $is_su;
}



/**********************************************************************/
/*                      USER PERMISSION CHECK                         */
/**********************************************************************/
function has_user_permission($function,$mode="",$record_shopactivity=true) {
	
	if( is_superuser() ) return true;	

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


/**********************************************************************/
/*                       FORGOT PASSWORD                              */
/**********************************************************************/
function do_user_reset_password_json() {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"do_user_reset_password_json(): START");

	$json_response=array();
	$json_response['status']='ERROR';

	// Get POST
	$email_id=get_arg($_POST,"email_id");

	// Validate parameters as normal strings 
	if ( !validate("Email Id",$email_id,5,100,"EMAIL") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_user_reset_password_json(): VALIDATE ARGS FAILED! ".print_r($_POST,true));
		exit;
	} 
	LOG_MSG('INFO',"do_user_reset_password_json(): Validated args");


	$new_pass=gen_pass("CXXXXXX");
	LOG_MSG('INFO',"do_user_reset_password_json(): new_pass=[$new_pass]");

	$ROW=db_user_password_update($email_id,$new_pass);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']="There was an error processing your request. Please contact customer care.";
		echo json_encode($json_response);
		exit;
	}

	$json_response['status']='OK';
	// Nothing was updated 
	if ( $ROW['NROWS'] == 0 ) {
		echo json_encode($json_response);
		exit;
	}

	// Send Email
	ob_start();
	include('forget_password_mail.html');
	$message=ob_get_contents();		// Set mail content
	ob_get_clean();

	$subject="Forgot Password";	// Set subject
	send_email($email_id,EMAIL_FROM,'',EMAIL_BCC,$subject,$message);
	LOG_MSG('INFO'," do_user_reset_password_json(): STEP 3. Mail Sent to $email_id");

	echo json_encode($json_response);
	LOG_MSG('INFO',"do_user_reset_password_json(): END");
	exit;
}



/**********************************************************************/
/*                      INITIALIZE INTERCOM                           */
/**********************************************************************/
function init_intercom() {

	LOG_MSG('INFO',"init_intercom(): START");

	global $TEMPLATE;

	$INTERCOM_USER=array();
	$INTERCOM_SHOP=array();

	/********************************* INTERCOM_USER *********************************/
	$INTERCOM_USER['user_id']=$_SESSION['admin']['user_id'];
	$INTERCOM_USER['email']='"'.$_SESSION['admin']['email_id'].'"';
	$INTERCOM_USER['created_at']=isset($_SESSION['admin']['shop']['created_dt']) ? strtotime($_SESSION['admin']['shop']['created_dt']) : strtotime($_SESSION['admin']['created_dt']);
	$INTERCOM_USER['name']='"'.$_SESSION['admin']['fname'].'"';
	if ( get_arg($_SESSION['admin'],'mobile') != '' ) $INTERCOM_USER['mobile_no']=$_SESSION['admin']['mobile'];

	// User View Count
	$INTERCOM_USER['view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$_SESSION['admin']['email_id'].'" AND activity LIKE "%View %"');
	if ( $INTERCOM_USER['view_count'] === false ) {
		LOG_MSG('ERROR','Error while fetching the VIEW COUNT from Shop Activity for the user email_id=['.$_SESSION['admin']['email_id'].']');
		$INTERCOM_USER['view_count']=0;
	}
	if ( $INTERCOM_USER['view_count'] == '' ) $INTERCOM_USER['view_count']=0;

	// User Edit Count
	$INTERCOM_USER['edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$_SESSION['admin']['email_id'].'" AND (activity LIKE "% Add%" OR activity LIKE "% Modify%" OR activity LIKE "% Delete%")');
	if ( $INTERCOM_USER['edit_count'] === false ) {
		LOG_MSG('ERROR','Error while fetching the EDIT COUNT from Shop Activity for the user email_id=['.$_SESSION['admin']['email_id'].']');
		$INTERCOM_USER['edit_count']=0;
	}
	if ( $INTERCOM_USER['edit_count'] == '' ) $INTERCOM_USER['edit_count']=0;

	// Admin View Count
	$INTERCOM_USER['admin_view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$_SESSION['admin']['email_id'].'" AND activity LIKE "Admin > %View %"');
	if ( $INTERCOM_USER['admin_view_count'] === false ) {
		LOG_MSG('ERROR','Error while fetching the ADMIN VIEW COUNT from Shop Activity for the user email_id=['.$_SESSION['admin']['email_id'].']');
		$INTERCOM_USER['admin_view_count']=0;
	}
	if ( $INTERCOM_USER['admin_view_count'] == '' ) $INTERCOM_USER['admin_view_count']=0;

	// Admin Edit Count
	$INTERCOM_USER['admin_edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$_SESSION['admin']['email_id'].'" AND (activity LIKE "Admin > % Add%" OR activity LIKE "Admin > % Modify%" OR activity LIKE "Admin > % Delete%")');
	if ( $INTERCOM_USER['admin_edit_count'] === false ) {
		LOG_MSG('ERROR','Error while fetching the ADMIN EDIT COUNT from Shop Activity for the user email_id=['.$_SESSION['admin']['email_id'].']');
		$INTERCOM_USER['admin_edit_count']=0;
	}
	if ( $INTERCOM_USER['admin_edit_count'] == '' ) $INTERCOM_USER['admin_edit_count']=0;

	// Store View Count
	$INTERCOM_USER['store_view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$_SESSION['admin']['email_id'].'" AND activity LIKE "Store > %View %"');
	if ( $INTERCOM_USER['store_view_count'] === false ) {
		LOG_MSG('ERROR','Error while fetching the STORE VIEW COUNT from Shop Activity for the user email_id=['.$_SESSION['admin']['email_id'].']');
		$INTERCOM_USER['store_view_count']=0;
	}
	if ( $INTERCOM_USER['store_view_count'] == '' ) $INTERCOM_USER['store_view_count']=0;

	// Store Edit Count
	$INTERCOM_USER['store_edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$_SESSION['admin']['email_id'].'" AND (activity LIKE "Store > % Add%" OR activity LIKE "Store > % Modify%" OR activity LIKE "Store > % Delete%")');
	if ( $INTERCOM_USER['store_edit_count'] === false ) {
		LOG_MSG('ERROR','Error while fetching the STORE EDIT COUNT from Shop Activity for the user email_id=['.$_SESSION['admin']['email_id'].']');
		$INTERCOM_USER['store_edit_count']=0;
	}
	if ( $INTERCOM_USER['store_edit_count'] == '' ) $INTERCOM_USER['store_edit_count']=0;

	// User hash
	$INTERCOM_USER['user_hash']='"'.hash_hmac("sha256", $_SESSION['admin']['user_id'], "JQE2ljUzKL7wMKAXrNBthccmevDyhZOFKrSoBRF7").'"';

	// App ID
	$INTERCOM_USER['app_id']='"e5d8196714cf0bf945bd764be170dd41c22dc998"';



	/********************************* INTERCOM_SHOP *********************************/
	$INTERCOM_SHOP['id']=SHOP_ID;
	$INTERCOM_SHOP['name']='"'.SHOP_DOMAIN.'"';
	$INTERCOM_SHOP['created_at']==isset($_SESSION['admin']['shop']['created_dt']) ? strtotime($_SESSION['admin']['shop']['created_dt']) : strtotime($_SESSION['admin']['created_dt']);
	$INTERCOM_SHOP['subscription_end_dt']=strtotime(shopsetting_get('subscription_end_date')." 23:59:59");
	$INTERCOM_SHOP['subs_reminder_dt']=strtotime(date("Y-m-d",strtotime(shopsetting_get('subscription_end_date')." -30 day"))." 23:59:59");
	//$INTERCOM_SHOP['subscription_end_dt']=strtotime(date('Y-m-d H:i:s', strtotime(shopsetting_get('subscription_end_date').' 00:00:00'. ' + 1 day')));

	//Store Revenue
	$INTERCOM_SHOP['revenue']=db_get_list('LIST','round(sum(net_total))','tOrder','payment_status="PAID" and shop_id='.SHOP_ID);
	$INTERCOM_SHOP['monthly_revenue']=db_get_list('LIST','round(sum(net_total))','tOrder','payment_status="PAID" and order_placed_dt > DATE_SUB(NOW(), INTERVAL 1 MONTH) and shop_id='.SHOP_ID);
	if ($INTERCOM_SHOP['revenue'] == '') $INTERCOM_SHOP['revenue']=0;
	if ($INTERCOM_SHOP['monthly_revenue'] == '') $INTERCOM_SHOP['monthly_revenue']=0;



	// Status
	# subscription will end within 15 days of creating
	# the shop, then he is still in trial
	if ( shopsetting_isset('subscription_is_trial') ) $INTERCOM_SHOP['status']='"TRIAL"';
	elseif ( shopsetting_isset('subscription_is_renewed') ) $INTERCOM_SHOP['status']='"RENEWED"';
	else $INTERCOM_SHOP['status']='"PAID"';

	// Plan
	if ( shopsetting_isset('subscription_is_trial') ) {
		$INTERCOM_SHOP['plan']='"FREE"';
	} else {
		switch ( shopsetting_get('max_no_of_products') ) {
			case 500: 
				$INTERCOM_SHOP['plan']='"QUICKY"'; 
				break;
			case 2000: 
				$INTERCOM_SHOP['plan']='"STARTER"';
				break;
			case 10000: 
				$INTERCOM_SHOP['plan']='"PRO"';
				break;
			case 25000: 
				$INTERCOM_SHOP['plan']='"PREMIUM"';
				break;
			default: $INTERCOM_SHOP['plan']='"CUSTOM"';
				break;
		}
	}

	// Subs Status
	$INTERCOM_SHOP['subs_status']=strtotime(shopsetting_get('subscription_end_date')) > strtotime(date('Y-m-d')) ? '"ACTIVE"' : '"EXPIRED"';

	// Expires In
	$INTERCOM_SHOP['expires_in']=(strtotime(shopsetting_get('subscription_end_date'))-strtotime(date('Y-m-d')))/(60*60*24);

	if ( isset( $TEMPLATE ) ) $template=$TEMPLATE; else $template=TEMPLATE_DIR."intercom.html"; 
	include($template); 

	LOG_MSG('INFO',"init_intercom(): END");

}
?>
