<?php


// SELECT
function db_search_report_select(
								$reg_no="",
								$type="",
								$vehicle_model="",
								$sticker_no="",
								$vehicle_status="",
								$driver_name="",
								$driver_phone_no="",
								$supervisor_name="",
								$filling_station="",
								$fuel_rate="",
								$supervisor_phone_no="",
								$client_name="",
								$daily_fuel_lmt="",
								$monthly_fuel_lmt="",
								$fuel_filled="",
								$odometer_reading="",
								$fuel_image="",
								$odometer_image="",
								$accountability_st_dt="",
								$accountability_en_dt="",
								$order_by="",
								$order_by_type=""	) {

		LOG_MSG('INFO',"db_search_report_select(): START { 
							accountability_st_dt=[$accountability_st_dt],
							accountability_en_dt=[$accountability_en_dt],
							order_by=[$order_by],
							order_by_type=[$order_by_type]		\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." s.reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator=" AND ";
	}
	if ( $vehicle_status !== "" ) { 
		$where_clause.=$seperator." s.vehicle_status = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","vehicle_status",$vehicle_status,true);
		$seperator=" AND ";
	}
	if ( $type !== "" ) { 
		$where_clause.=$seperator." v.type like? ";
		$param_arr=_db_prepare_param($param_arr,"s","type","%".$type."%",true);
		$seperator=" AND ";
	}
	if ( $driver_name !== "" ) { 
		$where_clause.=$seperator." s.driver_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","driver_name","%".$driver_name."%",true);
		$seperator=" AND ";
	}
	if ( $driver_phone_no !== "" ) { 
		$where_clause.=$seperator." s.driver_phone_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","driver_phone_no","%".$driver_phone_no."%",true);
		$seperator=" AND ";
	}
	if ( $supervisor_name !== "" ) { 
		$where_clause.=$seperator." s.supervisor_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","supervisor_name","%".$supervisor_name."%",true);
		$seperator=" AND ";
	}
	if ( $supervisor_phone_no !== "" ) { 
		$where_clause.=$seperator." s.supervisor_phone_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","supervisor_phone_no","%".$supervisor_phone_no."%",true);
		$seperator=" AND ";
	}
	if ( $client_name !== "" ) { 
		$where_clause.=$seperator." s.client_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","client_name","%".$client_name."%",true);
		$seperator=" AND ";
	}
	if ( $daily_fuel_lmt !== "" ) { 
		$where_clause.=$seperator." s.daily_fuel_lmt = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","daily_fuel_lmt",$daily_fuel_lmt,true);
		$seperator=" AND ";
	}
	if ( $monthly_fuel_lmt !== "" ) { 
		$where_clause.=$seperator." s.monthly_fuel_lmt = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","monthly_fuel_lmt",$monthly_fuel_lmt,true);
		$seperator=" AND ";
	}
	if ( $fuel_filled !== "" ) { 
		$where_clause.=$seperator." s.fuel_filled = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","fuel_filled",$fuel_filled,true);
		$seperator=" AND ";
	}
	if ( $odometer_reading !== "" ) { 
		$where_clause.=$seperator." s.odometer_reading like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","odometer_reading","%".$odometer_reading."%",true);
		$seperator=" AND ";
	}
	if ( $fuel_image !== "" ) { 
		$where_clause.=$seperator." s.fuel_image like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","fuel_image","%".$fuel_image."%",true);
		$seperator=" AND ";
	}
	if ( $odometer_image !== "" ) { 
		$where_clause.=$seperator." s.odometer_image like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","odometer_image","%".$odometer_image."%",true);
		$seperator=" AND ";
	}
	if ( $accountability_st_dt !== "" ) { 
		$where_clause.=$seperator." s.accountability_date >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","accountability_date",$accountability_st_dt,true);
		$seperator=" AND ";
	}
	if ( $accountability_en_dt !== "" ) { 
		$accountability_en_dt.=' 23:59:59';
		$where_clause.=$seperator." s.accountability_date <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","accountability_date",$accountability_en_dt,true);
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY $order_by $order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						s.reg_no,
						v.type,
						v.vehicle_model,
						v.sticker_no,
						s.supervisor_name,
						s.filling_station,
						s.fuel_rate,
						s.client_name,
						s.daily_fuel_lmt,
						s.monthly_fuel_lmt,
						s.fuel_filled,
						s.odometer_reading,
						s.fuel_image,
						s.odometer_image,
						s.accountability_date,
						s.created_dt
					FROM 
						tSearch s 
					LEFT OUTER JOIN tVehicle v ON(s.reg_no=v.reg_no AND s.travel_id=v.travel_id)
					$where_clause
					$order_by
					LIMIT 1000"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_search_report_select(): END");
	return $resp;
}


