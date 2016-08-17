<?php


// SELECT
function db_driver_select(
			$driver_id="",
			$name="",
			$photo="",
			$phone_no="",
			$owner_ph_no="",
			$address="",
			$license_no="",
			$license_exp_dt="",
			$salary="",
			$start_dt="",
			$end_dt="",
			$is_active="",
			$created_dt="") {


	LOG_MSG('INFO',"db_driver_select: START { 
							driver_id=[$driver_id],
							name=[$name],
							photo=[$photo],
							phone_no=[$phone_no],
							owner_ph_no=[$owner_ph_no],
							address=[$address],
							license_no=[$license_no],
							license_exp_dt=[$license_exp_dt],
							salary=[$salary],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							is_active=[$is_active],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $driver_id !== "" ) { 
		$where_clause.=$seperator." driver_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","driver_id",$driver_id,true);
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
		if ( $phone_no !== "" ) { 
			$where_clause.=$seperator." phone_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","phone_no","%".$phone_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $owner_ph_no !== "" ) { 
			$where_clause.=$seperator." owner_ph_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","owner_ph_no","%".$owner_ph_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $address !== "" ) { 
			$where_clause.=$seperator." address like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","address","%".$address."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $license_no !== "" ) { 
			$where_clause.=$seperator." license_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","license_no","%".$license_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $license_exp_dt !== "" ) { 
			$where_clause.=$seperator." license_exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","license_exp_dt","%".$license_exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $salary !== "" ) { 
			$where_clause.=$seperator." salary like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","salary","%".$salary."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $start_dt !== "" ) { 
			$where_clause.=$seperator." start_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","start_dt","%".$start_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $end_dt !== "" ) { 
			$where_clause.=$seperator." end_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","end_dt","%".$end_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $is_active !== "" ) { 
			$where_clause.=$seperator." is_active like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","is_active","%".$is_active."%",true);
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
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_driver_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						driver_id,
						name,
						photo,
						phone_no,
						owner_ph_no,
						address,
						license_no,
						license_exp_dt,
						salary,
						start_dt,
						end_dt,
						is_active,
						created_dt				FROM 
					tDriver
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_driver_select(): END");
	return $resp;
}


// INSERT
function db_driver_insert(
							$name,
							$photo,
							$phone_no,
							$owner_ph_no,
							$address,
							$license_no,
							$license_exp_dt,
							$salary,
							$start_dt,
							$end_dt,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_driver_insert(): START { 
							name=[$name],
							photo=[$photo],
							phone_no=[$phone_no],
							owner_ph_no=[$owner_ph_no],
							address=[$address],
							license_no=[$license_no],
							license_exp_dt=[$license_exp_dt],
							salary=[$salary],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	if ( $photo !== "" ) {
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	}
	$param_arr=_db_prepare_param($param_arr,"i","phone_no",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"i","owner_ph_no",$owner_ph_no);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","license_no",$license_no);
	$param_arr=_db_prepare_param($param_arr,"s","license_exp_dt",$license_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"d","salary",$salary);
	$param_arr=_db_prepare_param($param_arr,"s","start_dt",$start_dt);
	$param_arr=_db_prepare_param($param_arr,"s","end_dt",$end_dt);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",date("Y-m-d H:i:s"));

	$resp=execSQL("INSERT INTO 
						tDriver
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_driver_insert(): END");
	return $resp;

}



// UPDATE
function db_driver_update(	
							$driver_id,
							$name,
							$photo,
							$phone_no,
							$owner_ph_no,
							$address,
							$license_no,
							$license_exp_dt,
							$salary,
							$start_dt,
							$end_dt,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_driver_update(): START {
							driver_id=[$driver_id],
							name=[$name],
							photo=[$photo],
							phone_no=[$phone_no],
							owner_ph_no=[$owner_ph_no],
							address=[$address],
							license_no=[$license_no],
							license_exp_dt=[$license_exp_dt],
							salary=[$salary],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"i","phone_no",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"i","owner_ph_no",$owner_ph_no);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","license_no",$license_no);
	$param_arr=_db_prepare_param($param_arr,"s","license_exp_dt",$license_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","salary",$salary);
	$param_arr=_db_prepare_param($param_arr,"s","start_dt",$start_dt);
	$param_arr=_db_prepare_param($param_arr,"s","end_dt",$end_dt);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);

	// For the where clause
	$where_clause=" WHERE driver_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","driver_id",$driver_id,true);


	$resp=execSQL("UPDATE  
						tDriver
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_driver_update(): END");
	return $resp;
}



// DELETE
function db_driver_delete($driver_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_driver_delete(): START { driver_id=[$driver_id]");

	// For the where clause
	$where_clause=" WHERE driver_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","driver_id",$driver_id,true);


	$resp=execSQL("DELETE FROM  
						tDriver"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_driver_delete(): END");
	return $resp;
}


?>
