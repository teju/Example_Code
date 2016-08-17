<?php
function db_sync_to_sqlite($student_id="") {
	LOG_MSG('INFO',"db_sync_to_sqlite: START student_id=[$student_id]" );

	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." s.student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_sync_to_sqlite(): WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						s.student_id,
						s.name,
						s.student_photo,
						s.id_number,
						s.phone,
						s.guardian_name,
						s.email_id,
						s.guardian_photo,
						s.travel_id,
						s.exp_dt,
						s.is_active,
						s.client_id,
						v.reg_no st_reg_no
					FROM 
						tStudent s
						LEFT OUTER JOIN tVehicle v ON(v.vehicle_id=s.vehicle_id AND v.travel_id=s.travel_id) "
					.$where_clause
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_sync_to_sqlite(): END");
	return $resp;

} 

function db_sync_vehicle_select(
								$vehicle_id="") {
	LOG_MSG('INFO',"db_sync_vehicle_select: START" );

	$param_arr=_init_db_params();
	$where_clause=" WHERE v.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $vehicle_id !== "" ) { 
		$where_clause.=$seperator." vehicle_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","vehicle_id",$vehicle_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === " WHERE v.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}

	LOG_MSG('INFO',"db_sync_vehicle_select(): WHERE CLAUSE = [$where_clause]");
	$resp=execSQL("SELECT 
						v.vehicle_id,
						v.reg_no,
						v.route,
						v.client_id,
						v.supervisor_id,
						v.travel_id,
						v.is_active
					FROM 
						tVehicle v
					$where_clause "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_sync_vehicle_select(): END");
	return $resp;

} 

// DELETE
function db_appsync_delete() {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_appsync_delete(): START ");

	// For the where clause
	$where_clause=" WHERE travel_id=".TRAVEL_ID;

	$resp=execSQL("DELETE FROM  
						tAppSync"
					.$where_clause
					,array(), 
					true);

	LOG_MSG('INFO',"db_appsync_delete(): END");
	return $resp;
} 

// AppSync select
function db_appsync_select() {

	LOG_MSG('INFO',"db_appsync_select: START" );

	$param_arr=_init_db_params();

	$resp=execSQL("SELECT 
						table_name,
						primary_id,
						status,
						travel_id
					FROM 
						tAppSync
					WHERE travel_id =".TRAVEL_ID
					,array(), 
					false);

	LOG_MSG('INFO',"db_appsync_select(): END");
	return $resp;

} 

// AppSync select
function db_supervisor_update($imei,$is_sync) {
 
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_supervisor_update(): START {
							imei=[$imei] 
							is_sync=[$is_sync] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","is_sync",$is_sync);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);

	// For the where clause
	$where_clause=" WHERE imei like ? AND travel_id=".TRAVEL_ID;

	$resp=execSQL("UPDATE  
						tSupervisor
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_supervisor_update(): END");
	return $resp;
}

// SELECT
function db_iisc_group_select($group_id = "") {

	LOG_MSG('INFO',"db_iisc_group_select: START { }");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	if ( $group_id !== "" ) { 
		$where_clause.=$seperator." group_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_iisc_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						group_id,
						group_name,
						is_active,
						created_dt,
						travel_id
					FROM 
						tGroup
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_iisc_group_select(): END");
	return $resp;
}

// SELECT
function db_sync_location_select($location_id="") {

	LOG_MSG('INFO',"db_iisc_location_select: START { }");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	if ( $location_id !== "" ) { 
		$where_clause.=$seperator." location_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_iisc_location_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						location_id,
						location_name,
						created_dt,
						travel_id
					FROM 
						tLocation
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_iisc_location_select(): END");
	return $resp;
}

?>