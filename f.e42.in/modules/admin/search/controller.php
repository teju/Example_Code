<?php 

// STEP 2: Login check to be done here
if ( !is_admin() ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	return;
}

include("model.php");
include("db.php");

LOG_MSG('INFO',"SEARCH: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_search_save("ADD");
		break;
	case "save":
		do_search_save("UPDATE");
		break;
	case "remove":
		do_search_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"SEARCH: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_search_view("ADD");
		break;
	case "remove":
		go_search_view("DELETE");
		break;
	case "show":
		go_search_view("VIEW");
		break;
	case "modify":
		go_search_view("EDIT");
		break;
	case "list":
	default:
		go_search_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"SEARCH: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
