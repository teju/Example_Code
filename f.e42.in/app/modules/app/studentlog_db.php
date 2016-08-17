<?php

// SELECT
function db_studentlog_select(
			$studentlog_id="",
			$student_id="",
			$time_of_day="",
			$reg_no="",
			$client_name="",
			$route="") {

	LOG_MSG('INFO',"db_studentlog_select(): START { 
							studentlog_id=[$studentlog_id],
							student_id=[$student_id],
							time_of_day=[$time_of_day],
							reg_no=[$reg_no],
							client_name=[$client_name],
							route=[$route],

							\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $studentlog_id !== "" ) { 
		$where_clause.=$seperator." ul.studentlog_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","studentlog_id",$studentlog_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." ul.student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $time_of_day !== "" ) { 
		$where_clause.=$seperator." ul.time_of_day like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","time_of_day","%".$time_of_day."%",true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." ul.reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator="AND ";
		$is_search=true;
	}if ( $client_name !== "" ) { 
		$where_clause.=$seperator." ul.client_name like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","client_name","%".$client_name."%",true);
		$seperator="AND ";
		$is_search=true;
	}if ( $route !== "" ) { 
		$where_clause.=$seperator." ul.route like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","route","%".$route."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_studentlog_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						ul.studentlog_id,
						ul.student_id,
						ul.time_in,
						ul.time_of_day,
						ul.reg_no,
						ul.client_name,
						ul.route,
						s.name,
						s.id_number,
						s.phone
					FROM 
						tStudentLog ul
						LEFT OUTER JOIN tStudent s ON(s.student_id=ul.student_id AND s.travel_id=ul.travel_id)
					$where_clause 
					ORDER BY
						ul.student_id"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_studentlog_select(): END");
	return $resp;
}

// INSERT
function db_studentlog_insert(
							$student_id,
							$log_dt,
							$time_of_day,
							$reg_no,
							$st_reg_no,
							$client_name,
							$route,
							$latitude,
							$longitude,
							$address
							) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_studentlog_insert(): START { 
							student_id=[$student_id],
							time_of_day=[$time_of_day]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_dt);
	$param_arr=_db_prepare_param($param_arr,"s","time_of_day",$time_of_day);
	$param_arr=_db_prepare_param($param_arr,"s","route",$route);
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","st_reg_no",$st_reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","client_name",$client_name);
	$param_arr=_db_prepare_param($param_arr,"s","latitude",$latitude);
	$param_arr=_db_prepare_param($param_arr,"s","longitude",$longitude);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tStudentLog
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_studentlog_insert(): END");
	return $resp;

}

// UPDATE
function db_studentlog_update($studentlog_id,
						$time_out="",
						$check_in_image="",
						$check_out_image="") {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_studentlog_update(): START {
							studentlog_id=[$studentlog_id],
							time_out=[$time_out],
							check_in_image=[$check_in_image],
							check_out_image=[$check_out_image]\n}");

	// Add params to params_arr
	if ( $time_out != '' ) $param_arr=_db_prepare_param($param_arr,"s","time_out",$time_out);
	if ( $check_in_image != '' ) $param_arr=_db_prepare_param($param_arr,"s","check_in_image",$check_in_image);
	if ( $check_out_image != '' ) $param_arr=_db_prepare_param($param_arr,"s","check_out_image",$check_out_image);

	// For the where clause
	$where_clause=" 	WHERE 
							studentlog_id =? AND travel_id=".TRAVEL_ID;	// Do not update the time out if tapped for the second time
	$param_arr=_db_prepare_param($param_arr,"i","studentlog_id",$studentlog_id,true);

	$resp=execSQL("UPDATE  
						tStudentLog
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_studentlog_update(): END");
	return $resp;

}


function db_guardian_insert(
							$time_of_day,
							$created_dt ) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_guardian_insert(): START { 
							created_dt=[$created_dt],
							time_of_day=[$time_of_day]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","time_of_day",$time_of_day);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);
	$param_arr=_db_prepare_param($param_arr,"s","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tGuardianLog
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_guardian_insert(): END");
	return $resp;

}

function db_guardian_update(
							$studentlog_id,
							$time_of_day,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_guardian_update(): START {
							studentlog_id=[$studentlog_id]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","studentlog_id",$studentlog_id);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
	$param_arr=_db_prepare_param($param_arr,"s","time_of_day",$time_of_day,true);

	// For the where clause
	$where_clause=" 	WHERE 
							 created_dt like ? AND time_of_day = ? AND travel_id=".TRAVEL_ID ;

	$resp=execSQL("UPDATE  
						tGuardianLog
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_guardian_update(): END");
	return $resp;

}

function db_guardianlog_select() {

	LOG_MSG('INFO',"db_studentlog_select(): START");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id =".TRAVEL_ID;
	$seperator=" WHERE ";
	$is_search=false;

	$resp=execSQL("SELECT 
						time_of_day,
						created_dt
					FROM
						tGuardianLog" 
					,array(), 
					false);
	LOG_MSG('INFO',"db_studentlog_select(): END");
	return $resp;
}

function db_guardian_photo_update(
							$guardian_id,
							$guardian_image) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_guardian_photo_update(): START {
							guardian_id=[$guardian_id]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","photo",$guardian_image);
	$param_arr=_db_prepare_param($param_arr,"i","guardian_id",$guardian_id,true);

	// For the where clause
	$where_clause=" 	WHERE 
							guardian_id = ? AND travel_id= ".TRAVEL_ID;
	$resp=execSQL("UPDATE  
						tGuardianLog
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_guardian_photo_update(): END");
	return $resp;

}

?>
