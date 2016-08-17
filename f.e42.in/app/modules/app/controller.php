<?php 

include("model.php");
include("db.php");
include("attendance_model.php");
include("attendance_db.php");
include("nfctag_db.php");
include("studentlog_db.php");
include("iisc_model.php");
include("iisc_db.php");
include("wallet_model.php");
include("wallet_db.php");
include("sync_model.php");
include("sync_db.php");
include("sync_to_server_model.php");
include("sync_to_server_db.php");
include("latlog.php");

LOG_MSG('INFO',"WALLET: CONTROLLER DO: $DO");
clear_msgs();

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "insert_fuel":
		do_fuel_save("ADD");
		break;
	case "save":
		do_fuel_save("UPDATE");
		break;
	case "remove":
		do_wallet_delete_json();
		break;
	case "student_register":
		do_student_register();
		break;
	case "wallet_save":
		do_wallet_save();
		break;
}

reload_form();
LOG_MSG('INFO',"WALLET: CONTROLLER GO: $GO");

// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {

	case "app_validate_vehicle":
		get_app_validate_vehicle_json();
		break;
	case "app_fuel_store":
	case "app_odometer_store":
		app_search_save_json();
		break;
	case "app_image_store":
			app_image_store();
			break;
	case "app_attendance_image_store":
			app_attendance_image_store();
			break;
	case "app_sync_image_store":
			app_attendance_sync_image_store();
			break;
	case "iisc_app_attendance_image_store":
			iisc_app_attendance_image_store();
			break;
	case "student_login":
		go_student_login();
		break;	
	case "iisc_login":
		go_iisc_login();
		break;	
	case "guardian_login":
		go_guardian_login();
		break;	
	case "studentlog_list":
		go_studentlog_list();
		break;
	case "sync_to_sqlite":
		go_sync_to_sqlite();
		break;
	case "iisc_sync":
		go_iisc_sync_to_sqlite();
		break;
	case "iisc_synctodb":
		go_iisc_sync_to_db();
		break;
	case "sync_to_db":
		go_sync_to_db();
		break;
	case "sync_attendance_log":
		go_sync_attendance_log_to_db();
		break;
	case "update_sync_to_sqlite":
		do_updatesync_to_sqlite();
		break;
	case "update_sqlite":
		go_update_sqlite();
		break;
	case "update_supervisor":
		go_update_supervisor();
		break;
	case "todays_summary":
		go_fuel_filled_summary();
		break;
	case "nfctag":
		go_nfctag_select();
		break;
	case "show_all":
		go_show_all_wallet();
		break;
	case "check_balance":
		go_check_balance();
		break;
	case "check_balance":
		go_send_data();
		break;
}

show_msgs();

LOG_MSG('INFO',"WALLET: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
