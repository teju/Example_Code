<?php 

// STEP 2: Login check to be done here
if ( !is_admin() || !modulesetting_get('cleaner') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"cleaner: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_cleaner_save("ADD");
		break;
	case "save":
		do_cleaner_save("UPDATE");
		break;
	case "remove":
		do_cleaner_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"cleaner: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_cleaner_view("ADD");
		break;
	case "remove":
		go_cleaner_view("DELETE");
		break;
	case "show":
		go_cleaner_view("VIEW");
		break;
	case "modify":
		go_cleaner_view("EDIT");
		break;
	case "list":
	default:
		go_cleaner_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"cleaner: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
