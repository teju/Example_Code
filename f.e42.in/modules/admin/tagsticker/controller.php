<?php 

// STEP 2: Login check to be done here
if ( !is_admin() ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"TAGSTICKER: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_tagsticker_save("ADD");
		break;
	case "save":
		do_tagsticker_save("UPDATE");
		break;
	case "remove":
		do_tagsticker_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"TAGSTICKER: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_tagsticker_view("ADD");
		break;
	case "remove":
		go_tagsticker_view("DELETE");
		break;
	case "show":
		go_tagsticker_view("VIEW");
		break;
	case "modify":
		go_tagsticker_view("EDIT");
		break;
	case "list":
	default:
		go_tagsticker_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"TAGSTICKER: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
