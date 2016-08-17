<?php


// SELECT
function db_setting_select(
			$setting_id="",
			$name="",
			$value="",
			$effective_date="") {


	LOG_MSG('INFO',"db_setting_select: START { 
							setting_id=[$setting_id],
							name=[$name],
							value=[$value],
							effective_date=[$effective_date]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $setting_id !== "" ) { 
		$where_clause.=$seperator." setting_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","setting_id",$setting_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $value !== "" ) { 
			$where_clause.=$seperator." value like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","value","%".$value."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $effective_date !== "" ) { 
			$where_clause.=$seperator." effective_date like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","effective_date","%".$effective_date."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_setting_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						setting_id,
						name,
						value,
						effective_date
					FROM 
						tSetting
					".$where_clause." AND is_hidden=0"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_setting_select(): END");
	return $resp;
}


// INSERT
function db_setting_insert(
							$name,
							$value,
							$effective_date) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_setting_insert(): START { 
							name=[$name],
							value=[$value]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","value",$value);
	$param_arr=_db_prepare_param($param_arr,"s","effective_date",$effective_date);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tSetting
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_setting_insert(): END");
	return $resp;

}



// UPDATE
function db_setting_update(	
							$setting_id,
							$value,
							$effective_date) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_setting_update(): START {
							setting_id=[$setting_id],
							value=[$value]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","value",$value);
	$param_arr=_db_prepare_param($param_arr,"s","effective_date",$effective_date);

	// For the where clause
	$where_clause=" WHERE setting_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","setting_id",$setting_id,true);



	$resp=execSQL("UPDATE  
						tSetting
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_setting_update(): END");
	return $resp;
}



// DELETE
function db_setting_delete($setting_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_setting_delete(): START { setting_id=[$setting_id]");

	// For the where clause
	$where_clause=" WHERE setting_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","setting_id",$setting_id,true);


	$resp=execSQL("DELETE FROM  
						tSetting"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_setting_delete(): END");
	return $resp;
}


?>
