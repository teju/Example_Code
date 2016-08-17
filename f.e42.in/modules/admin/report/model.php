<?php

// Report
function go_report() {
	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_report(): START");

	$report_name=get_arg($_GET,'report_name');

	if ( !validate("Report Name",$report_name,1,20,"varchar") ){
		LOG_MSG('ERROR',"go_report(): VALIDATE ARGS FAILED! report_name=[$report_name]");
		return;
	}

	if ( 
		$report_name != 'search_report' && 
		$report_name != 'vehicle_report' && 
		$report_name != 'profitability_report' && 
		$report_name != 'attendance_report' && 
		$report_name != 'student_report' && 
		$report_name != 'smssent_report' && 
		$report_name != 'attendancelog_report' && 
		$report_name != 'expiry_report' && 
		$report_name != 'iisclog_report' ) {
			add_msg('ERROR','Invalid Report Name');
			return;
		}

		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."reports.html"; } 
		include($template); 

	LOG_MSG('INFO',"go_report(): END");

}

function search_report() {

	global $TEMPLATE, $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;
	if (  !modulesetting_get('log_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}

	if( !has_user_permission(__FUNCTION__) || !modulesetting_get('log_report') ) {  // CHECK USER ACCESSIBILITY
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	LOG_MSG('INFO',"search_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$reg_no=get_arg($_GET,"reg_no");
	$type=get_arg($_GET,"type");
	$vehicle_model=get_arg($_GET,"vehicle_model");
	$sticker_no=get_arg($_GET,"sticker_no");
	$vehicle_status=get_arg($_GET,"vehicle_status");
	$driver_name=get_arg($_GET,"driver_name");
	$driver_phone_no=get_arg($_GET,"driver_phone_no");
	$supervisor_name=get_arg($_GET,"supervisor_name");
	$filling_station=get_arg($_GET,"filling_station");
	$fuel_rate=get_arg($_GET,"fuel_rate");
	$supervisor_phone_no=get_arg($_GET,"supervisor_phone_no");
	$client_name=get_arg($_GET,"client_name");
	$daily_fuel_lmt=get_arg($_GET,"daily_fuel_lmt");
	$monthly_fuel_lmt=get_arg($_GET,"monthly_fuel_lmt");
	$fuel_filled=get_arg($_GET,"fuel_filled");
	$odometer_reading=get_arg($_GET,"odometer_reading");
	$fuel_image=get_arg($_GET,"fuel_image");
	$odometer_image=get_arg($_GET,"odometer_image");
	$accountability_date=get_arg($_GET,"accountability_date");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");

	if(get_arg($_GET,"accountability_st_dt") != '') $accountability_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"accountability_st_dt")));
	else $accountability_st_dt=""; 
	if(get_arg($_GET,"accountability_en_dt") != '') $accountability_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"accountability_en_dt")));
	else $accountability_en_dt="";
	$report_mode=get_arg($_GET,"report_mode");

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."search_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"search_report(): Got args");

	// Validate parameters as normal strings 
	if (!validate("Vehicle No",$reg_no,0,20,"varchar") ||
		!validate("Type",$type,0,11,"varchar") ||
		!validate("Driver Name",$driver_name,0,200,"varchar") ||
		!validate("Driver Phone",$driver_phone_no,0,18,"bigint") ||
		!validate("Supervisor Name",$supervisor_name,0,200,"varchar") ||
		!validate("Supervisor Mobile",$supervisor_phone_no,0,18,"bigint") ||
		!validate("Daily Fuel Lmt",$daily_fuel_lmt,0,10,"varchar") ||
		!validate("Monthly Fuel Lmt",$monthly_fuel_lmt,0,10,"varchar") ||
		!validate("Fuel Filled",$fuel_filled,0,10,"varchar") ||
		!validate("Odometer Reading",$odometer_reading,0,45,"varchar") ||
		!validate("Created Start Date",$accountability_st_dt,0,30,"varchar") || 
		!validate("Created End Date",$accountability_en_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_search_list(): Validate args failed!");
		return;
	}

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Order By should be one of the following 
	if ($order_by != 'reg_no' &&  
		$order_by != 'type' && 
		$order_by != 'vehicle_model' && 
		$order_by != 'sticker_no' && 
		$order_by != 'vehicle_status' && 
		$order_by != 'driver_name' && 
		$order_by != 'driver_phone_no' && 
		$order_by != 'supervisor_name' && 
		$order_by != 'filling_station' && 
		$order_by != 'fuel_rate' && 
		$order_by != 'supervisor_phone_no' && 
		$order_by != 'client_name' && 
		$order_by != 'daily_fuel_lmt' && 
		$order_by != 'monthly_fuel_lmt' && 
		$order_by != 'fuel_filled' && 
		$order_by != 'odometer_reading' && 
		$order_by != 'accountability_date' && 
		$order_by != 'created_dt' && 
		$order_by != 'created_en_dt' && 
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="reg_no=$reg_no&type=$type&vehicle_status=$vehicle_status&driver_name=$driver_name&driver_phone_no=$driver_phone_no&supervisor_name=$supervisor_name&supervisor_phone_no=$supervisor_phone_no&daily_fuel_lmt=$daily_fuel_lmt&monthly_fuel_lmt=$monthly_fuel_lmt&fuel_filled=$fuel_filled&odometer_reading=$odometer_reading&accountability_st_dt=$accountability_st_dt&accountability_en_dt=$accountability_en_dt";
	$search_str.="&order_by=$order_by&order_by_type=$order_by_type";
	LOG_MSG('INFO',"search_report(): Validated args");

	$search_row=db_search_report_select(
										$reg_no,
										$type,
										$vehicle_model,
										$sticker_no,
										$vehicle_status,
										$driver_name,
										$driver_phone_no,
										$supervisor_name,
										$filling_station,
										$fuel_rate,
										$supervisor_phone_no,
										$client_name,
										$daily_fuel_lmt,
										$monthly_fuel_lmt,
										$fuel_filled,
										$odometer_reading,
										$fuel_image,
										$odometer_image,
										$accountability_st_dt,
										$accountability_en_dt,
										$order_by,
										$order_by_type	);
	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}

	for ( $i=0;$i<$search_row[0]['NROWS'];$i++ ) {
		$search_row[$i]['fuel_amount']=0.00;
		$search_row[$i]['fuel_amount']=$search_row[$i]['fuel_filled'] * $search_row[$i]['fuel_rate'] ;
	}
	
	$search_row['filename']='modules/admin/report/search_list.html';
	$search_row['order_by_type']=$order_by_type;
	$search_row['order_by']=$order_by;
	$search_row['search_str']=$search_str;
	$search_row['report_name']='search_report';

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"search_report(): END");	
	exit;
}

