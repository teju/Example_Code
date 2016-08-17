<?php 

// STEP 2: Login check to be done here

include("model.php");
include("db.php");

LOG_MSG('INFO',"USERIMEI: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_userimei_save("ADD");
		break;
	case "save":
		do_userimei_save("UPDATE");
		break;
	case "remove":
		do_userimei_delete();
		break;
}

reload_form();
LOG_MSG('INFO',"USERIMEI: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_userimei_view("ADD");
		break;
	case "remove":
		go_userimei_view("DELETE");
		break;
	case "show":
		go_userimei_view("VIEW");
		break;
	case "modify":
		go_userimei_view("EDIT");
		break;
	case "list":
	default:
		go_userimei_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"USERIMEI: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
