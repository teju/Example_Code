<?php 
/*if ( false || !modulesetting_get('supervisor') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}*/
// STEP 2: Login check to be done here

include("model.php");
include("db.php");

// USER
include("modules/admin/user/db.php");

LOG_MSG('INFO',"SUPERVISOR: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_supervisor_save("ADD");
		break;
	case "save":
		do_supervisor_save("UPDATE");
		break;
	case "remove":
		do_supervisor_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"SUPERVISOR: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_supervisor_view("ADD");
		break;
	case "remove":
		go_supervisor_view("DELETE");
		break;
	case "show":
		go_supervisor_view("VIEW");
		break;
	case "modify":
		go_supervisor_view("EDIT");
		break;
	case "list":
	default:
		go_supervisor_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"SUPERVISOR: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
