<?php 

// STEP 2: Login check to be done here
if ( !is_superuser() ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"travel: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_travel_save("ADD");
		break;
	case "save":
		do_travel_save("UPDATE");
		break;
	case "remove":
		do_travel_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"travel: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_travel_view("ADD");
		break;
	case "remove":
		go_travel_view("DELETE");
		break;
	case "show":
		go_travel_view("VIEW");
		break;
	case "modify":
		go_travel_view("EDIT");
		break;
	case "list":
	default:
		go_travel_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"travel: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
