<?php


// SELECT
function db_student_group_select(
			$group_id="",
			$group_name="",
			$is_active="") {


	LOG_MSG('INFO',"db_student_group_select: START { 
							group_id=[$group_id],
							group_name=[$group_name],
							is_active=[$is_active] \n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $group_id !== "" ) { 
		$where_clause.=$seperator." group_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);
	} else {
		if ( $group_name !== "" ) { 
			$where_clause.=$seperator." group_name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","group_name","%".$group_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $is_active !== "" ) { 
			$where_clause.=$seperator." is_active = ? ";
			$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active,true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_student_group_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						group_id,
						group_name,
						is_active
					FROM 
						tGroup
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_student_group_select(): END");
	return $resp;
}


// INSERT
function db_student_group_insert(
							$group_name,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_student_group_insert(): START { 
							group_name=[$group_name],
							is_active=[$is_active]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","group_name",$group_name);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	

	$resp=execSQL("INSERT INTO 
						tGroup
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_group_insert(): END");
	return $resp;

}

// UPDATE
function db_student_group_update(	
							$group_id,
							$group_name,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_student_group_update(): START {
							group_name=[$group_name],
							is_active=[$is_active] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","group_name",$group_name);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);

	// For the where clause
	$where_clause=" WHERE group_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);


	$resp=execSQL("UPDATE  
						tGroup
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_group_update(): END");
	return $resp;
}


// DELETE
function db_student_group_delete($group_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_student_group_delete(): START { group_id=[$group_id]");

	// For the where clause
	$where_clause=" WHERE group_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);


	$resp=execSQL("DELETE FROM  
						tGroup"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_group_delete(): END");
	return $resp;
}
// SELECT
function db_location_group_select($group_id ="",$location_id="") {

	LOG_MSG('INFO',"db_location_group_select: START { }");

	$param_arr=_init_db_params();
	$where_clause="WHERE g.travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $group_id !== "" ) { 
		$where_clause.=$seperator." g.group_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);
	} 

	if ( $location_id !== "" ) { 
		$where_clause.=$seperator." g.location_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
	} 

	// No where clause
	if ( $where_clause === "WHERE g.travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_location_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						g.group_id,
						g.location_id,
						l.location_name location_name
					FROM 
						tStLocGroup g
						LEFT OUTER JOIN tLocation l ON(l.location_id=g.location_id AND l.travel_id = g.travel_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_location_group_select(): END");
	return $resp;
}
function db_group_location_insert($group_id,$location_id) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_group_location_insert(): START { 
							group_id=[$group_id],
							location_id=[$location_id]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id);
	$param_arr=_db_prepare_param($param_arr,"s","location_id",$location_id);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tStLocGroup
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_group_location_insert(): END");
	return $resp;

}
function db_group_location_delete($location_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_group_location_delete(): START { location_id=[$location_id]");

	// For the where clause
	$where_clause=" WHERE location_id=? AND travel_id = ".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);

	$resp=execSQL("DELETE FROM  
						tStLocGroup"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_group_location_delete(): END");
	return $resp;
}
?>
