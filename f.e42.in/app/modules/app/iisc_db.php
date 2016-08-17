<?php 

function db_iisclog_select(
			$iisclog_id="",
			$student_id="",
			$reg_no="",
			$image="",
			$created_dt="",
			$log_dt="") {

	LOG_MSG('INFO',"db_iisclog_select(): START { 
							iisclog_id=[$iisclog_id],
							student_id=[$student_id],
							reg_no=[$reg_no],
							image=[$image],
							created_dt=[$created_dt] 
							log_dt=[$log_dt] 
							\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $iisclog_id !== "" ) { 
		$where_clause.=$seperator." il.iisclog_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","iisclog_id",$iisclog_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." il.student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $reg_no !== "" ) { 
		$where_clause.=$seperator." il.reg_no like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $image !== "" ) { 
		$where_clause.=$seperator." il.image like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","image","%".$image."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $created_dt !== "" ) { 
		$where_clause.=$seperator." il.created_dt like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $log_dt !== "" ) { 
		$where_clause.=$seperator." il.log_dt = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_dt,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_iisclog_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						il.iisclog_id,
						il.student_id,
						il.reg_no,
						il.image,
						il.created_dt,
						s.name,
						s.id_number,
						s.phone
					FROM 
						tIISCLog il
						LEFT OUTER JOIN tStudent s ON(s.student_id=il.student_id AND s.travel_id=il.travel_id)
					$where_clause 
					ORDER BY
						il.student_id"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_iisclog_select(): END");
	return $resp;
}

function db_iisclog_insert(
							$student_id,
							$reg_no,
							$log_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_iisclog_insert(): START { 
							student_id=[$student_id]]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","log_dt",$log_dt);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tIISCLog
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_iisclog_insert(): END");
	return $resp;

}



// UPDATE
function db_iisclog_update($iisclog_id,
							$image="") {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_iisclog_update(): START {
							iisclog_id=[$iisclog_id],
							image=[$time_out]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","image",$image);

	// For the where clause
	$where_clause=" 	WHERE 
							iisclog_id =? AND travel_id=".TRAVEL_ID;	// Do not update the time out if tapped for the second time
	$param_arr=_db_prepare_param($param_arr,"i","iisclog_id",$iisclog_id,true);

	$resp=execSQL("UPDATE  
						tIISCLog
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_iisclog_update(): END");
	return $resp;

}
function db_iisc_location_select(
			$imei="",
			$location_id="",
			$imeiloc_id="") {

	LOG_MSG('INFO',"db_iisc_location_select(): START { 
							imei=[$imei]
							imeiloc_id=[$imeiloc_id]
							\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $imei !== "" ) { 
		$where_clause.=$seperator." imei = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $imeiloc_id !== "" ) { 
		$where_clause.=$seperator." imeiloc_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","imeiloc_id",$imeiloc_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_iisc_location_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						location_id,
						imei,
						travel_id
					FROM 
						tImeiLocation
					$where_clause "
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_iisc_location_select(): END");
	return $resp;
}

function db_iisc_location_group_select(
			$location_id="",
			$stlocgrp_id="") {

	LOG_MSG('INFO',"db_iisc_location_group_select(): START { 
							location_id=[$location_id]
							stlocgrp_id=[$stlocgrp_id]
							\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $location_id !== "" ) { 
		$where_clause.=$seperator." location_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	// WHERE CLAUSE
	if ( $stlocgrp_id !== "" ) { 
		$where_clause.=$seperator." stlocgrp_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","stlocgrp_id",$stlocgrp_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_iisc_location_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						stlocgrp_id,
						location_id,
						group_id,
						travel_id
					FROM 
						tStLocGroup
					$where_clause "
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_iisc_location_group_select(): END");
	return $resp;
}

function db_student_group_select(
			$student_id="",
			$stgrp_id="") {

	LOG_MSG('INFO',"db_iisc_location_group_select(): START { 
							student_id=[$student_id],
							stgrp_id=[$stgrp_id]
							\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// WHERE CLAUSE
	if ( $stgrp_id !== "" ) { 
		$where_clause.=$seperator." stgrp_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","stgrp_id",$stgrp_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_iisc_location_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						stgrp_id,
						student_id,
						group_id,
						travel_id
					FROM 
						tStudentGroup
					$where_clause "
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_iisc_location_group_select(): END");
	return $resp;
}
?>
