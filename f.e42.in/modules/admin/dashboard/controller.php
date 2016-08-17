<?php


//Dashboard
include("model.php");
include("db.php");

LOG_MSG('INFO',"DASHBOARD: CONTROLLER START: GO=$GO");
switch($GO){
	case 'stock_alert':
		go_stock_alert_list();
		break;
	case 'upgrade_plan':
		go_upgrade_plan();
		break;
	case 'list':
	default:
		if ( DOMAIN == 'a.e42.in' || DOMAIN == 'iisc.e42.in' ) {
			go_attendance_list();
		}
		else {
			go_dashboard_list();
		}
	break;
} 

LOG_MSG('INFO',"DASHBOARD: CONTROLLER END: GO=[$GO] DO=[$DO]");

?>
