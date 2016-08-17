<?php 

// STEP 2: Login check to be done here
if ( !is_admin()  || !modulesetting_get('settings') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"SETTING: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_setting_save("ADD");
		break;
	case "save":
		do_setting_save("UPDATE");
		break;
	case "remove":
		do_setting_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"SETTING: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_setting_view("ADD");
		break;
	case "remove":
		go_setting_view("DELETE");
		break;
	case "show":
		go_setting_view("VIEW");
		break;
	case "modify":
		go_setting_view("EDIT");
		break;
	case "list":
	default:
		go_setting_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"SETTING: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
