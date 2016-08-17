<?php


// SELECT
function db_supervisor_select(
			$supervisor_id="",
			$name="",
			$photo="",
			$phone_no="",
			$address="",
			$imei="",
			$start_dt="",
			$end_dt="",
			$is_active="",
			$created_dt="") {


	LOG_MSG('INFO',"db_supervisor_select: START { 
							supervisor_id=[$supervisor_id],
							name=[$name],
							photo=[$photo],
							phone_no=[$phone_no],
							address=[$address],
							imei=[$imei],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							is_active=[$is_active],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	if ( is_supervisor() ) {
		$where_clause="WHERE supervisor_id=".$_SESSION['supervisor_id'] ." AND s.travel_id=".TRAVEL_ID;
		$seperator="AND ";
	} else {
		$where_clause="WHERE s.travel_id=".TRAVEL_ID;
		$seperator=" AND";
	}
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $supervisor_id !== "" ) { 
		$where_clause.=$seperator." supervisor_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","supervisor_id",$supervisor_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." s.name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $phone_no !== "" ) { 
			$where_clause.=$seperator." s.phone_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","phone_no","%".$phone_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $address !== "" ) { 
			$where_clause.=$seperator." s.address like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","address","%".$address."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $imei !== "" ) { 
			$where_clause.=$seperator." s.imei like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $start_dt !== "" ) { 
			$where_clause.=$seperator." s.start_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","start_dt","%".$start_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $end_dt !== "" ) { 
			$where_clause.=$seperator." s.end_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","end_dt","%".$end_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $is_active !== "" ) { 
			$where_clause.=$seperator." s.is_active like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","is_active","%".$is_active."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." s.created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID|| (is_supervisor() && $where_clause === "WHERE supervisor_id=".$_SESSION['supervisor_id'] ." AND s.travel_id=".TRAVEL_ID) ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_supervisor_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						s.supervisor_id,
						s.name,
						s.photo,
						s.phone_no,
						s.address,
						s.imei,
						s.start_dt,
						s.end_dt,
						s.is_active,
						s.created_dt,
						u.email_id,
						u.password
					FROM 
						tSupervisor s
						LEFT OUTER JOIN tUser u ON(u.user_id=s.user_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_supervisor_select(): END");
	return $resp;
}


// INSERT
function db_supervisor_insert(
							$user_id,
							$name,
							$photo,
							$phone_no,
							$address,
							$imei,
							$start_dt,
							$end_dt,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_supervisor_insert(): START { 
							user_id=[$user_id],
							name=[$name],
							photo=[$photo],
							phone_no=[$phone_no],
							address=[$address],
							imei=[$imei],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id);
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	if($photo !==""){
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	}
	$param_arr=_db_prepare_param($param_arr,"i","phone_no",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","start_dt",$start_dt);
	$param_arr=_db_prepare_param($param_arr,"s","end_dt",$end_dt);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",date("Y-m-d H:i:s"));

	$resp=execSQL("INSERT INTO 
						tSupervisor
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_supervisor_insert(): END");
	return $resp;

}

// UPDATE
function db_supervisor_update(	
							$supervisor_id,
							$user_id,
							$name,
							$photo,
							$phone_no,
							$address,
							$imei,
							$start_dt,
							$end_dt,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_supervisor_update(): START {
							supervisor_id=[$supervisor_id],
							user_id=[$user_id],
							name=[$name],
							photo=[$photo],
							phone_no=[$phone_no],
							address=[$address],
							imei=[$imei],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"i","phone_no",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","start_dt",$start_dt);
	$param_arr=_db_prepare_param($param_arr,"s","end_dt",$end_dt);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);

	// For the where clause
	$where_clause=" WHERE supervisor_id=? AND travel_id= ".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","supervisor_id",$supervisor_id,true);


	$resp=execSQL("UPDATE  
						tSupervisor
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_supervisor_update(): END");
	return $resp;
}


// DELETE
function db_supervisor_delete($supervisor_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_supervisor_delete(): START { supervisor_id=[$supervisor_id]");

	// For the where clause
	$where_clause=" WHERE supervisor_id=? AND travel_id= ".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","supervisor_id",$supervisor_id,true);
	//$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);


	$resp=execSQL("DELETE FROM  
						tSupervisor"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_supervisor_delete(): END");
	return $resp;
}


?>
