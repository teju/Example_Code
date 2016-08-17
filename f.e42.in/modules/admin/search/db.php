<?php

// SELECT
function db_search_select(
			$search_id="",
			$reg_no="",
			$imei="",
			$vehicle_model="",
			$vehicle_status="",
			$description="",
			$driver_name="",
			$driver_phone_no="",
			$owner_ph_no="",
			$driver_sal="",
			$cleaner_name="",
			$cleaner_salary="",
			$supervisor_name="",
			$supervisor_phone_no="",
			$client_name="",
			$client_mobile="",
			$daily_fuel_lmt="",
			$monthly_fuel_lmt="",
			$filling_station="",
			$fuel_rate="",
			$fuel_filled="",
			$odometer_reading="",
			$fuel_image="",
			$odometer_image="",
			$accountability_date="",
			$created_dt="") {

	LOG_MSG('INFO',"db_search_select: START { 
							search_id=[$search_id],
							reg_no=[$reg_no],
							imei=[$imei],
							vehicle_model=[$vehicle_model],
							vehicle_status=[$vehicle_status],
							description=[$description],
							driver_name=[$driver_name],
							owner_ph_no=[$owner_ph_no],
							driver_sal=[$driver_sal],
							cleaner_name=[$cleaner_name],
							cleaner_salary=[$cleaner_salary],
							supervisor_name=[$supervisor_name],
							supervisor_phone_no=[$supervisor_phone_no],
							client_name=[$client_name],
							client_mobile=[$client_mobile],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							filling_station=[$filling_station],
							fuel_rate=[$fuel_rate],
							fuel_filled=[$fuel_filled],
							odometer_reading=[$odometer_reading],
							fuel_image=[$fuel_image],
							odometer_image=[$odometer_image],
							accountability_date=[$accountability_date],
							created_dt=[$created_dt] \n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $search_id !== "" ) { 
		$where_clause.=$seperator." search_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","search_id",$search_id,true);
	} else {
		if ( $reg_no !== "" ) { 
			$where_clause.=$seperator." reg_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $vehicle_status !== "" ) { 
			$where_clause.=$seperator." vehicle_status like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","vehicle_status","%".$vehicle_status."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $vehicle_model !== "" ) { 
			$where_clause.=$seperator." vehicle_model like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","vehicle_model","%".$vehicle_model."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $driver_name !== "" ) { 
			$where_clause.=$seperator." driver_name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","driver_name","%".$driver_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $client_name !== "" ) { 
			$where_clause.=$seperator." client_name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","client_name","%".$client_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $daily_fuel_lmt !== "" ) { 
			$where_clause.=$seperator." daily_fuel_lmt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","daily_fuel_lmt","%".$daily_fuel_lmt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $monthly_fuel_lmt !== "" ) { 
			$where_clause.=$seperator." monthly_fuel_lmt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","monthly_fuel_lmt","%".$monthly_fuel_lmt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $fuel_filled !== "" ) { 
			$where_clause.=$seperator." fuel_filled like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","fuel_filled","%".$fuel_filled."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $odometer_reading !== "" ) { 
			$where_clause.=$seperator." odometer_reading like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","odometer_reading","%".$odometer_reading."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $accountability_date !== "" ) { 
			$where_clause.=$seperator." accountability_date like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","accountability_date","%".$accountability_date."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_search_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						search_id,
						reg_no,
						imei,
						vehicle_model,
						vehicle_status,
						description,
						driver_name,
						driver_phone_no,
						owner_ph_no,
						driver_sal,
						cleaner_name,
						cleaner_salary,
						supervisor_name,
						supervisor_phone_no,
						client_name,
						client_mobile,
						daily_fuel_lmt,
						monthly_fuel_lmt,
						filling_station,
						fuel_rate,
						fuel_filled,
						odometer_reading,
						fuel_image,
						odometer_image,
						accountability_date,
						created_dt
					FROM 
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
							$reg_no,
							$imei,
							$vehicle_model,
							$vehicle_status,
							$description,
							$driver_name,
							$driver_phone_no,
							$owner_ph_no,
							$driver_sal,
							$cleaner_name,
							$cleaner_salary,
							$supervisor_name,
							$supervisor_phone_no,
							$client_name,
							$client_mobile,
							$daily_fuel_lmt,
							$monthly_fuel_lmt,
							$filling_station,
							$fuel_rate,
							$fuel_filled,
							$odometer_reading,
							$fuel_image,
							$odometer_image,
							$accountability_date,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_search_insert(): START { 
							reg_no=[$reg_no],
							imei=[$imei],
							vehicle_model=[$vehicle_model],
							vehicle_status=[$vehicle_status],
							description=[$description],
							driver_name=[$driver_name],
							owner_ph_no=[$owner_ph_no],
							driver_sal=[$driver_sal],
							cleaner_name=[$cleaner_name],
							cleaner_salary=[$cleaner_salary],
							supervisor_name=[$supervisor_name],
							supervisor_phone_no=[$supervisor_phone_no],
							client_name=[$client_name],
							client_mobile=[$client_mobile],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							filling_station=[$filling_station],
							fuel_rate=[$fuel_rate],
							fuel_filled=[$fuel_filled],
							odometer_reading=[$odometer_reading],
							fuel_image=[$fuel_image],
							odometer_image=[$odometer_image],
							accountability_date=[$accountability_date],
							created_dt=[$created_dt]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
		$param_arr=_db_prepare_param($param_arr,"s","vehicle_model",$vehicle_model);
		$param_arr=_db_prepare_param($param_arr,"i","vehicle_status",$vehicle_status);
		$param_arr=_db_prepare_param($param_arr,"s","description",$description);
		$param_arr=_db_prepare_param($param_arr,"s","driver_name",$driver_name);
		$param_arr=_db_prepare_param($param_arr,"s","driver_phone_no",$driver_phone_no);
		$param_arr=_db_prepare_param($param_arr,"s","owner_ph_no",$owner_ph_no);
		$param_arr=_db_prepare_param($param_arr,"s","driver_sal",$driver_sal);
		$param_arr=_db_prepare_param($param_arr,"s","cleaner_name",$cleaner_name);
		$param_arr=_db_prepare_param($param_arr,"s","cleaner_salary",$cleaner_salary);
		$param_arr=_db_prepare_param($param_arr,"s","supervisor_name",$supervisor_name);
		$param_arr=_db_prepare_param($param_arr,"s","supervisor_phone_no",$supervisor_phone_no);
		$param_arr=_db_prepare_param($param_arr,"s","client_name",$client_name);
		$param_arr=_db_prepare_param($param_arr,"s","client_mobile",$client_mobile);
		$param_arr=_db_prepare_param($param_arr,"d","daily_fuel_lmt",$daily_fuel_lmt);
		$param_arr=_db_prepare_param($param_arr,"d","monthly_fuel_lmt",$monthly_fuel_lmt);
		$param_arr=_db_prepare_param($param_arr,"s","filling_station",$filling_station);
		$param_arr=_db_prepare_param($param_arr,"d","fuel_rate",$fuel_rate);
		$param_arr=_db_prepare_param($param_arr,"d","fuel_filled",$fuel_filled);
		$param_arr=_db_prepare_param($param_arr,"s","odometer_reading",$odometer_reading);
		$param_arr=_db_prepare_param($param_arr,"s","fuel_image",$fuel_image);
		$param_arr=_db_prepare_param($param_arr,"s","odometer_image",$odometer_image);
		$param_arr=_db_prepare_param($param_arr,"s","accountability_date",$accountability_date);
		$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

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
function db_search_update($search_id,
							$reg_no,
							$imei,
							$vehicle_model,
							$vehicle_status,
							$description,
							$driver_name,
							$driver_phone_no,
							$owner_ph_no,
							$driver_sal,
							$cleaner_name,
							$cleaner_salary,
							$supervisor_name,
							$supervisor_phone_no,
							$client_name,
							$client_mobile,
							$daily_fuel_lmt,
							$monthly_fuel_lmt,
							$filling_station,
							$fuel_rate,
							$fuel_filled,
							$odometer_reading,
							$fuel_image,
							$odometer_image,
							$accountability_date,
							$created_dt) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_search_update(): START {
							search_id=[$search_id],
							reg_no=[$reg_no],
							imei=[$imei],
							vehicle_model=[$vehicle_model],
							vehicle_status=[$vehicle_status],
							description=[$description],
							driver_name=[$driver_name],
							owner_ph_no=[$owner_ph_no],
							driver_sal=[$driver_sal],
							cleaner_name=[$cleaner_name],
							cleaner_salary=[$cleaner_salary],
							supervisor_name=[$supervisor_name],
							supervisor_phone_no=[$supervisor_phone_no],
							client_name=[$client_name],
							client_mobile=[$client_mobile],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							filling_station=[$filling_station],
							fuel_rate=[$fuel_rate],
							fuel_filled=[$fuel_filled],
							odometer_reading=[$odometer_reading],
							fuel_image=[$fuel_image],
							odometer_image=[$odometer_image],
							accountability_date=[$accountability_date],
							created_dt=[$created_dt]\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
		$param_arr=_db_prepare_param($param_arr,"s","vehicle_model",$vehicle_model);
		$param_arr=_db_prepare_param($param_arr,"i","vehicle_status",$vehicle_status);
		$param_arr=_db_prepare_param($param_arr,"s","description",$description);
		$param_arr=_db_prepare_param($param_arr,"s","driver_name",$driver_name);
		$param_arr=_db_prepare_param($param_arr,"s","driver_phone_no",$driver_phone_no);
		$param_arr=_db_prepare_param($param_arr,"s","owner_ph_no",$owner_ph_no);
		$param_arr=_db_prepare_param($param_arr,"s","driver_sal",$driver_sal);
		$param_arr=_db_prepare_param($param_arr,"s","cleaner_name",$cleaner_name);
		$param_arr=_db_prepare_param($param_arr,"s","cleaner_salary",$cleaner_salary);
		$param_arr=_db_prepare_param($param_arr,"s","supervisor_name",$supervisor_name);
		$param_arr=_db_prepare_param($param_arr,"s","supervisor_phone_no",$supervisor_phone_no);
		$param_arr=_db_prepare_param($param_arr,"s","client_name",$client_name);
		$param_arr=_db_prepare_param($param_arr,"s","client_mobile",$client_mobile);
		$param_arr=_db_prepare_param($param_arr,"d","daily_fuel_lmt",$daily_fuel_lmt);
		$param_arr=_db_prepare_param($param_arr,"d","monthly_fuel_lmt",$monthly_fuel_lmt);
		$param_arr=_db_prepare_param($param_arr,"s","filling_station",$filling_station);
		$param_arr=_db_prepare_param($param_arr,"d","fuel_rate",$fuel_rate);
		$param_arr=_db_prepare_param($param_arr,"d","fuel_filled",$fuel_filled);
		$param_arr=_db_prepare_param($param_arr,"s","odometer_reading",$odometer_reading);
		$param_arr=_db_prepare_param($param_arr,"s","fuel_image",$fuel_image);
		$param_arr=_db_prepare_param($param_arr,"s","odometer_image",$odometer_image);
		$param_arr=_db_prepare_param($param_arr,"s","accountability_date",$accountability_date);
		$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

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
	$where_clause=" WHERE search_id=? AND travel_id=".TRAVEL_ID;
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
