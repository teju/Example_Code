<?php 

// ORG ID is mandatory
if ( !is_org_set() ) {	
	add_msg("ERROR","Please select active organisation");
	show_msgs();
	return;
}

//include("user/userimei/db.php");
include("model.php");
include("db.php");

LOG_MSG('INFO',"WALLET: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "add":
		do_wallet_save("ADD");
		break;
	case "save":
		do_wallet_save("UPDATE");
		break;
	case "remove":
		do_wallet_delete_json();
		break;
}

reload_form();
LOG_MSG('INFO',"WALLET: CONTROLLER GO: $GO");


// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case "new":
		go_wallet_view("ADD");
		break;
	case "remove":
		go_wallet_view("DELETE");
		break;
	case "show":
		go_wallet_view("VIEW");
		break;
	case "modify":
		go_wallet_view("EDIT");
		break;
	case "insert":
		do_wallet_save_json();
		break;
	case "list":
	default:
		go_wallet_list();
		break;
}
show_msgs();

LOG_MSG('INFO',"WALLET: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
