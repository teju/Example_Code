<?php

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
							$accountability_date,
							$travel_id){
							
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_search_insert: START { 
							reg_no=[$reg_no],
							imei=[$imei],
							vehicle_model=[$vehicle_model],
							vehicle_status=[$vehicle_status],
							description=[$description],
							driver_name=[$driver_name],
							driver_phone_no=[$driver_phone_no],
							owner_ph_no=[$owner_ph_no],
							driver_sal=[$driver_sal],
							cleaner_name=[$cleaner_name],
							cleaner_sal=[$cleaner_salary],
							supervisor_name=[$supervisor_name],
							supervisor_phone_no=[$supervisor_phone_no],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							filling_station=[$filling_station],
							fuel_rate=[$fuel_rate],
							accountability_date=[$accountability_date],
							travel_id=[$travel_id]							\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","vehicle_model",$vehicle_model);
	$param_arr=_db_prepare_param($param_arr,"i","vehicle_status",$vehicle_status);
	$param_arr=_db_prepare_param($param_arr,"s","description",$description);
	$param_arr=_db_prepare_param($param_arr,"s","driver_name",$driver_name);
	$param_arr=_db_prepare_param($param_arr,"i","driver_phone_no",$driver_phone_no);
	$param_arr=_db_prepare_param($param_arr,"i","owner_ph_no",$owner_ph_no);
	$param_arr=_db_prepare_param($param_arr,"d","driver_sal",$driver_sal);
	$param_arr=_db_prepare_param($param_arr,"s","cleaner_name",$cleaner_name);
	$param_arr=_db_prepare_param($param_arr,"d","cleaner_salary",$cleaner_salary);
	$param_arr=_db_prepare_param($param_arr,"s","supervisor_name",$supervisor_name);
	$param_arr=_db_prepare_param($param_arr,"s","supervisor_phone_no",$supervisor_phone_no);
	$param_arr=_db_prepare_param($param_arr,"s","client_name",$client_name);
	$param_arr=_db_prepare_param($param_arr,"i","client_mobile",$client_mobile);
	$param_arr=_db_prepare_param($param_arr,"d","daily_fuel_lmt",$daily_fuel_lmt);
	$param_arr=_db_prepare_param($param_arr,"d","monthly_fuel_lmt",$monthly_fuel_lmt);
	$param_arr=_db_prepare_param($param_arr,"s","filling_station",$filling_station);
	$param_arr=_db_prepare_param($param_arr,"d","fuel_rate",$fuel_rate);
	$param_arr=_db_prepare_param($param_arr,"s","accountability_date",$accountability_date);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",date("Y-m-d H:i:s"));

	$resp=execSQL("INSERT INTO 
						tSearch
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_vehicle_insert(): END");
	return $resp;

}


// UPDATE
function db_search_update(	
							$search_id,
							$fuel_filled="",
							$odometer_reading="",
							$fuel_image="",
							$odometer_image="") {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_search_update(): START {
							search_id=[$search_id],
							fuel_filled=[$fuel_filled],
							odometer_reading=[$odometer_reading],
							fuel_image=[$fuel_image],
							odometer_image=[$odometer_image]					\n}");

	// Add params to params_arr
	if ( $fuel_filled != '' ) $param_arr=_db_prepare_param($param_arr,"d","fuel_filled",$fuel_filled);
	if ( $odometer_reading != '' ) $param_arr=_db_prepare_param($param_arr,"s","odometer_reading",$odometer_reading);
	if ( $fuel_image != '' ) $param_arr=_db_prepare_param($param_arr,"s","fuel_image",$fuel_image);
	if ( $odometer_image != '' ) $param_arr=_db_prepare_param($param_arr,"s","odometer_image",$odometer_image);

	// For the where clause
	$where_clause=" WHERE search_id=? AND travel_id=".TRAVEL_ID;
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




//*****************************************
// check if tag_id exist in the ttagsticker //      
//*****************************************
function db_tagsticker_select($nfc_tag_id,$travel_id) {

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","nfc_tag_id",$nfc_tag_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","travel_id",$travel_id,true);

	LOG_MSG('INFO',"db_tagsticker_select(: START { 
							nfc_tag_id=[$nfc_tag_id] ,
							travel_id=[$travel_id] \n}");

	$resp=execSQL("SELECT 
						sticker_no
					FROM 
						tTagSticker
					WHERE
					nfc_tag_id = ? AND
					travel_id = ?"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_tagsticker_select(): END");
	return $resp;
}

//**********************************************************
// check if Supervisor exist and is_active in the tSpervisor //      
 //*********************************************************
function db_supervisor_select($imei,$travel_id) {

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);

	
	LOG_MSG('INFO',"db_tagsticker_select(: START { 
							imei=[$imei],
							travel_id=[$travel_id]							\n}");

	$resp=execSQL("SELECT 
						is_active
					FROM 
						tSupervisor
					WHERE
						imei = ? AND 
						travel_id = ?"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_supervisor_select(): END");
	return $resp;
}

//**********************************************************
// check if vehicle exist and is_active in the tVehicle     //      
 //*********************************************************
function db_vehicle_select($sticker_no,$travel_id) {

	LOG_MSG('INFO',"db_vehicle_select(: START { 
							sticker_no=[$sticker_no],
							travel_id=[$travel_id]	\n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","sticker_no",$sticker_no,true);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);

	$resp=execSQL("	SELECT 
						v.vehicle_id,
						v.reg_no,
						v.vehicle_model,
						v.vehicle_model AS Model,
						v.type,
						v.sticker_no AS 'sticker no',
						v.rc_no AS 'rc no',
						v.rc_exp_dt AS 'rc exp date',
						v.daily_fuel_lmt AS 'day fuel limit in ltrs',
						v.monthly_fuel_lmt AS 'month fuel limit in ltrs',
						v.insurance_ref_no AS 'insurance ref no',
						v.insurance_exp_dt AS 'insurance exp date',
						v.road_tax_ref_no AS 'road tax ref no',
						v.road_tax_exp_dt AS 'road tax exp date',
						DATE_FORMAT(v.start_dt, '%m-%d-%Y %l:%i%p') AS 'start date',
						DATE_FORMAT(v.end_dt, '%m-%d-%Y %l:%i%p') AS 'end date',
						v.driver_id,
						v.supervisor_id,
						v.is_active,
						DATE_FORMAT(v.created_dt, '%m-%d-%Y %l:%i%p') AS 'Created date',
						d.phone_no AS 'contact no',
						d.owner_ph_no AS 'owner_ph_no',
						d.name AS 'driver name',
						d.salary AS 'driver_sal',
						d.is_active AS driver_active,
						s.is_active AS supervisor_active,
						s.phone_no as 'supervisor_phone_no',
						s.name as 'supervisor name',
						c.name as 'client_name',
						c.mobile AS 'client_mobile',
						cl.name AS 'cleaner_name',
						cl.salary AS 'cleaner_salary'
					FROM
						tVehicle v
						LEFT OUTER JOIN tDriver d ON(d.driver_id=v.driver_id)
						LEFT OUTER JOIN tSupervisor s ON(s.supervisor_id=v.supervisor_id)
						LEFT OUTER JOIN tClient c ON(c.client_id=v.client_id)
						LEFT OUTER JOIN tCleaner cl ON(cl.cleaner_id=v.cleaner_id)
					WHERE
						v.sticker_no = ? AND v.travel_id=?"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_vehicle_select(): END");
	return $resp;
}



function db_get_capture_image_count($reg_no,$image_capture_range) {

	LOG_MSG('INFO',"db_get_capture_image_count(: START { 
							reg_no=[$reg_no],
							image_capture_range=[$image_capture_range] \n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no,true);

	$resp=execSQL("	SELECT 
						reg_no,
						fuel_image,
						odometer_image
					FROM
						tSearch
					WHERE
						reg_no = ? 
					ORDER BY 
						search_id DESC
					LIMIT 
						$image_capture_range"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_capture_image_count(): END");
	return $resp;
}


function db_get_odometer_reading($reg_no) {

	LOG_MSG('INFO',"db_get_odometer_reading(: START { 
							reg_no=[$reg_no] \n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no,true);

	$odometer_search_resp=execSQL("	SELECT 
						
						odometer_reading
					FROM
						tSearch
					WHERE
						reg_no = ? 
					ORDER BY 
						search_id DESC
					LIMIT 
						1"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_capture_image_count(): END");
	return $odometer_search_resp;
}


function db_setting_select($name,$travel_id) {

	LOG_MSG('INFO',"db_setting_select(: START { 
							name=[$name],
							travel_id=[$travel_id]\n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);

	$resp=execSQL("	SELECT 
						name,
						value
					FROM
						tSetting
					WHERE
						name = ? AND 
						travel_id = ? "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_setting_select(): END");
	return $resp;
}

function db_filling_station_select($imei,$travel_id) {

	LOG_MSG('INFO',"db_filling_station_select(: START { 
							imei=[$imei],
							travel_id=[$travel_id]\n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",$travel_id,true);

	$resp=execSQL("	SELECT 
						name,
						imei,
						location,
						fuel_rate
					FROM
						tFillingStation
					WHERE
						imei = ? AND 
						travel_id = ? "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_filling_station_select(): END");
	return $resp;
}

function db_search_select($imei="",
						 $created_dt="") {

	LOG_MSG('INFO',"db_search_select(: START { 
							imei=[$imei]\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	if ( $imei !== "" ) { 
		$where_clause.=$seperator." imei = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $created_dt !== "" ) { 
		$where_clause.=$seperator." created_dt like ? ";
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt."%",true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_search_select(): WHERE CLAUSE = [$where_clause]");


	$resp=execSQL("	SELECT 
						SUM(fuel_filled)fuel_filled,
						fuel_rate,
						count(search_id) total_fills
					FROM
						tSearch
					$where_clause"
						,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_search_select(): END");
	return $resp;
}
?>
