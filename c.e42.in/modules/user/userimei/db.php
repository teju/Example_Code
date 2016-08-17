<?php


// SELECT
function db_userimei_select(
			$user_id="",
			$imei="",
			$created_dt="") {


	LOG_MSG('INFO',"db_userimei_select: START { 
							user_id=[$user_id],
							imei=[$imei],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $user_id !== "" ) { 
		$where_clause.=$seperator." user_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);
	} else {
		if ( $imei !== "" ) { 
			$where_clause.=$seperator." imei like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_userimei_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						user_id,
						imei,
						created_dt
					FROM 
					tUserIMEI
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_userimei_select(): END");
	return $resp;
}


// INSERT
function db_userimei_insert(
							$imei,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_userimei_insert(): START { 
							imei=[$imei],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);

	$resp=execSQL("INSERT INTO 
						tUserIMEI
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_userimei_insert(): END");
	return $resp;

}



// UPDATE
function db_userimei_update(	
							$user_id,
							$imei,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_userimei_update(): START {
							user_id=[$user_id],
							imei=[$imei],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);

	// For the where clause
	$where_clause=" WHERE imei=?";
	$param_arr=_db_prepare_param($param_arr,"i","imei",$imei,true);


	$resp=execSQL("UPDATE  
						tUserIMEI
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_userimei_update(): END");
	return $resp;
}



// DELETE
function db_userimei_delete($imei) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_userimei_delete(): START { imei=[$imei]");

	// For the where clause
	$where_clause=" WHERE imei=?";
	$param_arr=_db_prepare_param($param_arr,"i","imei",$imei,true);


	$resp=execSQL("DELETE FROM  
						tUserIMEI"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_userimei_delete(): END");
	return $resp;
}


?>
