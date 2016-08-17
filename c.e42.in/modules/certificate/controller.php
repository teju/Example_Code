<?php 

// ORG ID is mandatory
//if ( !is_org_set() ) { 
//	add_msg("ERROR","Please select active organisation");
//	show_msgs();
//	return;
//}

// certificate
include("model.php");
include("db.php");

// certtag
include("certtag/model.php");
include("certtag/db.php");

// tag
include("tag/db.php");
include("tag/model.php");


LOG_MSG('INFO',"CERTIFICATE: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_certificate_save("ADD");
		break;
	case "save":
		do_certificate_save("UPDATE");
		break;
	case "remove":
		do_certificate_delete_json();
		break;

	// certtag 
	case "certtag_add":
		do_certtag_save_json("ADD");
		break;
	case "certtag_remove":
		do_certtag_delete_json();
		break;

	// tag 
	case "tag_add":
		do_tag_save_json("ADD");
		break;
	case "tag_remove":
		do_tag_delete_json();
		break;
	case "tag_update":
		do_tag_update_json("UPDTE");
		break;

	case "req_report":
		do_request_report();
		break;

	case "send_email":
		go_app_send_email();
		break;
}

reload_form();
LOG_MSG('INFO',"certificate: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {

	// Tag
	case "tag_list":
		go_tag_list();
		break;

	// Certificate
	case "verify":
		get_certificate_json();
		break;
	case "123":
		get_certificate_json();
		break;
	case "certificate_list":
		get_certificate_list_json();
		break;
	case "new":
		go_certificate_view("ADD");
		break;
	case "remove":
		go_certificate_view("DELETE");
		break;
	case "show":
		go_certificate_view("VIEW");
		break;
	case "modify":
		go_certificate_view("EDIT");
		break;
	case "list":
	default:
		go_certificate_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"certificate: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
