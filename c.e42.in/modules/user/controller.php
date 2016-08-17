<?php 

// ORG ID is mandatory
if ( !is_org_set() ) {	
	add_msg("ERROR","Please select active organisation");
	show_msgs();
	return;
}

//include("model.php");
//include("db.php");

// Use IMEI
include("userimei/model.php");
include("userimei/db.php");

LOG_MSG('INFO',"USER: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_user_save("ADD");
		break;
	case "save":
		do_user_save("UPDATE");
		break;
	case "remove":
		do_user_delete_json();
		break;
	case "user_register":
		do_user_register();
		break;
	case "user_login":
		do_user_login();
		break;
	case "remove_image_json":
		do_user_remove_image_json();
		break;
	case "forgot_pass":
		do_user_forgot_pass_otp_json();
		break;
}

reload_form();
LOG_MSG('INFO',"USER: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "activate":
		do_user_activate();
		break;
	case "new":
		go_user_view("ADD");
		break;
	case "remove":
		go_user_view("DELETE");
		break;
	case "show":
		go_user_view("VIEW");
		break;
	case "modify":
		go_user_view("EDIT");
		break;
	case "list":
	default:
		go_user_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"USER: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
