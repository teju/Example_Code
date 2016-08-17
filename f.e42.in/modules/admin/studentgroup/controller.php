<?php 

// STEP 2: Login check to be done here
if ( !is_admin() || !modulesetting_get('student_group') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"student_group: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_student_group_save("ADD");
		break;
	case "save":
		do_student_group_save("UPDATE");
		break;
	case "remove":
		do_student_group_delete();
		break;
	case "save_location_json":
		do_group_location_save();
		break;
	case "remove_location":
		do_group_location_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"student_group: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_student_group_view("ADD");
		break;
	case "remove":
		go_student_group_view("DELETE");
		break;
	case "show":
		go_student_group_view("VIEW");
		break;
	case "modify":
		go_student_group_view("EDIT");
		break;
	case "list":
	default:
		go_student_group_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"student_group: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
