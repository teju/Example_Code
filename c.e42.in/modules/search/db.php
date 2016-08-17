<?php


// SELECT
function db_search_select(
			$search_id="",
			$user_id="",
			$certificate_id="",
			$search_result="",
			$created_dt="") {


	LOG_MSG('INFO',"db_search_select: START { 
							search_id=[$search_id],
							user_id=[$user_id],
							certificate_id=[$certificate_id],
							search_result=[$search_result],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $search_id !== "" ) { 
		$where_clause.=$seperator." search_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","search_id",$search_id,true);
	} else {
		if ( $user_id !== "" ) { 
			$where_clause.=$seperator." user_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","user_id","%".$user_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $certificate_id !== "" ) { 
			$where_clause.=$seperator." certificate_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","certificate_id","%".$certificate_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $search_result !== "" ) { 
			$where_clause.=$seperator." search_result like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","search_result","%".$search_result."%",true);
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
	LOG_MSG('INFO',"db_search_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						search_id,
						user_id,
						certificate_id,
						search_result,
						created_dt				FROM 
					tSearch
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_search_select(): END");
	return $resp;
}


// INSERT
function db_search_insert(
							$user_id,
							$certificate_id,
							$search_result,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_search_insert(): START { 
							user_id=[$user_id],
							certificate_id=[$certificate_id],
							search_result=[$search_result],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id);
		$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id);
		$param_arr=_db_prepare_param($param_arr,"s","search_result",$search_result);
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);

	$resp=execSQL("INSERT INTO 
						tSearch
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_search_insert(): END");
	return $resp;

}



// UPDATE
function db_search_update(	
							$search_id,
							$user_id,
							$certificate_id,
							$search_result,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_search_update(): START {
							search_id=[$search_id],
							user_id=[$user_id],
							certificate_id=[$certificate_id],
							search_result=[$search_result],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id);
		$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id);
		$param_arr=_db_prepare_param($param_arr,"s","search_result",$search_result);
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);

	// For the where clause
	$where_clause=" WHERE search_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","search_id",$search_id,true);


	$resp=execSQL("UPDATE  
						tSearch
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_search_update(): END");
	return $resp;
}



// DELETE
function db_search_delete($search_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_search_delete(): START { search_id=[$search_id]");

	// For the where clause
	$where_clause=" WHERE search_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","search_id",$search_id,true);


	$resp=execSQL("DELETE FROM  
						tSearch"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_search_delete(): END");
	return $resp;
}


?>
