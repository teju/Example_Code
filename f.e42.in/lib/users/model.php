<?php

/**********************************************************************/
/*                              LOGIN                                 */
/**********************************************************************/
// LOGIN
function do_login($auto_login=0) {

	global $GO;
	global $ROW;
	global $DOMAIN;

	// Are we already logged in?
	do_logout();

	// Auto login?
	if ( $auto_login ) {	// Happens after registration
		session_regenerate_id(true);
		
		$_SESSION['logged_in']=1;
		$_SESSION['user_id']=$auto_login;
		$_SESSION['name']=$ROW["name"];		// This is the row used for registration
		$_SESSION['email_id']=$ROW["email_id"];
	}
	else if ( get_arg($_POST,"lemail_id") && get_arg($_POST,"lpassword")) {
		// Get parameters
		$_email_id=get_arg($_POST,"lemail_id");
		$_password=get_arg($_POST,"lpassword");
		
		// Validate ALL parameters
		if (!validate("Email ID",$_email_id,5,100,"EMAIL") ||
			!validate("Password",$_password,5,100,"PASSWORD")) {
			add_msg('ERROR',"The email ID or password you entered is incorrect</br>");
			return;
		}
		##################################################
		#                  DB LOGIN                      #
		##################################################
		$ROW=db_do_login($_email_id,$_password,$DOMAIN);
		LOG_ARR("INFO","ROW",$ROW);
		if ( $ROW[0]['STATUS'] == "OK" && $ROW[0]["NROWS"] == 1) {

			session_regenerate_id(true);
			
			$_SESSION['email_id']=$_email_id;
			$_SESSION['logged_in']=1;
			$_SESSION['user_id']=$ROW[0]["user_id"];
			$_SESSION['name']=$ROW[0]["name"];
			$_SESSION['is_admin']=0;
			$_SESSION['is_supervisor']=0;
			$_SESSION['is_superuser']=0;
			$_SESSION['is_viewer']=0;
			$_SESSION['travel_id']=$ROW[0]["travel_id"];
			$_SESSION['domain']=$ROW[0]["domain"];
			$_SESSION['travel_name']=$ROW[0]["travel_name"];

			if ( $ROW[0]["type"] == "ADMIN" ) $_SESSION['is_admin']=1;
			if ( $ROW[0]["type"] == "VIEWER" ) $_SESSION['is_viewer']=1;
			if ( $ROW[0]["type"] == "SUPERVISOR" ) { 
				$_SESSION['is_supervisor']=1;
				$_SESSION['supervisor_id']=$ROW[0]["supervisor_id"];
			}
			if ( $ROW[0]["type"] == "SUPERUSER" ) { 
				$_SESSION['is_superuser']=1;
			}
			add_msg('SUCCESS',"Welcome ".$ROW[0]["name"]."! </br>");
		}
	}

	// logged_in will not be set if we failed anywhere above
	if ( !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] ) {
		add_msg('ERROR',"The email ID or password you entered is incorrect</br>");
	}
	LOG_ARR("INFO","SESSION",$_SESSION);
}

// LOGOUT
function do_logout() {

	LOG_MSG('INFO',"do_logout(): SESSION START");
	
	$_SESSION="";
	$_SESSION['is_admin']=0;
	$_SESSION['logged_in']="";
	$_SESSION['email_id']="";
	$_SESSION['user_id']="";
}

//IS LOGGEDIN?
function is_loggedin() {

	LOG_MSG('INFO',"is_loggedin(): SESSION START");
	
	return (isset($_SESSION["logged_in"]) && 
			$_SESSION['logged_in'] );
}

// IS ADMIN?
function is_admin() {

	LOG_MSG('INFO',"is_admin(): SESSION START");
	// both logged in and is employee check
	
	return (isset($_SESSION["logged_in"]) && 
			isset($_SESSION["is_admin"]) && 
			$_SESSION['logged_in'] && 
			$_SESSION['is_admin']);
}

function is_viewer() {

	LOG_MSG('INFO',"is_viewer(): SESSION START");
	// both logged in and is employee check
	
	return (isset($_SESSION["logged_in"]) && 
			isset($_SESSION["is_viewer"]) && 
			$_SESSION['logged_in'] && 
			$_SESSION['is_viewer']);
}

// IS SUPERVISOR?
function is_supervisor() {

	LOG_MSG('INFO',"is_supervisor(): SESSION START");
	// both logged in and is employee check
	
	return (isset($_SESSION["logged_in"]) && 
			isset($_SESSION["is_supervisor"]) && 
			$_SESSION['logged_in'] && 
			$_SESSION['is_supervisor']);
}

function is_superuser() {

	LOG_MSG('INFO',"is_supervisor(): SESSION START");
	// both logged in and is employee check
	
	return (isset($_SESSION["logged_in"]) && 
			isset($_SESSION["is_superuser"]) && 
			$_SESSION['logged_in'] && 
			$_SESSION['is_superuser']);
}



/**********************************************************************/
/*                      USER PERMISSION CHECK                         */
/**********************************************************************/
function has_user_permission($function,$mode="") {

	return true;

	$return=true;
	$user_id=get_arg($_SESSION,'user_id');	

		$user_permission_row=db_permission_select($user_id,$function,$mode);
		if ( $user_permission_row[0]['STATUS'] != 'OK' || $user_permission_row[0]['NROWS'] != 1 ) {
		add_msg("ERROR", "Sorry! You do not have the permission to perform this action.<br />");
		$return=false;
		}
	
	LOG_MSG("INFO","has_user_permission(): user_id=[$user_id] function=[$function] mode=[$mode] has_premission=[$return]");
	return $return;
}

?>