function search_vehicle_report() {
	if (  !modulesetting_get('vehicle_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}

	global $TEMPLATE, $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;

	if( !has_user_permission(__FUNCTION__) ) {  // CHECK USER ACCESSIBILITY
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	LOG_MSG('INFO',"search_vehicle_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$client_name=get_arg($_GET,"client_name");
	$reg_no=get_arg($_GET,"reg_no");
	$type=get_arg($_GET,"type");
	$vehicle_status=get_arg($_GET,"vehicle_status");
	$daily_fuel_lmt=get_arg($_GET,"daily_fuel_lmt");
	$monthly_fuel_lmt=get_arg($_GET,"monthly_fuel_lmt");
	$fuel_filled=get_arg($_GET,"fuel_filled");
	$odometer_reading=get_arg($_GET,"odometer_reading");
	$fuel_image=get_arg($_GET,"fuel_image");
	$odometer_image=get_arg($_GET,"odometer_image");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");

	if(get_arg($_GET,"created_st_dt") != '') $created_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_st_dt")));
	else $created_st_dt=""; 
	if(get_arg($_GET,"created_en_dt") != '') $created_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_en_dt")));
	else $created_en_dt="";
	$report_mode=get_arg($_GET,"report_mode");

	$row_client=db_get_list("ARRAY","name,client_id","tClient","travel_id=".TRAVEL_ID);
	if ( $row_client[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Client details. Please try again later</div>";
		LOG_ARR("INFO","row_client",$row_client);
		exit;
	}

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."search_vehicle_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"search_vehicle_report(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Client",$client_name,0,200,"varchar") ||
		!validate("Vehicle No",$reg_no,0,20,"varchar") ||
		!validate("Type",$type,0,11,"varchar") ||
		!validate("Daily Fuel Lmt",$daily_fuel_lmt,0,10,"varchar") ||
		!validate("Monthly Fuel Lmt",$monthly_fuel_lmt,0,10,"varchar") ||
		!validate("Fuel Filled",$fuel_filled,0,10,"varchar") ||
		!validate("Odometer Reading",$odometer_reading,0,45,"varchar") ||
		!validate("Created Start Date",$created_st_dt,0,30,"varchar") || 
		!validate("Created End Date",$created_en_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_search_list(): Validate args failed!");
		return;
	}

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Order By should be one of the following 
	if ($order_by != 'client_name' &&
		$order_by != 'reg_no' &&  
		$order_by != 'type' &&
		$order_by != 'vehicle_model' && 
		$order_by != 'vehicle_status' && 
		$order_by != 'daily_fuel_lmt' && 
		$order_by != 'monthly_fuel_lmt' && 
		$order_by != 'fuel_filled' && 
		$order_by != 'odometer_reading' && 
		$order_by != 'fuel_image' && 
		$order_by != 'odometer_image' &&
		$order_by != 'total_fills' && 
		$order_by != 'created_dt' && 
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="client_name=$client_name&reg_no=$reg_no&type=$type&vehicle_status=$vehicle_status&daily_fuel_lmt=$daily_fuel_lmt&monthly_fuel_lmt=$monthly_fuel_lmt&fuel_filled=$fuel_filled&odometer_reading=$odometer_reading&created_st_dt=$created_st_dt&created_en_dt=$created_en_dt";
	$search_str.="&order_by=$order_by&order_by_type=$order_by_type";
	LOG_MSG('INFO',"search_vehicle_report(): Validated args");

	$search_row=db_search_vehicle_report_select(
										$client_name,
										$reg_no,
										$type,
										$vehicle_status,
										$daily_fuel_lmt,
										$monthly_fuel_lmt,
										$fuel_filled,
										$odometer_reading,
										$fuel_image,
										$odometer_image,
										$created_st_dt,
										$created_en_dt,
										$order_by,
										$order_by_type	);
									
									
	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}

	$search_row['filename']='modules/admin/report/search_vehicle_list.html';
	$search_row['order_by_type']=$order_by_type;
	$search_row['order_by']=$order_by;
	$search_row['search_str']=$search_str;
	$search_row['report_name']='search_vehicle_report';

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"search_vehicle_report(): END");	
	exit;
}

function expiry_report() {
	if (  !modulesetting_get('expiry_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}

	global $TEMPLATE, $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;

	if( !has_user_permission(__FUNCTION__) ) {  // CHECK USER ACCESSIBILITY
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	LOG_MSG('INFO',"expiry_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$reg_no=get_arg($_GET,"reg_no");
	$exp_type=get_arg($_GET,"exp_type");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");

	if(get_arg($_GET,"exp_st_dt") != '') $exp_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"exp_st_dt")));
	else $exp_st_dt=""; 
	if(get_arg($_GET,"exp_en_dt") != '') $exp_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"exp_en_dt")));
	else $exp_en_dt="";
	$report_mode=get_arg($_GET,"report_mode");

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."expiry_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"expiry_report(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Vehicle No",$reg_no,0,20,"varchar") ||
		!validate("Expiry Start Date",$exp_st_dt,0,30,"varchar") || 
		!validate("Expiry End Date",$exp_en_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_search_list(): Validate args failed!");
		return;
	}

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Order By should be one of the following 
	if ($order_by != 'reg_no' &&  
		$order_by != 'rc_exp_dt' && 
		$order_by != 'insurance_exp_dt' && 
		$order_by != 'road_tax_exp_dt' && 
		$order_by != 'start_dt' && 
		$order_by != 'end_dt' && 
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="reg_no=$reg_no&exp_type=$exp_type&exp_st_dt=$exp_st_dt&exp_en_dt=$exp_en_dt";
	$search_str.="&order_by=$order_by&order_by_type=$order_by_type";
	LOG_MSG('INFO',"search_vehicle_report(): Validated args");

	$search_row=db_expiry_report_select(
										$reg_no,
										$exp_type,
										$exp_st_dt,
										$exp_en_dt,
										$order_by,
										$order_by_type	);
	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}

	$search_row['filename']='modules/admin/report/expiry_list.html';
	$search_row['order_by_type']=$order_by_type;
	$search_row['order_by']=$order_by;
	$search_row['search_str']=$search_str;
	$search_row['report_name']='expiry_report';

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"search_vehicle_report(): END");	
	exit;
}

function profitability_report() {

	if (!modulesetting_get('profitability_report') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
	}

	global $TEMPLATE, $ERROR_MESSAGE;

	if( !has_user_permission(__FUNCTION__)) {  // CHECK USER ACCESSIBILITY
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	LOG_MSG('INFO',"profitability_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$reg_no=get_arg($_GET,"reg_no");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");
	$tripsheet_order_by=get_arg($_GET,"tripsheet_order_by");
	$tripsheet_order_by_type=get_arg($_GET,"tripsheet_order_by_type");
	$jobcard_order_by=get_arg($_GET,"jobcard_order_by");
	$jobcard_order_by_type=get_arg($_GET,"jobcard_order_by_type");

	if(get_arg($_GET,"created_st_dt") != '') $created_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_st_dt")));
	else $created_st_dt=""; 
	if(get_arg($_GET,"created_en_dt") != '') $created_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_en_dt")));
	else $created_en_dt="";
	$report_mode=get_arg($_GET,"report_mode");

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."profitability_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"profitability_report(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Vehicle No",$reg_no,0,20,"varchar") ||
		!validate("Created Start Date",$created_st_dt,0,30,"varchar") || 
		!validate("Created End Date",$created_en_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_search_list(): Validate args failed!");
		return;
	}

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Order By should be one of the following 
	if ($order_by != 'reg_no' &&  
		$order_by != 'fuel_filled' &&  
		$order_by != 'fuel_amount' &&  
		$order_by != 'driver_sal' &&  
		$order_by != 'cleaner_salary' && 
		$order_by != 'profit' && 
		$order_by != 'profit_percentage' && 
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="reg_no=$reg_no&created_st_dt=$created_st_dt&created_en_dt=$created_en_dt";
	$search_str.="&order_by=$order_by&order_by_type=$order_by_type";
	LOG_MSG('INFO',"search_vehicle_report(): Validated args");

	$search_row=db_profitability_report_select(
										$reg_no,
										$created_st_dt,
										$created_en_dt,
										$order_by,
										$order_by_type	);

	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}

	if ( $jobcard_order_by != 'jobcard_amount' &&  
		 $jobcard_order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $jobcard_order_by_type != 'ASC' && $jobcard_order_by_type != 'DESC' && $jobcard_order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	} 

	$jobcard_amount_row=db_get_job_amount(
									$reg_no,
									$created_st_dt,
									$created_en_dt,
									$jobcard_order_by,
									$jobcard_order_by_type);
	if ( $jobcard_amount_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}

	// Add the job amount row in the main array
	for ( $i=0;$i<$search_row[0]['NROWS'];$i++ ) {
		$search_row[$i]['jobcard_amount']=0.00;
		for ( $j=0;$j<$jobcard_amount_row[0]['NROWS'];$j++ ) {
			if ( $search_row[$i]['reg_no'] == $jobcard_amount_row[$j]['reg_no'] ) {
				$search_row[$i]['jobcard_amount']=$jobcard_amount_row[$j]['jobcard_amount'];
			}
		}
	}

	if ( $tripsheet_order_by != 'tripsheet_amount' &&  
		 $tripsheet_order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $tripsheet_order_by_type != 'ASC' && $tripsheet_order_by_type != 'DESC' && $tripsheet_order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}

	$search_str.="&tripsheet_order_by=$tripsheet_order_by&tripsheet_order_by_type=$tripsheet_order_by_type";

	$tripsheet_amount_row=db_get_tripsheet_amount(
												$reg_no,
												$created_st_dt,
												$created_en_dt,
												$tripsheet_order_by,
												$tripsheet_order_by_type);
	if ( $tripsheet_amount_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}

	// Add the trip amount row in the main array
	for ( $i=0;$i<$search_row[0]['NROWS'];$i++ ) {
		$search_row[$i]['tripsheet_amount']=0.00;
		for ( $j=0;$j<$tripsheet_amount_row[0]['NROWS'];$j++ ) {
			if ( $search_row[$i]['reg_no'] == $tripsheet_amount_row[$j]['reg_no'] ) {
				$search_row[$i]['tripsheet_amount']=$tripsheet_amount_row[$j]['tripsheet_amount'];
			}
		}
	}

	for ( $i=0;$i<$search_row[0]['NROWS'];$i++ ) {
		$search_row[$i]['profit']=0.00;
		$search_row[$i]['profit_percentage']=0.00;
		$search_row[$i]['profit']=$search_row[$i]['tripsheet_amount'] - 
									($search_row[$i]['fuel_amount'] + 
									$search_row[$i]['jobcard_amount'] + 
									$search_row[$i]['driver_sal'] + 
									$search_row[$i]['cleaner_salary']);
		if( $search_row[$i]['tripsheet_amount'] != 0 || $search_row[$i]['profit'] != 0) {
		$search_row[$i]['profit_percentage']=round((($search_row[$i]['tripsheet_amount']/$search_row[$i]['profit'])*100),2);
		}
	}

	if( $tripsheet_order_by != "" ) {
		$temp_arr=$search_row;
		for ( $j=0;$j<$tripsheet_amount_row[0]['NROWS'];$j++ ) {
			for ( $i=0;$i<$temp_arr[0]['NROWS'];$i++ ) {
				if ( $temp_arr[$i]['reg_no'] == $tripsheet_amount_row[$j]['reg_no'] ) {
					$search_row[$j]=$temp_arr[$i];
				}
			}
		}
		$search_row[0]["NROWS"]=$temp_arr[0]["NROWS"];
	}

	if( $jobcard_order_by != "" ) {
		$jobcard_temp_arr=$search_row;
		for ( $j=0;$j<$jobcard_amount_row[0]['NROWS'];$j++ ) {
			for ( $i=0;$i<$jobcard_temp_arr[0]['NROWS'];$i++ ) {
				if ( $jobcard_temp_arr[$i]['reg_no'] == $jobcard_amount_row[$j]['reg_no'] ) {
					$search_row[$j]=$jobcard_temp_arr[$i];
				}
			}
		}
		$search_row[0]["NROWS"]=$jobcard_temp_arr[0]["NROWS"];
	}

	$search_row['filename']='modules/admin/report/profitability_list.html';
	$search_row['order_by_type']=$order_by_type;
	$search_row['jobcard_order_by_type']=$jobcard_order_by_type;
	$search_row['tripsheet_order_by_type']=$tripsheet_order_by_type;
	$search_row['order_by']=$order_by;
	$search_row['tripsheet_order_by']=$tripsheet_order_by;
	$search_row['jobcard_order_by']=$jobcard_order_by;
	$search_row['search_str']=$search_str;
	$search_row['report_name']='profitability_report';

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"profitability_report(): END");
	exit;
}

function smssent_report() {

	if (  !modulesetting_get('sms_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}
	global $ROW, $TEMPLATE, $ERROR_MESSAGE;

	if( !has_user_permission(__FUNCTION__) ) {  // CHECK USER ACCESSIBILITY
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	if (!modulesetting_get('sms_report') ) {
	add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
	show_msgs();
	return;
}

	LOG_MSG('INFO',"smssent_report(): START");

	// Show search bar
	$report_mode=get_arg($_GET,"report_mode");
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."smssent_searchbar.html"; } 
		include($template); 
		exit;
	}

	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$from_no=get_arg($_GET,"from_no");
	$to_no=get_arg($_GET,"to_no");
	$message=get_arg($_GET,"sms_message"); // To distinguish between the variable 'message' in dialog and in the sms message
	$response=get_arg($_GET,"response");
	$status=get_arg($_GET,"status");
	if(get_arg($_GET,"smssent_st_dt") != '') $smssent_st_dt=date('Y-m-d H:i:s',strtotime(get_arg($_GET,"smssent_st_dt")));
	else $smssent_st_dt=""; 
	if(get_arg($_GET,"smssent_en_dt") != '') $smssent_en_dt=date('Y-m-d H:i:s',strtotime(get_arg($_GET,"smssent_en_dt")));
	else $smssent_en_dt="";
	$search_query=get_arg($_GET,"search_query");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");

	LOG_MSG('INFO',"smssent_report(): Got args".print_r($_GET,true));

	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,100,"varchar") ||
		!validate("From",$from_no,0,20,"varchar") ||
		!validate("To",$to_no,0,18,"bigint") ||
		!validate("Message",$message,0,1000,"varchar") ||
		!validate("Response",$response,0,1000,"varchar") ||
		!validate("Status",$status,0,10,"varchar") ||
		!validate("Sms Sent Date from",$smssent_st_dt,0,30,"varchar") ||
		!validate("Sms Sent Date to",$smssent_en_dt,0,30,"varchar") ||
		!validate("Order By",$order_by,0,30,"varchar") ||
		!validate("Sort By",$order_by_type,0,4,"varchar") ) {
		LOG_MSG('ERROR',"smssent_report(): VALIDATE ARGS FAILED!");
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	// Order By should be one of the following 
	if ($order_by != 'name' &&  
		$order_by != 'from_no' && 
		$order_by != 'to_no' && 
		$order_by != 'sms_message' && 
		$order_by != 'response' && 
		$order_by != 'status' && 
		$order_by != 'created_dt' &&
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	 if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}
	// The field name in the db function is messgae
	if ( $order_by == 'sms_message') {
		$order_by = 'message';
	}
	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}
	LOG_MSG('INFO',"smssent_report(): Validated args");

	$search_str="name=$name&from_no=$from_no&to_no=$to_no&response=$response&smssent_st_dt=$smssent_st_dt&smssent_en_dt=$smssent_en_dt&status=$status";
	$smssent_row=db_smssent_report_select(
										$name,
										$from_no,
										$to_no,
										$message,
										$response,
										$status,
										$smssent_st_dt,
										$smssent_en_dt,
										$order_by,
										$order_by_type	);
	if ( $smssent_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Sms Sent Report. Please try again later</div>";
		LOG_ARR("INFO","smssent_row",$smssent_row);
		exit;
	}

	$smssent_row['filename']='modules/admin/report/smssent_list.html';
	$smssent_row['order_by_type']=$order_by_type;
	$smssent_row['order_by']=$order_by;
	$smssent_row['search_str']=$search_str;
	$smssent_row['report_name']='SMSSent_Report';
	$resp=generate($report_mode,$smssent_row);	// Generates HTML, PDF, EMAIL
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
				$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"smssent_report(): END");
	exit;
}

function attendance_report() {
	if (  !modulesetting_get('attendance_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}
	global $TEMPLATE, $ERROR_MESSAGE;

	LOG_MSG('INFO',"search_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$time_in=get_arg($_GET,"time_in");
	$time_out=get_arg($_GET,"time_out");
	$time_of_day=get_arg($_GET,"time_of_day");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");
	LOG_MSG('DEBUG',"do_user_list(): Got args");

	if(get_arg($_GET,"created_st_dt") != '') $created_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_st_dt")));
	else $created_st_dt=""; 
	if(get_arg($_GET,"created_en_dt") != '') $created_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_en_dt")));
	else $created_en_dt="";
	$report_mode=get_arg($_GET,"report_mode");

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."attendance_search.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"search_report(): Got args");

	// Validate parameters as normal strings 
	
// Validate parameters as normal strings 
	if (
		!validate("Time In",$time_in,0,30,"varchar") ||
		!validate("Time Out",$time_out,0,30,"varchar") ||
		!validate("Time Of Day",$time_of_day,0,10,"varchar")||
		!validate("Created Start Date",$created_st_dt,0,30,"varchar")||
		!validate("Created End Date",$created_en_dt,0,30,"varchar")) {
		LOG_MSG('ERROR',"do_student_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_student_list(): Validated args");

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="time_in=$time_in&time_out=$time_out&time_of_day=$time_of_day	";
	LOG_MSG('INFO',"search_report(): Validated args");
	
	$user_row=db_get_list('ARRAY','student_id,name','tStudent','1');//,"time_in >= '$time_in' AND time_out <= '$time_out'");
	if( $created_st_dt != "" && $created_en_dt != "" ) {
		if( $created_st_dt != $created_en_dt) {
			$date_row=db_get_list('ARRAY','DATE_FORMAT(time_in,"%Y-%m-%d") date','tStudentLog',"time_in >= '$created_st_dt' AND time_in <= '$created_en_dt'");
		}
		else {
			$date_row=db_get_list('ARRAY','DATE_FORMAT(time_in,"%Y-%m-%d") date','tStudentLog',"time_in like '%$created_st_dt%'");
		}
	}
	else {
		$date_row=db_get_list('ARRAY','DATE_FORMAT(time_in,"%Y-%m-%d") date','tStudentLog', '1 Group BY 1');
	}

	$student_row=db_studentlog_select(
		"",
			$created_st_dt,
			$created_en_dt);
	if ( $student_row[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Users. Please try again later. <br/>");
		return;
	}
	$attendance_arr =array();

	for ( $i=0;$i<$user_row[0]['NROWS'];$i++ ) {	// Loop the date
		for ( $j=0;$j<$date_row[0]['NROWS'];$j++ ) {	// Loop the date
			$attendance_arr[$user_row[$i]['student_id']][$date_row[$j]['date']]['MORNING']='A';
			$attendance_arr[$user_row[$i]['student_id']][$date_row[$j]['date']]['EVENING']='A';
		}
	}
	// Get the present users
	for ( $k=0;$k<$student_row[0]['NROWS'];$k++ ) {		// Loop the user log
		$attendance_arr[$student_row[$k]['student_id']][date('Y-m-d',strtotime($student_row[$k]['time_in']))]['name']=$student_row[$k]['name'];
		$attendance_arr[$student_row[$k]['student_id']][date('Y-m-d',strtotime($student_row[$k]['time_in']))][$student_row[$k]['time_of_day']]='P';
	}

	$student_row['filename']='modules/admin/report/attendance_list.html';
	$student_row['search_str']=$search_str;
	$student_row['user_row']=$user_row;
	$student_row['date_row']=$date_row;
	$student_row['attendance_arr']=$attendance_arr;
	$search_row['order_by']=$order_by;
	$search_row['order_by_type']=$order_by_type;
	$student_row['report_name']='attendance_report';

	$resp=generate($report_mode,$student_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"search_report(): END");	
	exit;
}

function student_report() {

	global $TEMPLATE, $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;
	if (  !modulesetting_get('student_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}

	if( !has_user_permission(__FUNCTION__) || !modulesetting_get('student_report') ) {  // CHECK USER ACCESSIBILITY
		echo "<div class='error' style='margin-top:200px;text-align:center;'>$ERROR_MESSAGE</div>";
		exit;
	}

	LOG_MSG('INFO',"student_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$student_name=get_arg($_GET,"student_name");
	$guardian_name=get_arg($_GET,"guardian_name");
	$id_number=get_arg($_GET,"id_number");
	$client_name=get_arg($_GET,"client_name");
	$created_dt=get_arg($_GET,"created_dt");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");

	$report_mode=get_arg($_GET,"report_mode");

	if(get_arg($_GET,"created_st_dt") != '') $created_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_st_dt")));
	else $created_st_dt=""; 
	if(get_arg($_GET,"created_en_dt") != '') $created_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_en_dt")));
	else $created_en_dt="";

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."student_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"student_report(): Got args");

	// Validate parameters as normal strings 
	if (!validate("Student Name",$student_name,0,200,"varchar") ||
		!validate("Guardian Name",$guardian_name,0,200,"varchar") ||
		!validate("Id Number",$id_number,0,200,"varchar") ||
		!validate("Client Name",$client_name,0,200,"varchar")){
		LOG_MSG('ERROR',"do_student_list(): Validate args failed!");
		return;
	}
// Order By should be one of the following 
	if ($order_by != 'name' &&  
		$order_by != 'id_number' && 
		$order_by != 'time_in' && 
		$order_by != 'time_out' && 
		$order_by != 'time_of_day' && 
		$order_by != 'reg_no' && 
		$order_by != 'st_reg_no' && 
		$order_by != 'client_name' && 
		$order_by != 'route' && 
		$order_by != 'created_dt' && 
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="student_name=$student_name&guardian_name=$guardian_name&id_number=$id_number&client_name=$client_name&created_st_dt=$created_st_dt&created_en_dt=$created_en_dt&order_by=$order_by&order_by_type=$order_by_type";


	$search_row=db_student_report_select(
										$student_name,
										$guardian_name,
										$id_number,
										$client_name,
										$created_st_dt,
										$created_en_dt,
										$order_by,
										$order_by_type);
	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","search_row",$search_row);
		exit;
	}
	$search_row['filename']='modules/admin/report/student_list.html';
	$search_row['search_str']=$search_str;
	$search_row['report_name']='student_report';
	$search_row['order_by']=$order_by;
	$search_row['order_by_type']=$order_by_type;

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
					$json_response['message']=$ERROR_MESSAGE;
					echo json_encode($json_response);
					exit;
				}
				$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
				echo json_encode($json_response);
	}

	LOG_MSG('INFO',"search_report(): END");	
	exit;
}

function iisclog_report() {

	global $TEMPLATE, $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;
	if (  !modulesetting_get('iisclog_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}

	LOG_MSG('INFO',"iisclog_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$student_name=get_arg($_GET,"student_name");
	$id_number=get_arg($_GET,"id_number");
	if(get_arg($_GET,"created_dt") != '') $created_dt=date('Y-m-d',strtotime(get_arg($_GET,"created_dt")));
	else $created_dt=""; 
	$report_mode=get_arg($_GET,"report_mode");

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."iisclog_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"iisclog_report(): Got args");

	// Validate parameters as normal strings 
	if (!validate("User Name",$student_name,0,200,"varchar") ||
		!validate("Id Number",$id_number,0,200,"varchar") ||
		!validate("Created Date",$created_dt,0,30,"varchar")) {
		LOG_MSG('ERROR',"iisclog_report(): Validate args failed!");
		return;
	}

	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="student_name=$student_name&id_number=$id_number&created_dt=$created_dt";

	$search_row=db_iisclog_report_select(
										$student_name,
										$id_number,
										$created_dt);
	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","iisclog_report(): search_row",$search_row);
		exit;
	}
	$search_row['filename']='modules/admin/report/iisclog_list.html';
	$search_row['search_str']=$search_str;
	$search_row['report_name']='userlog_report';

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			exit;
		}
		$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
		echo json_encode($json_response);
	}

	LOG_MSG('INFO',"iisclog_report(): END");	
	exit;
}

function attendancelog_report() {

	global $TEMPLATE, $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;
	if ( !modulesetting_get('attendancelog_report') ) {
		add_msg('ERROR','Sorry! You do not have the privileges to perform this action');
		show_msgs();
		return;
	}

	LOG_MSG('INFO',"attendancelog_report(): START");

	// Do we have a search string?
	// Get all the args from $_GET
	$student_name=get_arg($_GET,"student_name");
	$id_number=get_arg($_GET,"id_number");
	if(get_arg($_GET,"log_st_dt") != '') $log_st_dt=date('Y-m-d',strtotime(get_arg($_GET,"log_st_dt")));
	else $log_st_dt=""; 
	if(get_arg($_GET,"log_en_dt") != '') $log_en_dt=date('Y-m-d',strtotime(get_arg($_GET,"log_en_dt")));
	else $log_en_dt="";
	$report_mode=get_arg($_GET,"report_mode");
	$order_by=get_arg($_GET,"order_by");
	$order_by_type=get_arg($_GET,"order_by_type");
	print_arr($_GET);

	// Show search bar
	if ( $report_mode == 'SEARCH' ) {
		if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."attendancelog_searchbar.html"; } 
		include($template); 
		exit;
	}
	LOG_MSG('INFO',"attendancelog_report(): Got args");

	// Validate parameters as normal strings 
	if (!validate("User Name",$student_name,0,200,"varchar") ||
		!validate("Id Number",$id_number,0,200,"varchar") ||
		!validate("Created Date",$log_st_dt,0,30,"varchar")) {
		LOG_MSG('ERROR',"attendancelog_report(): Validate args failed!");
		return;
	}
// Order By should be one of the following 
	if ($order_by != 'student_name' &&  
		$order_by != 'log_dt' &&  
		$order_by != 'created_dt' && 
		$order_by != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By</div>";
		exit;
	}

	// Order By Type should be one of the following 
	if ( $order_by_type != 'ASC' && $order_by_type != 'DESC' && $order_by_type != '' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Order By Type</div>";
		exit;
	}
	// Report mode should be one of the following 
	if ( $report_mode != 'CSV' && $report_mode != 'HTML' && $report_mode != 'PDF' && $report_mode != 'EMAIL' ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>Invalid Report Mode</div>";
		exit;
	}

	// Search string when searched from the Report
	$search_str="student_name=$student_name&id_number=$id_number&log_st_dt=$log_st_dt&log_en_dt=$log_en_dt";

	$search_row=db_attendancelog_report_select(
										$student_name,
										$id_number,
										$log_st_dt,
										$log_en_dt,
										$order_by,
										$order_by_type);
	if ( $search_row[0]['STATUS'] != "OK" ) {
		echo "<div class='error' style='margin-top:200px;text-align:center;'>There was an error loading the Search Report. Please try again later</div>";
		LOG_ARR("INFO","iisclog_report(): search_row",$search_row);
		exit;
	}
	$search_row['filename']='modules/admin/report/attendancelog_list.html';
	$search_row['search_str']=$search_str;
	$search_row['report_name']='attendancelog_report';
	$search_row['order_by']=$order_by;
	$search_row['order_by_type']=$order_by_type;

	$resp=generate($report_mode,$search_row);
	if ( $report_mode == 'EMAIL' ) {
		$json=array();
		$json_response['status']='ERROR';
		if ( $resp === false ) {
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			exit;
		}
		$json_response['status']='OK';
		$json_response['to']=get_arg($_GET,"to");
		echo json_encode($json_response);
	}

	LOG_MSG('INFO',"iisclog_report(): END");	
	exit;
}

?>
