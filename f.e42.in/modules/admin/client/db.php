<?php


// SELECT
function db_client_select(
			$client_id="",
			$name="",
			$photo="",
			$mobile="",
			$address="") {


	LOG_MSG('INFO',"db_client_select: START { 
							client_id=[$client_id],
							name=[$name],
							photo=[$photo],
							mobile=[$mobile],
							address=[$address]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $client_id !== "" ) { 
		$where_clause.=$seperator." client_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","client_id",$client_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $photo !== "" ) { 
			$where_clause.=$seperator." photo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","photo","%".$photo."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $mobile !== "" ) { 
			$where_clause.=$seperator." mobile like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","mobile","%".$mobile."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $address !== "" ) { 
			$where_clause.=$seperator." address like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","address","%".$address."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		
	}

	// No where clause
	if ( $where_clause ===  "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_client_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						client_id,
						name,
						photo,
						mobile,
						address			FROM 
					tClient
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_client_select(): END");
	return $resp;
}


// INSERT
function db_client_insert(
							$name,
							$photo,
							$mobile,
							$address) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_client_insert(): START { 
							name=[$name],
							photo=[$photo],
							mobile=[$mobile],
							address=[$address]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	if($photo !==""){
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	}
	$param_arr=_db_prepare_param($param_arr,"i","mobile",$mobile);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	

	$resp=execSQL("INSERT INTO 
						tClient
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_client_insert(): END");
	return $resp;

}



// UPDATE
function db_client_update(	
							$client_id,
							$name,
							$photo,
							$mobile,
							$address) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_client_update(): START {
							client_id=[$client_id],
							name=[$name],
							photo=[$photo],
							mobile=[$mobile],
							address=[$address]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"i","mobile",$mobile);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	

	// For the where clause
	$where_clause=" WHERE client_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","client_id",$client_id,true);


	$resp=execSQL("UPDATE  
						tClient
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_client_update(): END");
	return $resp;
}



// DELETE
function db_client_delete($client_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_client_delete(): START { client_id=[$client_id]");

	// For the where clause
	$where_clause=" WHERE client_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","client_id",$client_id,true);


	$resp=execSQL("DELETE FROM  
						tClient"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_client_delete(): END");
	return $resp;
}


?>
