<?php 

// org
include("model.php");
include("db.php");

LOG_MSG('INFO',"ORG: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_org_save("ADD");
		break;
	case "save":
		do_org_save("UPDATE");
		break;
	case "remove":
		do_org_delete_json();
		break;


	// certtag 
	case "certtag_add":
		do_certtag_save_json("ADD");
		break;
	case "certtag_remove":
		do_certtag_delete_json();
		break;
}

reload_form();
LOG_MSG('INFO',"org: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {

	// org
	case "verify":
		get_org_json();
		break;
	case "org_list":
		get_org_list_json();
		break;
	case "new":
		go_org_view("ADD");
		break;
	case "remove":
		go_org_view("DELETE");
		break;
	case "show":
		go_org_view("VIEW");
		break;
	case "modify":
		go_org_view("EDIT");
		break;
	case "list":
	default:
		go_org_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"org: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
