<?php
/**********************************************************************/
/*                              LOGIN                                 */
/**********************************************************************/
// LOGIN
function do_login($auto_login=0) {
	global $GO;
	global $ROW;

	// Are we already logged in?
	do_logout();

	// Everybody goes home after login
	$GO="Home";

	// Auto login?
	if ( $auto_login ) {	// Happens after registration
		session_regenerate_id(true);
		$_SESSION['logged_in']=1;
		$_SESSION['user_id']=$auto_login;
		$_SESSION['fname']=$ROW["fname"];		// This is the row used for registration
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
		$ROW=db_do_login($_email_id,$_password);

		if ( $ROW[0]['STATUS'] == "OK" && $ROW[0]["NROWS"] == 1) {
			session_regenerate_id(true);
			$_SESSION['email_id']=$_email_id;
			$_SESSION['logged_in']=1;
			$_SESSION['user_id']=$ROW[0]["user_id"];
			$_SESSION['fname']=$ROW[0]["fname"];
			add_msg('SUCCESS',"Welcome ".$ROW[0]["fname"]."! </br>");
			if ( $ROW[0]["type"] == "E" ) {
				$_SESSION['employee']=1;
				// For employees store a backup of their details since they
				// switch roles often
				$_SESSION['e_user_id']=$ROW[0]["user_id"];
				$_SESSION['e_fname']=$ROW[0]["fname"];
				$_SESSION['e_email_id']=$_email_id;
			}
		}
	}

	// logged_in will not be set if we failed anywhere above
	if ( !isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] ) {
		add_msg('ERROR',"1The email ID or password you entered is incorrect</br>");
	}
}

// LOGOUT
function do_logout() {

	$_SESSION="";
	$_SESSION['employee']=0;
	$_SESSION['logged_in']="";
	$_SESSION['email_id']="";
	$_SESSION['user_id']="";
	$GO="Home";
}


// IS EMPLOYEE?
function is_employee() {
	// both logged in and is employee check
	return (isset($_SESSION["logged_in"]) && 
			isset($_SESSION["employee"]) && 
			$_SESSION['logged_in'] && 
			$_SESSION['employee']);
}
?>
