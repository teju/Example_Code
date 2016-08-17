<?php 

// STEP 2: Login check to be done here
if ( !is_admin() && ! is_attendance() || !modulesetting_get('client')  ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}


include("model.php");
include("db.php");

LOG_MSG('INFO',"CLIENT: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_client_save("ADD");
		break;
	case "save":
		do_client_save("UPDATE");
		break;
	case "remove":
		do_client_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"CLIENT: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_client_view("ADD");
		break;
	case "remove":
		go_client_view("DELETE");
		break;
	case "show":
		go_client_view("VIEW");
		break;
	case "modify":
		go_client_view("EDIT");
		break;
	case "list":
	default:
		go_client_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"CLIENT: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
