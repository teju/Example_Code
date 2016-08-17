<?php


// SELECT
function db_org_select(
			$org_id="",
			$name="",
			$logo="") {

	LOG_MSG('INFO',"db_org_select: START { 
							org_id=[$org_id],
							name=[$name],
							logo=[$logo]	\n}");

	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $org_id !== "" ) { 
		$where_clause.=$seperator." org_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","org_id",$org_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $logo !== "" ) { 
			$where_clause.=$seperator." logo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","logo","%".$logo."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_org_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						org_id,
						name,
						logo
					FROM 
						tOrg 
					$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_org_select(): END");
	return $resp;
}


// INSERT
function db_org_insert(
							$name,
							$logo) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_org_insert(): START { 
							name=[$name],
							logo=[$logo]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","logo",$logo);

	$resp=execSQL("INSERT INTO 
						tOrg
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_org_insert(): END");
	return $resp;

}



// UPDATE
function db_org_update(	
							$org_id,
							$name,
							$logo) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_org_update(): START {
							org_id=[$org_id],
							name=[$name],
							logo=[$logo]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","logo",$logo);

	// For the where clause
	$where_clause=" WHERE org_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","org_id",$org_id,true);


	$resp=execSQL("UPDATE  
						tOrg
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_org_update(): END");
	return $resp;
}



// DELETE
function db_org_delete($org_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_org_delete(): START { org_id=[$org_id]");

	// For the where clause
	$where_clause=" WHERE org_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","org_id",$org_id,true);


	$resp=execSQL("DELETE FROM  
						tOrg"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_org_delete(): END");
	return $resp;
}


?>