function db_profitability_report_select(
								$reg_no="",
								$created_st_dt="",
								$created_en_dt="",
								$order_by="",
								$order_by_type=""	) {

		LOG_MSG('INFO',"db_profitability_report_select(): START { 
							order_by=[$order_by],
							order_by_type=[$order_by_type]		\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND";

	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." s.reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator=" AND ";
	}
	if ( $created_st_dt !== "" ) { 
		$where_clause.=$seperator." s.created_dt >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_st_dt,true);
		$seperator=" AND ";
	}
	if ( $created_en_dt !== "" ) { 
		$created_en_dt.=' 23:59:59';
		$where_clause.=$seperator." s.created_dt <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_en_dt,true);
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY $order_by $order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						s.reg_no,
						SUM(s.fuel_filled) fuel_filled,
						SUM(s.fuel_filled*s.fuel_rate) fuel_amount,
						s.driver_sal,
						s.cleaner_salary
					FROM 
						tSearch s
					$where_clause
					GROUP BY
						reg_no
					$order_by
					LIMIT 10000"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_profitability_report_select(): END");
	return $resp;
}

function db_get_job_amount(
								$reg_no="",
								$st_dt="",
								$en_dt="",
								$order_by="",
								$order_by_type=""	) {

		LOG_MSG('INFO',"db_get_job_amount(): START { 
							reg_no=[$reg_no],
							st_dt=[$st_dt],
							en_dt=[$en_dt],
							jobcard_order_by=[$order_by],
							jobcard_order_by_type=[$order_by_type]		\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";

	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator=" AND ";
	}
	if ( $st_dt !== "" ) { 
		$where_clause.=$seperator." date >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","date",$st_dt,true);
		$seperator=" AND ";
	}
	if ( $en_dt !== "" ) { 
		$where_clause.=$seperator." date <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","date",$en_dt,true);
		$seperator=" AND ";
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY $order_by $order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						reg_no,
						SUM(amount) jobcard_amount
					FROM 
						tJobCard 
					$where_clause
					GROUP BY
						reg_no
					$order_by
					LIMIT 10000"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_job_amount(): END");
	return $resp;
}

function db_get_tripsheet_amount(
								$reg_no="",
								$st_dt="",
								$en_dt="",
								$tripsheet_order_by="",
								$tripsheet_order_by_type="") {

		LOG_MSG('INFO',"db_get_tripsheet_amount(): START { 
							reg_no=[$reg_no],
							st_dt=[$st_dt],
							en_dt=[$en_dt],
							tripsheet_order_by=[$tripsheet_order_by],
							tripsheet_order_by_type=[$tripsheet_order_by_type]		\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";

	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator=" AND ";
	}
	if ( $st_dt !== "" ) { 
		$where_clause.=$seperator." date >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","date",$st_dt,true);
		$seperator=" AND ";
	}
	if ( $en_dt !== "" ) { 
		$where_clause.=$seperator." date <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","date",$en_dt,true);
		$seperator=" AND ";
	}

	// Order By
	if ( $tripsheet_order_by != '' ) $tripsheet_order_by=" ORDER BY $tripsheet_order_by $tripsheet_order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						reg_no,
						SUM(amount) tripsheet_amount
					FROM 
						tTripSheet 
					$where_clause
					GROUP BY
						reg_no
					$tripsheet_order_by
					LIMIT 10000"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_tripsheet_amount(): END");
	return $resp;
}

// SELECT
function db_expiry_report_select(
								$reg_no="",
								$exp_type="",
								$exp_st_dt="",
								$exp_en_dt="",
								$order_by="",
								$order_by_type=""	) {

		LOG_MSG('INFO',"db_expiry_report_select(): START { 
							reg_no=[$reg_no],
							exp_type=[$exp_type],
							exp_st_dt=[$exp_st_dt],
							exp_en_dt=[$exp_en_dt],
							order_by=[$order_by],
							order_by_type=[$order_by_type]		\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE v.travel_id=".TRAVEL_ID;
	$seperator=" AND";

	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator=" AND ";
	}
	if ( $exp_st_dt !== "" ) { 
		$where_clause.=$seperator." $exp_type >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s",$exp_type,$exp_st_dt,true);
		$seperator=" AND ";
	}
	if ( $exp_en_dt !== "" ) { 
		$where_clause.=$seperator." $exp_type <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s",$exp_type,$exp_en_dt,true);
		$seperator=" AND ";
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY $order_by $order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE v.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						v.reg_no,
						v.rc_exp_dt,
						v.insurance_exp_dt,
						v.road_tax_exp_dt,
						v.start_dt,
						v.end_dt,
						created_dt
					FROM 
						tVehicle v
					$where_clause
					$order_by
					LIMIT 10000"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_expiry_report_select(): END");
	return $resp;
}




