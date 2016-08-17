<?php

//SELECT
function db_syncstudentlog_select(
			$student_id="",
			$time_in="") {

	LOG_MSG('INFO',"db_syncstudentlog_select: START { 
							student_id=[$student_id],
							time_in=[$time_in] \n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
		if ( $student_id !== "" ) { 
			$where_clause.=$seperator." student_id = ? ";
			$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $time_in !== "" ) { 
			$where_clause.=$seperator." time_in like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","time_in",$time_in."%",true);
			$seperator="AND ";
			$is_search=true;
		}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_syncstudentlog_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						student_id,
						time_in,
						time_of_day
					FROM 
						tStudentLog
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_syncstudentlog_select(): END");
	return $resp;
}

// INSERT
function db_synclog_insert(
							$student_id,
							$time_in,
							$time_out,
							$time_of_day,
							$reg_no,
							$st_reg_no,
							$client_name,
							$route,
							$check_in_image,
							$check_out_image,
							$latitude,
							$longitude,
							$address,
							$travel_id){
							
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_synclog_insert: START { 
							student_id=[$student_id],
							time_in=[$time_in],
							time_out=[$time_out],
							time_of_day=[$time_of_day],
							reg_no=[$reg_no],
							st_reg_no=[$st_reg_no],
							client_name=[$client_name],
							route=[$route],
							check_in_image=[$check_in_image],
							check_out_image=[$check_out_image],
							latitude=[$latitude],
							longitude=[$longitude],
							address=[$address],
							travel_id=[$travel_id]							\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"s","time_in",$time_in);
	$param_arr=_db_prepare_param($param_arr,"s","time_out",$time_out);
	$param_arr=_db_prepare_param($param_arr,"s","time_of_day",$time_of_day);
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","st_reg_no",$st_reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","client_name",$client_name);
	$param_arr=_db_prepare_param($param_arr,"s","route",$route);
	$param_arr=_db_prepare_param($param_arr,"s","check_in_image",$check_in_image);
	$param_arr=_db_prepare_param($param_arr,"s","check_out_image",$check_out_image);
	$param_arr=_db_prepare_param($param_arr,"s","latitude",$latitude);
	$param_arr=_db_prepare_param($param_arr,"s","longitude",$longitude);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","travel_id",$travel_id);

	$resp=execSQL("INSERT INTO 
						tStudentLog
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_synclog_insert(): END");
	return $resp;

}

// UPDATE
function db_sync_studentlog_update($student_id,
						$time_in="",
						$time_out="",
						$check_out_image="",
						$check_in_image="") {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_sync_studentlog_update(): START {
							student_id=[$student_id],
							time_in=[$time_in],
							time_out=[$time_out],
							check_out_image=[$check_out_image],
							check_in_image=[$check_in_image]\n}");

	// Add params to params_arr
	if ( $time_out != '' ) $param_arr=_db_prepare_param($param_arr,"s","time_out",$time_out);
	if ( $check_in_image != '' ) $param_arr=_db_prepare_param($param_arr,"s","check_in_image",$check_in_image);
	if ( $check_out_image != '' ) $param_arr=_db_prepare_param($param_arr,"s","check_out_image",$check_out_image);

	// For the where clause
	$where_clause=" 	WHERE 
							student_id =? AND time_in like ? AND  travel_id=".TRAVEL_ID;	// Do not update the time out if tapped for the second time
	$param_arr=_db_prepare_param($param_arr,"s","student_id",$student_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","time_in","%".$time_in."%",true);

	$resp=execSQL("UPDATE  
						tStudentLog
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_sync_studentlog_update(): END");
	return $resp;
}

// INSERT
function db_attendance_log_insert(
							$imei,
							$nfc_tag_id,
							$id_number,
							$student_id,
							$latitude,
							$longitude,
							$address,
							$comments,
							$log_dt){
							
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_synclog_insert: START { 
							imei=[$imei],
							nfc_tag_id=[$nfc_tag_id],
							id_number=[$id_number],
							student_id=[$student_id],
							latitude=[$latitude],
							longitude=[$longitude],
							address=[$address],
							comments=[$comments],
							log_dt=[$log_dt]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","nfc_tag_id",$nfc_tag_id);
	$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number);
	$param_arr=_db_prepare_param($param_arr,"s","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"s","latitude",$latitude);
	$param_arr=_db_prepare_param($param_arr,"s","longitude",$longitude);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","comments",$comments);
	$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_dt);
	$param_arr=_db_prepare_param($param_arr,"s","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tAttendanceLog
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_synclog_insert(): END");
	return $resp;

}
// UPDATE
function db_sync_iisclog_update($student_id,
								$log_dt,
								$image="") {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_sync_iisclog_update(): START {
							student_id=[$student_id],
							log_dt=[$log_dt],
							image=[$image]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","image",$image);

	// For the where clause
	$where_clause=" 	WHERE 
							student_id =? AND log_dt = ? AND travel_id=".TRAVEL_ID;	// Do not update the time out if tapped for the second time
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_dt,true);

	$resp=execSQL("UPDATE  
						tIISCLog
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_sync_iisclog_update(): END");
	return $resp;

}
?>
