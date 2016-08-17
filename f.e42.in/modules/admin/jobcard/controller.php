<?php 

// STEP 2: Login check to be done here
if ( !is_admin() || !modulesetting_get('jobcard') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"jobcard: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_jobcard_save("ADD");
		break;
	case "save":
		do_jobcard_save("UPDATE");
		break;
	case "remove":
		do_jobcard_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"jobcard: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_jobcard_view("ADD");
		break;
	case "remove":
		go_jobcard_view("DELETE");
		break;
	case "show":
		go_jobcard_view("VIEW");
		break;
	case "modify":
		go_jobcard_view("EDIT");
		break;
	case "list":
	default:
		go_jobcard_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"jobcard: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
