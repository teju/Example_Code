<?php 
if( !is_admin() || !is_viewer() ) {
	if ( !modulesetting_get('log_report') && 
		!modulesetting_get('vehicle_report') &&
		!modulesetting_get('profitability_report') &&
		!modulesetting_get('expiry_report') &&
		!modulesetting_get('sms_report') && 
		!modulesetting_get('attendance_report') )  {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
	}
}
// Report
include("model.php");
include("db.php");

LOG_MSG('INFO',"Report: CONTROLLER DO: $DO");
LOG_MSG('INFO',"Report: CONTROLLER GO: $GO");

// If this check is not done, then all DO ajax calls will end up playing
// the list page by default 
// All the GO stuff
switch ($GO) {
	case 'search_report':
		search_report();
		break;
	case 'expiry_report':
		expiry_report();
		break;
	case 'profitability_report':
		profitability_report();
		break;
	case 'vehicle_report':
		search_vehicle_report();
		break;	
	case 'smssent_report':
		smssent_report();
		break;
	case 'attendance_report':
		attendance_report();
		break;
	case 'attendancelog_report':
		attendancelog_report();
		break;
	case 'student_report':
		student_report();
		break;
        case 'iisclog_report':
                iisclog_report();
                break;
	default:
		go_report();
		break;
}
show_msgs();

LOG_MSG('INFO',"REPORT: CONTROLLER end: GO=[$GO] DO=[$DO]");

?>
