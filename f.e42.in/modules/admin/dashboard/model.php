<?php 

function go_dashboard_list() {

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_dashboard_list(): START GET=".print_r($_GET,true));

	
	//If start date is empty Change date to 30 days before from today
	if(get_arg($_GET,'st_date') == '' ) $_GET['st_date']=date('d-M-Y',mktime(0,0,0,date('m'),date('d')-30,date('Y')));
	//If end date is empty change date to today's date
	if(get_arg($_GET,'en_date') == '' ) $_GET['en_date']=date('d-M-Y',strtotime(today()));

	//Get Date Arguments
	$st_date=date('Y-m-d',strtotime(get_arg($_GET,'st_date')));
	$en_date=date('Y-m-d',strtotime(get_arg($_GET,'en_date')));

	$total_fuel_filled=db_total_fuel_select($st_date,$en_date);
	if ( $total_fuel_filled[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Drivers. Please try again later. <br/>");
		return;
	}

	$today_fuel_filled=db_today_fuel_select();
	if ( $today_fuel_filled[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Drivers. Please try again later. <br/>");
		return;
	}

	$mothly_fuel_filled=db_monthly_fuel_select($st_date,$en_date);
	if ( $mothly_fuel_filled[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Drivers. Please try again later. <br/>");
		return;
	}

	include ("dashboard.html");

	LOG_MSG('INFO',"go_dashboard_list(): END");

}
function go_attendance_list() {

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_attendance_list(): START GET=".print_r($_GET,true));

	//If start date is empty Change date to 30 days before from today
	if(get_arg($_GET,'time_in') == '' ) $_GET['time_in']=date('d-M-Y');

	//Get Date Arguments
	$time_in=date('Y-m-d',strtotime(get_arg($_GET,'time_in')));

	$attendance_row=db_total_students($time_in);
	if ( $attendance_row[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading. Please try again later. <br/>");
		return;
	}

	$total_student_on_board_morning=db_total_student_on_board('MORNING',$time_in);
	if ( $total_student_on_board_morning[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading. Please try again later. <br/>");
		return;
	}

	for ( $i=0;$i<$attendance_row[0]['NROWS'];$i++ ) {
		$reg_no=$attendance_row[$i]['reg_no'];
		$attendance_row['total_student_on_board_morning'][$reg_no]=0;
		for ( $j=0;$j<$total_student_on_board_morning[0]['NROWS'];$j++ ) {
			if ( $total_student_on_board_morning[$j]['reg_no'] == $reg_no ) $attendance_row['total_student_on_board_morning'][$reg_no]=$total_student_on_board_morning[$j]['total_students_on_board'];
		}
	}

	$total_student_on_board_evening=db_total_student_on_board('EVENING',$time_in);
	if ( $total_student_on_board_evening[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading. Please try again later. <br/>");
		return;
	}

	for ( $i=0;$i<$attendance_row[0]['NROWS'];$i++ ) {
		$reg_no=$attendance_row[$i]['reg_no'];
		$attendance_row['total_student_on_board_evening'][$reg_no]=0;
		for ( $j=0;$j<$total_student_on_board_evening[0]['NROWS'];$j++ ) {
			if ( $total_student_on_board_evening[$j]['reg_no'] == $reg_no ) {
				$attendance_row['total_student_on_board_evening'][$reg_no]=$total_student_on_board_evening[$j]['total_students_on_board'];
			}
		}
	}

	$invalid_route_morning=db_invalid_route_select('MORNING',$time_in);
	if ( $invalid_route_morning[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading. Please try again later. <br/>");
		return;
	}

	for ( $i=0;$i<$attendance_row[0]['NROWS'];$i++ ) {
		$reg_no=$attendance_row[$i]['reg_no'];
		if ( !isset($attendance_row['invalid_count_morning'][$reg_no]) ) $attendance_row['invalid_count_morning'][$reg_no]=0;
		for ( $j=0;$j<$invalid_route_morning[0]['NROWS'];$j++ ) {
			if ( $invalid_route_morning[$j]['reg_no'] != $invalid_route_morning[$j]['st_reg_no'] && $invalid_route_morning[$j]['reg_no'] == $reg_no ) { 
				$attendance_row['invalid_count_morning'][$reg_no]+=1;
			}
		}
	}

	$invalid_route_evening=db_invalid_route_select('EVENING',$time_in);
	if ( $invalid_route_evening[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading. Please try again later. <br/>");
		return;
	}

	for ( $i=0;$i<$attendance_row[0]['NROWS'];$i++ ) {
		$reg_no=$attendance_row[$i]['reg_no'];
		$attendance_row['invalid_count_evening'][$reg_no]=0;
		for ( $j=0;$j<$invalid_route_evening[0]['NROWS'];$j++ ) {
			if ( $invalid_route_evening[$j]['reg_no'] != $invalid_route_evening[$j]['st_reg_no'] && $invalid_route_evening[$j]['reg_no'] == $reg_no ) { 
				$attendance_row['invalid_count_evening'][$reg_no]+=1;
			}
		}
	}
	

	include ("dashboard.html");

	LOG_MSG('INFO',"go_attendance_list(): END");
}

?>