<?php 

// STEP 2: Login check to be done here
if ( !is_admin() || !modulesetting_get('driver') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"DRIVER: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_driver_save("ADD");
		break;
	case "save":
		do_driver_save("UPDATE");
		break;
	case "remove":
		do_driver_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"DRIVER: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_driver_view("ADD");
		break;
	case "remove":
		go_driver_view("DELETE");
		break;
	case "show":
		go_driver_view("VIEW");
		break;
	case "modify":
		go_driver_view("EDIT");
		break;
	case "list":
	default:
		go_driver_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"DRIVER: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
