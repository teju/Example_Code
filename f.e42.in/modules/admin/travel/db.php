<?php


// SELECT
function db_travel_select(
			$travel_id="",
			$name="",
			$domain="",
			$logo="",
			$mobile="",
			$address="",
			$created_dt="") {


	LOG_MSG('INFO',"db_travel_select: START { 
							travel_id=[$travel_id],
							name=[$name],
							domain=[$domain],
							logo=[$logo],
							mobile=[$mobile],
							address=[$address],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="  WHERE";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $travel_id !== "" ) { 
		$where_clause.=$seperator." travel_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $domain !== "" ) { 
			$where_clause.=$seperator." domain like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","domain","%".$domain."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $logo !== "" ) { 
			$where_clause.=$seperator." logo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","logo","%".$logo."%",true);
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
		
		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === ""  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_travel_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						travel_id,
						name,
						domain,
						logo,
						mobile,
						address,
						created_dt
										FROM 
					tTravel
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_travel_select(): END");
	return $resp;
}


// INSERT
function db_travel_insert(
							$name,
							$domain,
							$logo,
							$mobile,
							$address,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_travel_insert(): START { 
							name=[$name],
							domain=[$domain],
							logo=[$logo],
							mobile=[$mobile],
							address=[$address],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","domain",$domain);
	if($logo !==""){
	$param_arr=_db_prepare_param($param_arr,"s","logo",$logo);
	}
	$param_arr=_db_prepare_param($param_arr,"i","mobile",$mobile);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);
	
	

	$resp=execSQL("INSERT INTO 
						tTravel
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_travel_insert(): END");
	return $resp;

}



// UPDATE
function db_travel_update(	
							$travel_id,
							$name,
							$domain,
							$logo,
							$mobile,
							$address,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_travel_update(): START {
							travel_id=[$travel_id],
							name=[$name],
							domain=[$domain],
							logo=[$logo],
							mobile=[$mobile],
							address=[$address],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","domain",$domain);
	$param_arr=_db_prepare_param($param_arr,"s","logo",$logo);
	$param_arr=_db_prepare_param($param_arr,"i","mobile",$mobile);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);

	// For the where clause
	$where_clause=" WHERE travel_id=? ";
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);

	$resp=execSQL("UPDATE  
						tTravel
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_travel_update(): END");
	return $resp;
}


// DELETE
function db_travel_delete($travel_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_travel_delete(): START { travel_id=[$travel_id]");

	// For the where clause
	$where_clause=" WHERE travel_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);


	$resp=execSQL("DELETE FROM  
						tTravel"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_travel_delete(): END");
	return $resp;
}


?>