// SELECT
function db_search_vehicle_report_select(
								$client_name="",
								$reg_no="",
								$type="",
								$vehicle_status="",
								$daily_fuel_lmt="",
								$monthly_fuel_lmt="",
								$fuel_filled="",
								$odometer_reading="",
								$fuel_image="",
								$odometer_image="",
								$created_st_dt="",
								$created_en_dt="",
								$order_by="",
								$order_by_type=""	) {

		LOG_MSG('INFO',"db_search_vehicle_report_select(): START { 
							order_by=[$order_by],
							order_by_type=[$order_by_type]		\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	if ( $client_name !== "" ) { 
		$where_clause.=$seperator." s.client_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","client_name","%".$client_name."%",true);
		$seperator=" AND ";
	}
	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." s.reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator=" AND ";
	}
	if ( $vehicle_status !== "" ) { 
		$where_clause.=$seperator." s.vehicle_status = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","vehicle_status",$vehicle_status,true);
		$seperator=" AND ";
	}
	if ( $type !== "" ) { 
		$where_clause.=$seperator." v.type like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","type","%".$type."%",true);
		$seperator=" AND ";
	}
	if ( $daily_fuel_lmt !== "" ) { 
		$where_clause.=$seperator." s.daily_fuel_lmt = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","daily_fuel_lmt",$daily_fuel_lmt,true);
		$seperator=" AND ";
	}
	if ( $monthly_fuel_lmt !== "" ) { 
		$where_clause.=$seperator." s.monthly_fuel_lmt = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","monthly_fuel_lmt",$monthly_fuel_lmt,true);
		$seperator=" AND ";
	}
	if ( $fuel_filled !== "" ) { 
		$where_clause.=$seperator." s.fuel_filled = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","fuel_filled",$fuel_filled,true);
		$seperator=" AND ";
	}
	if ( $odometer_reading !== "" ) { 
		$where_clause.=$seperator." s.odometer_reading like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","odometer_reading","%".$odometer_reading."%",true);
		$seperator=" AND ";
	}if ( $fuel_image !== "" ) { 
		$where_clause.=$seperator." s.fuel_image like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","fuel_image","%".$fuel_image."%",true);
		$seperator=" AND ";
	}if ( $odometer_image !== "" ) { 
		$where_clause.=$seperator." s.odometer_image like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","odometer_image","%".$odometer_image."%",true);
		$seperator=" AND ";
	}
	if ( $created_st_dt !== "" ) { 
		$where_clause.=$seperator." s.created_dt >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_st_dt,true);
		$seperator=" AND ";
	}
	if ( $created_en_dt !== "" ) { 
		$created_en_dt.=' 23:59:59';
		$where_clause.=$seperator." s.created_dt <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_en_dt,true);
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY $order_by $order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						s.client_name,
						s.reg_no,
						COUNT(s.reg_no),
						v.type,
						v.vehicle_model,
						SUM(fuel_filled) fuel_filled,
						COUNT(fuel_filled) total_fills,
						(MAX(odometer_reading)-MIN(odometer_reading))/SUM(fuel_filled) odometer_reading
					FROM 
						tSearch s
						LEFT OUTER JOIN tVehicle v ON(s.reg_no=v.reg_no AND s.travel_id=v.travel_id)
					$where_clause
					GROUP BY
						reg_no
					$order_by
					LIMIT 10000"
					,$param_arr['params'], 
					false);
					
					
	LOG_MSG('INFO',"db_search_vehicle_report_select(): END");
	return $resp;
}

