<?php 

// STEP 2: Login check to be done here
if ( !is_admin() || !modulesetting_get('student') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"student: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_student_save("ADD");
		break;
	case "save":
		do_student_save("UPDATE");
		break;
	case "remove":
		do_student_delete();
		break;
	case "save_group_location_json":
		do_group_location_save();
		break;
	case "remove_group":
		do_group_location_delete();
		break;
	case "remove_image":
		do_student_image_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"student: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_student_view("ADD");
		break;
	case "remove":
		go_student_view("DELETE");
		break;
	case "show":
		go_student_view("VIEW");
		break;
	case "modify":
		go_student_view("EDIT");
		break;
	case "list":
	default:
		go_student_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"student: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
