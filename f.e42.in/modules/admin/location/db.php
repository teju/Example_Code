<?php

// SELECT
function db_location_select(
			$location_id="",
			$location_name="") {

	LOG_MSG('INFO',"db_location_select: START { 
							location_id=[$location_id],
							location_name=[$location_name]\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $location_id !== "" ) { 
		$where_clause.=$seperator." location_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
	} else {
		if ( $location_name !== "" ) { 
			$where_clause.=$seperator." location_name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","location_name","%".$location_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_location_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						location_id,
						location_name
					FROM 
						tLocation
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_location_select(): END");
	return $resp;
}

// INSERT
function db_location_insert(
							$location_name) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_location_insert(): START { 
							location_name=[$location_name]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","location_name",$location_name);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tLocation
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_location_insert(): END");
	return $resp;

}

// UPDATE
function db_location_update(	
							$location_id,
							$location_name) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_location_update(): START {
							location_id=[$location_id],
							location_name=[$location_name]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","location_name",$location_name);

	// For the where clause
	$where_clause=" WHERE location_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);


	$resp=execSQL("UPDATE  
						tLocation
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_location_update(): END");
	return $resp;
}

// DELETE
function db_location_delete($location_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_location_delete(): START { location_id=[$location_id]");

	// For the where clause
	$where_clause=" WHERE location_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);

	$resp=execSQL("DELETE FROM  
						tLocation"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_location_delete(): END");
	return $resp;
}

function db_location_imei_delete($imei) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_location_imei_delete(): START { imei=[$imei]");

	// For the where clause
	$where_clause=" WHERE imei=? AND travel_id = ".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);

	$resp=execSQL("DELETE FROM  
						tImeiLocation"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_location_imei_delete(): END");
	return $resp;
}

function db_location_imei_insert($location_id,$imei) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_location_imei_insert(): START { 
							location_id=[$location_id],
							imei=[$imei]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tImeiLocation
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_location_imei_insert(): END");
	return $resp;
}
// SELECT
function db_imei_location_select($location_id) {

	LOG_MSG('INFO',"db_imei_location_select: START { }");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $location_id !== "" ) { 
		$where_clause.=$seperator." location_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
	} 

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_imei_location_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						location_id,
						imei
					FROM 
						tImeiLocation
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_imei_location_select(): END");
	return $resp;
}
?>
