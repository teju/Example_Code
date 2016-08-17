<?php

/********************************************************************************/
/*                                SHOP SETTINGS                                 */
/********************************************************************************/
function modulesetting_isset($name) {
	global $MODULE_SETTINGS;
	$is_set=(get_arg($MODULE_SETTINGS,$name) == 1) ? 1 : 0;
	LOG_MSG('INFO',"modulesetting_isset(): name=[$name] is_set=[$is_set]");
	return $is_set;

}

function modulesetting_get($name) {
	global $MODULE_SETTINGS;
	$value=get_arg($MODULE_SETTINGS,$name);
	LOG_MSG('INFO',"modulesetting_get(): name=[$name] value=[$value]");
	return $value; 
}

function modulesetting_init() {
	global $MODULE_SETTINGS;

	$MODULE_SETTINGS=array();
	LOG_MSG('INFO',"modulesetting_init(): START");

	$resp=execSQL("SELECT 
						name,value
					FROM 
						tSetting 
					WHERE
						is_hidden=1 AND 
						TRAVEL_ID=".TRAVEL_ID
					,array(), 
					false);
	if ( $resp[0]['STATUS'] != 'OK' ) {  
		add_msg("ERROR","There was an error loading your module settings. Please contact customer care");
		return false;
	}

	LOG_MSG('INFO',"modulesetting_init(): Loaded ".$resp[0]['NROWS']." settings");
	for ($i=0;$i<$resp[0]['NROWS'];$i++) {
		$name=$resp[$i]['name'];
		$value=$resp[$i]['value'];
		$MODULE_SETTINGS[$name]=$value;
	}
	LOG_ARR("INFO","SHOP SETTINGS", $MODULE_SETTINGS);

	LOG_MSG('INFO',"modulesetting_init(): END");
	return true;
}

function modulesetting_set($name,$value,$desc="no desc",$type="text",$category="GENERAL",$is_hidden=0) {

	LOG_MSG('INFO',"modulesetting_set(): START { name=[$name] value=[$value] desc=[$desc] type=[$type] category=[$category] is_hidden=[$is_hidden]}");

	$param_arr=_init_db_params();

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","value",$value);
	$param_arr=_db_prepare_param($param_arr,"s","description",$desc);
	$param_arr=_db_prepare_param($param_arr,"s","type",$type);
	$param_arr=_db_prepare_param($param_arr,"s","category",$category);
	$param_arr=_db_prepare_param($param_arr,"i","is_hidden",$is_hidden);

	$param_arr=_db_prepare_param($param_arr,"s","value",$value);

	// For the where clause
	/*$resp=execSQL("UPDATE  
						tShopSetting
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);
	*/
	$resp=execSQL("INSERT INTO tShopSetting
			(name,
			value,
			description,
			type,
			category,
			is_hidden,
			module_id) 
		       VALUES 
			(?,?,?,?,?,?,".SHOP_ID.") 
		       ON DUPLICATE KEY UPDATE 
			value=?;
		",$param_arr['params'],true);
	if ( $resp['STATUS'] != 'OK' ) {  
		LOG_MSG('ERROR',"modulesetting_set(): There was an error updating the modulesetting =[$name]");
		return false;
	}
	return true;
}



/********************************************************************************/
/*                                SHOP SETTINGS DB                              */
/********************************************************************************/
function modulesetting_db_isset($name,$category='') {

	LOG_MSG('INFO',"modulesetting_db_isset(): START { name=[$name]\n}");

	$param_arr=_init_db_params();

	// Where Clause
	$where_clause="WHERE name = ? AND TRAVEL_ID=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);

	if ( $category != '' ) {
		$where_clause.=" AND category=? ";
		$param_arr=_db_prepare_param($param_arr,"s","category",$category,true);
	}

	$resp=execSQL("SELECT 
						value
					FROM 
						tSetting 
					$where_clause "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"modulesetting_db_isset(): END");

	if ( $resp[0]['STATUS'] != 'OK' ) {  
		LOG_MSG('ERROR',"modulesetting_db_isset(): There was an error loading name=[$name]");
		return false;
	}

	if ( $resp[0]['NROWS'] != 1 ) {
		LOG_MSG('INFO',"modulesetting_isset(): No value found for [$name]");
		return false;
	}

	if ( $resp[0]['value'] == 1 ) return 1;	else return 0;
}

function modulesetting_db_get($name,$category='') {

	LOG_MSG('INFO',"modulesetting_db_get(): START { name=[$name] category=[$category] \n}");

	$param_arr=_init_db_params();

	// Where Clause
	$where_clause="WHERE name = ? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);

	if ( $category != '' ) {
		$where_clause.=" AND category=? ";
		$param_arr=_db_prepare_param($param_arr,"s","category",$category,true);
	}

	$resp=execSQL("SELECT 
						value
					FROM 
						tSetting 
					$where_clause "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"modulesetting_db_get(): END");

	if ( $resp[0]['STATUS'] != 'OK' ) {  
		LOG_MSG('ERROR',"modulesetting_db_get(): There was an error loading name=[$name]");
		return false;
	}

	if ( $resp[0]['NROWS'] != 1 ) {
		LOG_MSG('INFO',"modulesetting_get(): No value found for [$name]");
		return false;
	}

	return $resp[0]['value'];
}


?>
