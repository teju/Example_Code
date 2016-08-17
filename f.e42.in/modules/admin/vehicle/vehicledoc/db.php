<?php
// INSERT
function db_vehicledoc_insert(
							$doc_name,
							$vehicle_id) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_vehicledoc_insert(): START { 
							doc_name=[$doc_name],
							vehicle_id=[$vehicle_id] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","doc_name",$doc_name);
	$param_arr=_db_prepare_param($param_arr,"i","vehicle_id",$vehicle_id);

	$resp=execSQL("INSERT INTO 
						tVehicleDoc
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_vehicledoc_insert(): END");
	return $resp;

}
function db_vehicledoc_select(
			$vehicledoc_id="",
			$doc_doc_name="",
			$vehicle_id="") {


	LOG_MSG('INFO',"db_vehicledoc_select: START { 
							vehicledoc_id=[$vehicledoc_id],
							doc_name=[$doc_name],
							vehicle_id=[$vehicle_id] \n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $vehicledoc_id !== "" ) { 
		$where_clause.=$seperator." vehicledoc_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","vehicledoc_id",$vehicledoc_id,true);
	} else {
		if ( $doc_name !== "" ) { 
			$where_clause.=$seperator." doc_name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","doc_name","%".$doc_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $vehicle_id !== "" ) { 
			$where_clause.=$seperator." vehicle_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","vehicle_id","%".$vehicle_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_vehicledoc_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						vehicledoc_id,
						doc_name,
						vehicle_id,
						FROM 
					tVehicleDoc
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_vehicledoc_select(): END");
	return $resp;
}


?>
