<?php 

// STEP 2: Login check to be done here
if ( !is_admin() || !modulesetting_get('location') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"location: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_location_save("ADD");
		break;
	case "save":
		do_location_save("UPDATE");
		break;
	case "remove":
		do_location_delete();
		break;
	case "remove_imei":
		do_location_imei_delete();
		break;
	case "save_imei_json":
		do_location_imei_save();
		break;
}

reload_form();
LOG_MSG('INFO',"location: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_location_view("ADD");
		break;
	case "remove":
		go_location_view("DELETE");
		break;
	case "remove_imei":
		go_location_view("DELETE IMEI");
		break;
	case "show":
		go_location_view("VIEW");
		break;
	case "modify":
		go_location_view("EDIT");
		break;
	case "list":
	default:
		go_location_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"location: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
