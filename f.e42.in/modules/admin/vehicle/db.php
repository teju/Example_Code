<?php


// SELECT
function db_vehicle_select(
			$vehicle_id="",
			$reg_no="",
			$route="",
			$vehicle_model="",
			$type="",
			$sticker_no="",
			$rc_no="",
			$rc_exp_dt="",
			$daily_fuel_lmt="",
			$monthly_fuel_lmt="",
			$insurance_ref_no="",
			$insurance_exp_dt="",
			$road_tax_ref_no="",
			$road_tax_exp_dt="",
			$permit="",
			$permit_exp_dt="",
			$authorization="",
			$authorization_exp_dt="",
			$start_dt="",
			$end_dt="",
			$driver_id="",
			$cleaner_id="",
			$supervisor_id="",
			$client_id="",
			$is_active="",
			$created_dt="",
			$client_name="") {


	LOG_MSG('INFO',"db_vehicle_select: START { 
							vehicle_id=[$vehicle_id],
							reg_no=[$reg_no],
							route=[$route],
							vehicle_model=[$vehicle_model],
							type=[$type],
							sticker_no=[$sticker_no],
							rc_no=[$rc_no],
							rc_exp_dt=[$rc_exp_dt],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							insurance_ref_no=[$insurance_ref_no],
							insurance_exp_dt=[$insurance_exp_dt],
							road_tax_ref_no=[$road_tax_ref_no],
							road_tax_exp_dt=[$road_tax_exp_dt],
							permit=[$permit],
							permit_exp_dt=[$permit_exp_dt],
							authorization=[$authorization],
							authorization_exp_dt=[$authorization_exp_dt],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							driver_id=[$driver_id],
							supervisor_id=[$supervisor_id],
							client_id=[$client_id],
							cleaner_id=[$cleaner_id],
							is_active=[$is_active],
							created_dt=[$created_dt]	
							client_name=[$client_name]	
							\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE v.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $vehicle_id !== "" ) { 
		$where_clause.=$seperator." v.vehicle_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","vehicle_id",$vehicle_id,true);
	} else {
		if ( $reg_no !== "" ) { 
			$where_clause.=$seperator." v.reg_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $route !== "" ) { 
			$where_clause.=$seperator." v.route like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","route","%".$route."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $vehicle_model !== "" ) { 
			$where_clause.=$seperator." v.vehicle_model like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","vehicle_model","%".$vehicle_model."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $type !== "" ) { 
			$where_clause.=$seperator." v.type like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","type","%".$type."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $sticker_no !== "" ) { 
			$where_clause.=$seperator." v.sticker_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","sticker_no","%".$sticker_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $rc_no !== "" ) { 
			$where_clause.=$seperator." v.rc_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","rc_no","%".$rc_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $rc_exp_dt !== "" ) { 
			$where_clause.=$seperator." v.rc_exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","rc_exp_dt","%".$rc_exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		
		
		if ( $daily_fuel_lmt !== "" ) { 
			$where_clause.=$seperator." v.daily_fuel_lmt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","daily_fuel_lmt","%".$daily_fuel_lmt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $monthly_fuel_lmt !== "" ) { 
			$where_clause.=$seperator." v.monthly_fuel_lmt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","monthly_fuel_lmt","%".$monthly_fuel_lmt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $insurance_ref_no !== "" ) { 
			$where_clause.=$seperator." v.insurance_ref_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","insurance_ref_no","%".$insurance_ref_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $insurance_exp_dt !== "" ) { 
			$where_clause.=$seperator." v.insurance_exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","insurance_exp_dt","%".$insurance_exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $road_tax_ref_no !== "" ) { 
			$where_clause.=$seperator." v.road_tax_ref_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","road_tax_ref_no","%".$road_tax_ref_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $road_tax_exp_dt !== "" ) { 
			$where_clause.=$seperator." v.road_tax_exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","road_tax_exp_dt","%".$road_tax_exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $permit !== "" ) { 
			$where_clause.=$seperator." v.permit like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","permit","%".$permit."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $permit_exp_dt !== "" ) { 
			$where_clause.=$seperator." v.permit_exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","permit_exp_dt","%".$permit_exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $authorization !== "" ) { 
			$where_clause.=$seperator." v.authorization like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","authorization","%".$authorization."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $authorization_exp_dt !== "" ) { 
			$where_clause.=$seperator." v.authorization_exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","authorization_exp_dt","%".$authorization_exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		
		if ( $start_dt !== "" ) { 
			$where_clause.=$seperator." v.start_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","start_dt","%".$start_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $end_dt !== "" ) { 
			$where_clause.=$seperator." v.end_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","end_dt","%".$end_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $driver_id !== "" ) { 
			$where_clause.=$seperator." v.driver_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","driver_id","%".$driver_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $cleaner_id !== "" ) { 
			$where_clause.=$seperator." v.cleaner_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"i","cleaner_id","%".$cleaner_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $supervisor_id !== "" ) { 
			$where_clause.=$seperator." v.supervisor_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","supervisor_id","%".$supervisor_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $client_id !== "" ) { 
			$where_clause.=$seperator." v.client_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"i","client_id","%".$client_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		
		if ( $is_active !== "" ) { 
			$where_clause.=$seperator." v.is_active like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","is_active","%".$is_active."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." v.created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $client_name !== "" ) { 
			$where_clause.=$seperator." c.name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$client_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
                if ( is_supervisor() ) {
                       $where_clause.=$seperator." v.supervisor_id = ? ";
                       $param_arr=_db_prepare_param($param_arr,"s","supervisor_id",$_SESSION['supervisor_id'],true);
                       $seperator="AND ";
                       $is_search=true;
                }
	}

	// No where clause
	if ( $where_clause === "WHERE v.travel_id=".TRAVEL_ID) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_vehicle_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						v.vehicle_id,
						v.reg_no,
						v.route,
						v.vehicle_model,
						v.type,
						v.sticker_no,
						v.rc_no,
						v.rc_exp_dt,
						v.daily_fuel_lmt,
						v.monthly_fuel_lmt,
						v.insurance_ref_no,
						v.insurance_exp_dt,
						v.road_tax_ref_no,
						v.road_tax_exp_dt,
						v.permit,
						v.permit_exp_dt,
						v.authorization,
						v.authorization_exp_dt,
						v.start_dt,
						v.end_dt,
						v.driver_id,
						v.cleaner_id,
						v.supervisor_id,
						v.client_id,
						v.is_active,
						v.created_dt,
						d.name driver_name,
						cl.name cleaner_name,
						s.name supervisor_name,
						c.name client_name
					FROM 
						tVehicle v
						LEFT OUTER JOIN tDriver d ON(d.driver_id=v.driver_id AND d.travel_id=v.travel_id)
						LEFT OUTER JOIN tSupervisor s ON(s.supervisor_id=v.supervisor_id AND s.travel_id=v.travel_id)
						LEFT OUTER JOIN tClient c ON(c.client_id=v.client_id AND c.travel_id=v.travel_id)
						LEFT OUTER JOIN tCleaner cl ON(cl.cleaner_id=v.cleaner_id AND cl.travel_id=v.travel_id)
					".$where_clause
					,$param_arr['params'], 
					false);
	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_vehicle_select(): END");
	return $resp;
}


// INSERT
function db_vehicle_insert(
							$reg_no,
							$route,
							$vehicle_model,
							$type,
							$sticker_no,
							$rc_no,
							$rc_exp_dt,
							$daily_fuel_lmt,
							$monthly_fuel_lmt,
							$insurance_ref_no,
							$insurance_exp_dt,
							$road_tax_ref_no,
							$road_tax_exp_dt,
							$permit,
							$permit_exp_dt,
							$authorization,
							$authorization_exp_dt,
							$start_dt,
							$end_dt,
							$driver_id,
							$supervisor_id,
							$client_id,
							$cleaner_id,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_vehicle_insert(): START { 
							reg_no=[$reg_no],
							route=[$route],
							vehicle_model=[$vehicle_model],
							type=[$type],
							sticker_no=[$sticker_no],
							rc_no=[$rc_no],
							rc_exp_dt=[$rc_exp_dt],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							insurance_ref_no=[$insurance_ref_no],
							insurance_exp_dt=[$insurance_exp_dt],
							road_tax_ref_no=[$road_tax_ref_no],
							road_tax_exp_dt=[$road_tax_exp_dt],
							permit=[$permit],
							permit_exp_dt=[$permit_exp_dt],
							authorization=[$authorization],
							authorization_exp_dt=[$authorization_exp_dt],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							driver_id=[$driver_id],
							supervisor_id=[$supervisor_id],
							client_id=[$client_id],
							cleaner_id=[$cleaner_id],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","route",$route);
	$param_arr=_db_prepare_param($param_arr,"s","vehicle_model",$vehicle_model);
	$param_arr=_db_prepare_param($param_arr,"s","type",$type);
	$param_arr=_db_prepare_param($param_arr,"s","sticker_no",$sticker_no);
	$param_arr=_db_prepare_param($param_arr,"s","rc_no",$rc_no);
	$param_arr=_db_prepare_param($param_arr,"s","rc_exp_dt",$rc_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"d","daily_fuel_lmt",$daily_fuel_lmt);
	$param_arr=_db_prepare_param($param_arr,"d","monthly_fuel_lmt",$monthly_fuel_lmt);
	$param_arr=_db_prepare_param($param_arr,"s","insurance_ref_no",$insurance_ref_no);
	$param_arr=_db_prepare_param($param_arr,"s","insurance_exp_dt",$insurance_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","road_tax_ref_no",$road_tax_ref_no);
	$param_arr=_db_prepare_param($param_arr,"s","road_tax_exp_dt",$road_tax_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","permit",$permit);
	$param_arr=_db_prepare_param($param_arr,"s","permit_exp_dt",$permit_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","authorization",$authorization);
	$param_arr=_db_prepare_param($param_arr,"s","authorization_exp_dt",$authorization_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","start_dt",$start_dt);
	$param_arr=_db_prepare_param($param_arr,"s","end_dt",$end_dt);
	$param_arr=_db_prepare_param($param_arr,"i","driver_id",$driver_id);
	$param_arr=_db_prepare_param($param_arr,"i","cleaner_id",$cleaner_id);
	$param_arr=_db_prepare_param($param_arr,"i","client_id",$client_id);
	$param_arr=_db_prepare_param($param_arr,"i","supervisor_id",$supervisor_id);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",date("Y-m-d H:i:s"));

	$resp=execSQL("INSERT INTO 
						tVehicle
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_vehicle_insert(): END");
	return $resp;

}



// UPDATE
function db_vehicle_update(	
							$vehicle_id,
							$reg_no,
							$route,
							$vehicle_model,
							$type,
							$sticker_no,
							$rc_no,
							$rc_exp_dt,
							$daily_fuel_lmt,
							$monthly_fuel_lmt,
							$insurance_ref_no,
							$insurance_exp_dt,
							$road_tax_ref_no,
							$road_tax_exp_dt,
							$permit,
							$permit_exp_dt,
							$authorization,
							$authorization_exp_dt,
							$start_dt,
							$end_dt,
							$driver_id,
							$supervisor_id,
							$client_id,
							$cleaner_id,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_vehicle_update(): START {
							vehicle_id=[$vehicle_id],
							reg_no=[$reg_no],
							route=[$route],
							vehicle_model=[$vehicle_model],
							type=[$type],
							sticker_no=[$sticker_no],
							rc_no=[$rc_no],
							rc_exp_dt=[$rc_exp_dt],
							daily_fuel_lmt=[$daily_fuel_lmt],
							monthly_fuel_lmt=[$monthly_fuel_lmt],
							insurance_ref_no=[$insurance_ref_no],
							insurance_exp_dt=[$insurance_exp_dt],
							road_tax_ref_no=[$road_tax_ref_no],
							road_tax_exp_dt=[$road_tax_exp_dt],
							start_dt=[$start_dt],
							end_dt=[$end_dt],
							driver_id=[$driver_id],
							supervisor_id=[$supervisor_id],
							client_id=[$client_id],
							cleaner_id=[$cleaner_id],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","route",$route);
	$param_arr=_db_prepare_param($param_arr,"s","vehicle_model",$vehicle_model);
	$param_arr=_db_prepare_param($param_arr,"s","type",$type);
	$param_arr=_db_prepare_param($param_arr,"s","sticker_no",$sticker_no);
	$param_arr=_db_prepare_param($param_arr,"s","rc_no",$rc_no);
	$param_arr=_db_prepare_param($param_arr,"s","rc_exp_dt",$rc_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"d","daily_fuel_lmt",$daily_fuel_lmt);
	$param_arr=_db_prepare_param($param_arr,"d","monthly_fuel_lmt",$monthly_fuel_lmt);
	$param_arr=_db_prepare_param($param_arr,"s","insurance_ref_no",$insurance_ref_no);
	$param_arr=_db_prepare_param($param_arr,"s","insurance_exp_dt",$insurance_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","road_tax_ref_no",$road_tax_ref_no);
	$param_arr=_db_prepare_param($param_arr,"s","road_tax_exp_dt",$road_tax_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","permit",$permit);
	$param_arr=_db_prepare_param($param_arr,"s","permit_exp_dt",$permit_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","authorization",$authorization);
	$param_arr=_db_prepare_param($param_arr,"s","authorization_exp_dt",$authorization_exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","start_dt",$start_dt);
	$param_arr=_db_prepare_param($param_arr,"s","end_dt",$end_dt);
	$param_arr=_db_prepare_param($param_arr,"i","driver_id",$driver_id);
	$param_arr=_db_prepare_param($param_arr,"i","supervisor_id",$supervisor_id);
	$param_arr=_db_prepare_param($param_arr,"i","client_id",$client_id);
	$param_arr=_db_prepare_param($param_arr,"i","cleaner_id",$cleaner_id);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);

	// For the where clause
	$where_clause=" WHERE vehicle_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","vehicle_id",$vehicle_id,true);


	$resp=execSQL("UPDATE  
						tVehicle
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_vehicle_update(): END");
	return $resp;
}

function db_vehicle_isactive_update(	
							$vehicle_id,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_vehicle_isactive_update(): START {
							vehicle_id=[$vehicle_id],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);

	// For the where clause
	$where_clause=" WHERE vehicle_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","vehicle_id",$vehicle_id,true);


	$resp=execSQL("UPDATE  
						tVehicle
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_vehicle_isactive_update(): END");
	return $resp;
}



// DELETE
function db_vehicle_delete($vehicle_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_vehicle_delete(): START { vehicle_id=[$vehicle_id]");

	// For the where clause
	$where_clause=" WHERE vehicle_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","vehicle_id",$vehicle_id,true);


	$resp=execSQL("DELETE FROM  
						tVehicle"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_vehicle_delete(): END");
	return $resp;
}


?>
