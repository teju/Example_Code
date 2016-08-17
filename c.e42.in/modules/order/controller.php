<?php 

// order
include("model.php");
include("db.php");

LOG_MSG('INFO',"ORDER: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "make_payment":
		do_order_make_payment();
		break;
	case "add":
		do_order_save("ADD");
		break;
	case "save":
		do_order_save("UPDATE");
		break;
	case "remove":
		do_order_delete_json();
		break;
}

reload_form();
LOG_MSG('INFO',"ORDER: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {

	// order
	case "make_payment":
		go_order_make_payment();
		break;
	case "payment_confirm":
		go_order_payment_confirm();
		break;
	case "verify":
		get_order_json();
		break;
	case "order_list":
		get_order_list_json();
		break;
	case "new":
		go_order_view("ADD");
		break;
	case "remove":
		go_order_view("DELETE");
		break;
	case "show":
		go_order_view("VIEW");
		break;
	case "modify":
		go_order_view("EDIT");
		break;
	case "list":
	default:
		go_order_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"ORDER: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