// SELECT
function db_attendancelog_report_select(
								$student_name="",
								$id_number="",
								$log_st_dt="",
								$log_en_dt="",
								$order_by="",
								$order_by_type=""	) {

		LOG_MSG('INFO',"db_search_vehicle_report_select(): START { 
							order_by=[$order_by],
							order_by_type=[$order_by_type],
							student_name=[$student_name],
							id_number=[$id_number],
							log_st_dt=[$log_st_dt],
							log_en_dt=[$log_en_dt]
							\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE a.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	if ( $student_name !== "" ) { 
		$where_clause.=$seperator." s.name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","student_name","%".$student_name."%",true);
		$seperator=" AND ";
	}
	if ( $id_number !== "" ) { 
		$where_clause.=$seperator." a.id_number like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","id_number","%".$id_number."%",true);
		$seperator=" AND ";
	}
	if ( $log_st_dt !== "" ) { 
		$where_clause.=$seperator." a.log_dt >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_st_dt,true);
		$seperator=" AND ";
	}
	if ( $log_en_dt !== "" ) { 
		$log_en_dt.=' 23:59:59';
		$where_clause.=$seperator." a.log_dt <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_en_dt,true);
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY $order_by $order_by_type ";

	// No where clause
	if ( $where_clause === "WHERE a.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						a.imei,
						a.nfc_tag_id,
						a.id_number,
						s.name student_name,
						a.latitude,
						a.longitude,
						a.address,
						a.comments,
						a.log_dt,
						a.created_dt
					FROM 
						tAttendanceLog a
						LEFT OUTER JOIN tStudent s ON(s.student_id=a.student_id AND s.travel_id=a.travel_id)
					$where_clause
					$order_by
					LIMIT 10000"
					,$param_arr['params'], 
					false);
					
					
	LOG_MSG('INFO',"db_search_vehicle_report_select(): END");
	return $resp;
}





// SMS sent Report
function db_smssent_report_select(
								$name='',
								$from='',
								$to='',
								$message='',
								$response='',
								$status='',
								$smssent_st_dt='',
								$smssent_en_dt='',
								$order_by='',
								$order_by_type=''	) {

	LOG_MSG('INFO',"db_smssent_report_select(): START { 
							name=[$name],
							from=[$from],
							to=[$to],
							message=[$message],
							response=[$response],
							status=[$status],
							smssent_st_dt=[$smssent_st_dt],
							smssent_en_dt=[$smssent_en_dt],
							order_by=[$order_by],
							order_by_type=[$order_by_type]		\n}");

	$param_arr=_init_db_params();

	$where_clause="WHERE ss.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$having_clause="";
	//Only if the entity is supplier

	if ( $name !== "" ) { 
		$where_clause.=" AND sg.name LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","value","%".$name."%",true);
	}
	if ( $from !== "" ) { 
		$where_clause.=" AND ss.from LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","from","%".$from."%",true);
	}
	if ( $to !== "" ) { 
		$where_clause.=" AND ss.to LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","to","%".$to."%",true);
	}
	if ( $message !== "" ) { 
		$where_clause.=" AND ss.message LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","message","%".$message."%",true);
	}
	if ( $response !== "" ) { 
		$where_clause.=" AND ss.response LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","response","%".$response."%",true);
	}
	if ( $status !== "" ) { 
		$where_clause.=" AND ss.status LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","status","%".$status."%",true);
	}
	if ( $smssent_st_dt !== "" ) { 
		$where_clause.=" AND ss.created_dt >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","smssent_st_dt",$smssent_st_dt,true);
		$is_search=true;
	}
	if ( $smssent_en_dt !== "" ) { 
		$where_clause.=" AND ss.created_dt <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","smssent_en_dt",$smssent_en_dt,true);
		$is_search=true;
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY `$order_by` $order_by_type"; 
	else $order_by=" ORDER BY ss.created_dt desc";

	// No where clause
	if ( $where_clause === "WHERE ss.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						sg.name,
						ss.from AS from_no,
						ss.to AS to_no,
						ss.message,
						ss.response,
						ss.status,
						ss.created_dt
					FROM 
						tSMSSent AS ss
						LEFT OUTER JOIN tSMSGateway AS sg ON(ss.smsgateway_id=sg.smsgateway_id)
					$where_clause 
					$order_by
					LIMIT 300" 
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_smssent_report_select(): END");
	return $resp;
}


