<?php


// SELECT
function db_student_select(
			$id_number="",
			$student_id="") {

	LOG_MSG('INFO',"db_student_select: START { 
			id_number=[$id_number]
			student_id=[$student_id]
			\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE s.is_active=1 AND s.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	if ( $id_number !== "" ) { 
		$where_clause.=$seperator." s.id_number = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." s.student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE s.is_active=1 AND s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_student_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						s.student_id,
						s.name,
						s.student_photo,
						s.id_number,
						s.phone,
						s.guardian_name,
						s.email_id,
						s.guardian_photo,
						s.is_active,
						s.created_dt,
						s.vehicle_id,
						s.exp_dt,
						v.reg_no,
						s.client_id,
						c.name client_name,
						s.is_active
					FROM 
						tStudent s
						LEFT OUTER JOIN tVehicle v ON(v.vehicle_id=s.vehicle_id)
						LEFT OUTER JOIN tClient c ON(c.client_id=s.client_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_student_select(): END");
	return $resp;
}

function db_att_vehicle_select($imei) {

	LOG_MSG('INFO',"db_att_vehicle_select(: START { 
							imei=[$imei] \n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);

	$resp=execSQL("	SELECT 
						v.vehicle_id,
						v.reg_no,
						v.route,
						v.supervisor_id,
						v.client_id,
						v.travel_id,
						c.name client_name,
						v.is_active
					FROM
						tSupervisor s 
						LEFT OUTER JOIN  tVehicle v ON(s.supervisor_id=v.supervisor_id)
						LEFT OUTER JOIN tClient c ON(c.client_id=v.client_id)
					WHERE
						s.imei = ? AND v.is_active =1 AND
						v.travel_id=".TRAVEL_ID
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_att_vehicle_select(): END");
	return $resp;
}

// SELECT
function db_nfctag_select(
			$nfc_tag_id="",
			$id_number="",
			$created_dt="") {

	LOG_MSG('INFO',"db_nfctag_select(): START { 
							nfc_tag_id=[$nfc_tag_id],
							id_number=[$id_number],
							created_dt=[$created_dt]	\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $nfc_tag_id !== "" ) { 
		$where_clause.=$seperator." nfc_tag_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","nfc_tag_id",$nfc_tag_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $id_number !== "" ) { 
		$where_clause.=$seperator." id_number = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $created_dt !== "" ) { 
		$where_clause.=$seperator." created_dt like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_nfctag_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						nfc_tag_id,
						id_number,
						created_dt,
						type,
						travel_id
					FROM 
						tNFCTag
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_nfctag_select(): END");
	return $resp;
}

function db_guardian_select(
			$time_in="",
			$time_of_day="") {

	LOG_MSG('INFO',"db_guardian_select(): START { 
							time_in=[$time_in],
							time_of_day=[$time_of_day]
							\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $time_in !== "" ) { 
		$where_clause.=$seperator." time_in like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","time_in","%".$time_in."%",true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $time_of_day !== "" ) { 
		$where_clause.=$seperator." time_of_day like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","time_of_day","%".$time_of_day."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_guardian_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						time_of_day,
						created_dt
					FROM 
						tGuardianLog
					WHERE
						time_of_day like ? AND
						created_dt like ?"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_guardian_select(): END");
	return $resp;
}

function db_attendance_setting_select($name) {

	LOG_MSG('INFO',"db_attendance_setting_select (: START { 
							name=[$name] \n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);

	$resp=execSQL("	SELECT 
						setting_id,
						name,
						value,
						travel_id
					FROM
						tSetting
					WHERE
						name = ? AND 
						travel_id = ".TRAVEL_ID
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_setting_select(): END");
	return $resp;
}

function db_get_attendance_capture_image_count($student_id,$image_capture_range) {

	LOG_MSG('INFO',"db_get_capture_image_count(: START { 
							student_id=[$student_id],
							image_capture_range=[$image_capture_range] \n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","student_id",$student_id,true);

	$resp=execSQL("	SELECT 
						student_id,
						student_image
					FROM
						tStudentLog
					WHERE
						student_id = ? 
					ORDER BY 
						studentlog_id DESC
					LIMIT 
						$image_capture_range"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_capture_image_count(): END");
	return $resp;
}

function db_att_client_select(
			$client_id="") {

	LOG_MSG('INFO',"db_att_client_select(): START { 
							client_id=[$client_id]	\n}");

		$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $client_id !== "" ) { 
		$where_clause.=$seperator." client_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","client_id",$client_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_att_client_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						client_id,
						name,
						mobile,
						travel_id
					FROM 
						tClient
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_att_client_select(): END");
	return $resp;
}

function db_att_supervisor_select(
			$imei="",
			$is_sync="",
			$supervisor_id="") {

	LOG_MSG('INFO',"db_att_supervisor_select(): START { 
							is_sync=[$is_sync]	
							supervisor_id=[$supervisor_id]	\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $is_sync !== "" ) { 
		$where_clause.=$seperator." is_sync = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","is_sync",$is_sync,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $imei !== "" ) { 
		$where_clause.=$seperator." imei like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $supervisor_id !== "" ) { 
		$where_clause.=$seperator." supervisor_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","supervisor_id",$supervisor_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_att_supervisor_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						supervisor_id,
						imei,
						travel_id,
						is_sync,
						is_active
					FROM 
						tSupervisor
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_att_supervisor_select(): END");
	return $resp;						
}

?>
