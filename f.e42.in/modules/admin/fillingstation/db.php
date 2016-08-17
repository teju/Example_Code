<?php


// SELECT
function db_filling_station_select(
			$fs_id="",
			$imei="",
			$name="",
			$location="",
			$fuel_rate="") {


	LOG_MSG('INFO',"db_filling_station_select: START { 
							fs_id=[$fs_id],
							imei=[$imei],
							name=[$name],
							location=[$location],
							fuel_rate=[$fuel_rate]\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $fs_id !== "" ) { 
		$where_clause.=$seperator." fs_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","fs_id",$fs_id,true);
	} else {
		if ( $imei !== "" ) { 
			$where_clause.=$seperator." imei like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $location !== "" ) { 
			$where_clause.=$seperator." location like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","location","%".$location."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $fuel_rate !== "" ) { 
			$where_clause.=$seperator." fuel_rate like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","fuel_rate","%".$fuel_rate."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_filling_station_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						fs_id,
						imei,
						name,
						location,
						fuel_rate
					FROM 
						tFillingStation
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_filling_station_select(): END");
	return $resp;
}


// INSERT
function db_filling_station_insert(
							$imei,
							$name,
							$location,
							$fuel_rate) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_filling_station_insert(): START { 
							imei=[$imei],
							name=[$name],
							location=[$location],
							fuel_rate=[$fuel_rate]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","location",$location);
	$param_arr=_db_prepare_param($param_arr,"s","fuel_rate",$fuel_rate);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	

	$resp=execSQL("INSERT INTO 
						tFillingStation
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_filling_station_insert(): END");
	return $resp;

}



// UPDATE
function db_filling_station_update(	
							$fs_id,
							$imei,
							$name,
							$location,
							$fuel_rate) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_filling_station_update(): START {
							fs_id=[$fs_id],
							imei=[$imei],
							name=[$name],
							location=[$location],
							fuel_rate=[$fuel_rate]}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","location",$location);
	$param_arr=_db_prepare_param($param_arr,"s","fuel_rate",$fuel_rate);
	
	// For the where clause
	$where_clause=" WHERE fs_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","fs_id",$fs_id,true);


	$resp=execSQL("UPDATE  
						tFillingStation
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_filling_station_update(): END");
	return $resp;
}


// DELETE
function db_filling_station_delete($fs_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_filling_station_delete(): START { fs_id=[$fs_id]");

	// For the where clause
	$where_clause=" WHERE fs_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","fs_id",$fs_id,true);


	$resp=execSQL("DELETE FROM  
						tFillingStation"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_filling_station_delete(): END");
	return $resp;
}


?>