function db_studentlog_select(
			$student_id="",
			$created_st_dt="",
			$created_en_dt="") {


	LOG_MSG('INFO',"db_studentlog_select(): START { 
							student_id=[$student_id],
							time_in_st_dt=[$created_st_dt],
							time_in_en_dt=[$created_en_dt]	\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE ul.travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." ul.student_id like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","student_id","%".$student_id."%",true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $created_st_dt !== "" ) { 
		$where_clause.=$seperator." ul.time_in >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_st_dt,true);
		$seperator=" AND ";
	}
	if ( $created_en_dt !== "" ) { 
		$created_en_dt.=' 23:59:59';
		$where_clause.=$seperator." ul.time_in <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_en_dt,true);
	}
	
	
	// No where clause
	if ( $where_clause === "WHERE ul.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_studentlog_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						ul.student_id,
						ul.time_in,
						ul.time_out,
						ul.time_of_day,
						ul.client_name,
						ul.route,
						u.name,
						u.created_dt
					FROM 
						tStudentLog ul
						LEFT OUTER JOIN tStudent u ON(u.student_id=ul.student_id)
						LEFT OUTER JOIN tClient c ON(c.name=ul.client_name)
					$where_clause 
					ORDER BY
						ul.student_id"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_studentlog_select(): END");
	return $resp;
}

function db_student_report_select(
								$student_name="",
								$guardian_name="",
								$id_number="",
								$client_name="",
								$created_st_dt="",
								$created_en_dt="",
								$order_by='',
								$order_by_type=''	) {

		LOG_MSG('INFO',"db_student_report_select(): START { 
							created_st_dt=[$created_st_dt],
							created_en_dt=[$created_en_dt],
							order_by=[$order_by],
							order_by_type=[$order_by_type]\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE sl.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $student_name !== "" ) { 
		$where_clause.=$seperator." s.name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","student_name","%".$student_name."%",true);
		$seperator=" AND ";
	}
	if ( $guardian_name !== "" ) { 
		$where_clause.=$seperator." s.guardian_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","guardian_name","%".$guardian_name."%",true);
		$seperator=" AND ";
	}
	if ( $id_number !== "" ) { 
		$where_clause.=$seperator." s.id_number like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","id_number","%".$id_number."%",true);
		$seperator=" AND ";
	}
	if ( $client_name !== "" ) { 
		$where_clause.=$seperator." sl.client_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","client_name","%".$client_name."%",true);
		$seperator=" AND ";
	}
	if ( $created_st_dt !== "" ) { 
		$where_clause.=$seperator." sl.created_dt >= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_st_dt,true);
		$seperator=" AND ";
	}
	if ( $created_en_dt !== "" ) { 
		$created_en_dt.=' 23:59:59';
		$where_clause.=$seperator." sl.created_dt <= ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_en_dt,true);
	}

	// Order By
	if ( $order_by != '' ) $order_by=" ORDER BY `$order_by` $order_by_type"; 
	else $order_by=" ORDER BY sl.created_dt desc";

	// No where clause
	if ( $where_clause === "WHERE sl.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						sl.student_id,
						s.name student_name,
						s.id_number,
						sl.time_in,
						sl.time_out,
						sl.time_of_day,
						sl.reg_no,
						sl.st_reg_no,
						sl.client_name,
						sl.route,
						sl.check_in_image,
						sl.check_out_image,
						s.student_photo,
						s.guardian_name,
						sl.created_dt
					FROM 
						tStudentLog sl
						LEFT OUTER JOIN tGuardianLog g ON ( g.studentlog_id = sl.studentlog_id AND g.travel_id=sl.travel_id )
						LEFT OUTER JOIN tStudent s ON ( sl.student_id = s.student_id AND sl.travel_id=s.travel_id ) 
						$where_clause
						$order_by
					LIMIT 300" 
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_search_report_select(): END");
	return $resp;
}



function db_iisclog_report_select(
								$student_name="",
								$id_number="",
								$created_dt="") {

	LOG_MSG('INFO',"db_iisclog_report_select(): START { \n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE il.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $student_name !== "" ) { 
		$where_clause.=$seperator." s.name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","student_name","%".$student_name."%",true);
		$seperator=" AND ";
	}
	if ( $id_number !== "" ) { 
		$where_clause.=$seperator." s.id_number like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","id_number","%".$id_number."%",true);
		$seperator=" AND ";
	}
	if ( $created_dt !== "" ) { 
		$where_clause.=$seperator." il.created_dt LIKE ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",'%'.$created_dt.'%',true);
		$seperator=" AND ";
	}
	// No where clause
	if ( $where_clause === "WHERE il.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						il.student_id,
						s.name student_name,
						s.id_number,
						il.reg_no,
						il.image,
						s.student_photo,
						il.created_dt
					FROM 
						tIISCLog il
						LEFT OUTER JOIN tStudent s ON ( il.student_id = s.student_id AND il.travel_id=s.travel_id ) 
						$where_clause"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_iisclog_report_select(): END");
	return $resp;
}

?>
