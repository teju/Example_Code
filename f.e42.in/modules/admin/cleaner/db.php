<?php


// SELECT
function db_cleaner_select(
			$cleaner_id="",
			$name="",
			$photo="",
			$mobile="",
			$address="",
			$salary="") {


	LOG_MSG('INFO',"db_cleaner_select: START { 
							cleaner_id=[$cleaner_id],
							name=[$name],
							photo=[$photo],
							mobile=[$mobile],
							address=[$address],
							salary=[$salary]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $cleaner_id !== "" ) { 
		$where_clause.=$seperator." cleaner_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","cleaner_id",$cleaner_id,true);
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
		
		if ( $salary !== "" ) { 
			$where_clause.=$seperator." salary like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","salary","%".$salary."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_cleaner_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						cleaner_id,
						name,
						photo,
						mobile,
						address,
						salary
										FROM 
					tCleaner
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_cleaner_select(): END");
	return $resp;
}


// INSERT
function db_cleaner_insert(
							$name,
							$photo,
							$mobile,
							$address,
							$salary) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_cleaner_insert(): START { 
							name=[$name],
							photo=[$photo],
							mobile=[$mobile],
							address=[$address],
							
							salary=[$salary]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	if($photo !==""){
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	}
	$param_arr=_db_prepare_param($param_arr,"i","mobile",$mobile);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","salary",$salary);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	

	$resp=execSQL("INSERT INTO 
						tCleaner
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_cleaner_insert(): END");
	return $resp;

}



// UPDATE
function db_cleaner_update(	
							$cleaner_id,
							$name,
							$photo,
							$mobile,
							$address,
							$salary) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_cleaner_update(): START {
							cleaner_id=[$cleaner_id],
							name=[$name],
							photo=[$photo],
							mobile=[$mobile],
							address=[$address],
							salary=[$salary]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"i","mobile",$mobile);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","salary",$salary);
	
	// For the where clause
	$where_clause=" WHERE cleaner_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","cleaner_id",$cleaner_id,true);


	$resp=execSQL("UPDATE  
						tCleaner
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_cleaner_update(): END");
	return $resp;
}


// DELETE
function db_cleaner_delete($cleaner_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_cleaner_delete(): START { cleaner_id=[$cleaner_id]");

	// For the where clause
	$where_clause=" WHERE cleaner_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","cleaner_id",$cleaner_id,true);


	$resp=execSQL("DELETE FROM  
						tCleaner"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_cleaner_delete(): END");
	return $resp;
}


?>
