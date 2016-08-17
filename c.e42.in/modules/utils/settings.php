<?php

/********************************************************************************/
/*                                SHOP SETTINGS                                 */
/********************************************************************************/
function shopsetting_isset($name) {
	global $SHOP_SETTINGS;
	$is_set=(get_arg($SHOP_SETTINGS,$name) == 1) ? 1 : 0;
	LOG_MSG('INFO',"shopsetting_isset(): name=[$name] is_set=[$is_set]");
	return $is_set;

}

function shopsetting_get($name) {
	global $SHOP_SETTINGS;
	$value=get_arg($SHOP_SETTINGS,$name);
	LOG_MSG('INFO',"shopsetting_get(): name=[$name] value=[$value]");
	return $value; 
}

function shopsetting_init() {
	global $SHOP_SETTINGS;

	$SHOP_SETTINGS=array();
	LOG_MSG('INFO',"shopsetting_init(): START");

	$resp=execSQL("SELECT 
						name,value
					FROM 
						tShopSetting 
					WHERE
						category NOT LIKE 'APP\_%' AND 
						shop_id=".SHOP_ID
					,array(), 
					false);
	if ( $resp[0]['STATUS'] != 'OK' ) {  
		add_msg("ERROR","There was an error loading your shop settings. Please contact customer care");
		return false;
	}

	LOG_MSG('INFO',"shopsetting_init(): Loaded ".$resp[0]['NROWS']." settings");
	for ($i=0;$i<$resp[0]['NROWS'];$i++) {
		$name=$resp[$i]['name'];
		$value=$resp[$i]['value'];
		$SHOP_SETTINGS[$name]=$value;
	}
	LOG_ARR("INFO","SHOP SETTINGS", $SHOP_SETTINGS);

	LOG_MSG('INFO',"shopsetting_init(): END");
	return true;
}

function shopsetting_set($name,$value) {

	LOG_MSG('INFO',"shopsetting_set(): START { name=[$name] value=[$value]}");

	$param_arr=_init_db_params();

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","value",$value);

	// For the where clause
	$where_clause=" WHERE name=? AND shop_id=".SHOP_ID;
	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);

	$resp=execSQL("UPDATE  
						tShopSetting
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	if ( $resp['STATUS'] != 'OK' ) {  
		LOG_MSG('ERROR',"shopsetting_set(): There was an error updating the shopsetting =[$name]");
		return false;
	}
	return true;
}



/********************************************************************************/
/*                                SHOP SETTINGS DB                              */
/********************************************************************************/
function shopsetting_db_isset($name,$category='') {

	LOG_MSG('INFO',"shopsetting_db_isset(): START { name=[$name]\n}");

	$param_arr=_init_db_params();

	// Where Clause
	$where_clause="WHERE name = ? AND shop_id=".SHOP_ID;
	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);

	if ( $category != '' ) {
		$where_clause.=" AND category=? ";
		$param_arr=_db_prepare_param($param_arr,"s","category",$category,true);
	}

	$resp=execSQL("SELECT 
						value
					FROM 
						tShopSetting 
					$where_clause "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"shopsetting_db_isset(): END");

	if ( $resp[0]['STATUS'] != 'OK' ) {  
		LOG_MSG('ERROR',"shopsetting_db_isset(): There was an error loading name=[$name]");
		return false;
	}

	if ( $resp[0]['NROWS'] != 1 ) {
		LOG_MSG('INFO',"shopsetting_isset(): No value found for [$name]");
		return false;
	}

	if ( $resp[0]['value'] == 1 ) return 1;	else return 0;
}

function shopsetting_db_get($name,$category='') {

	LOG_MSG('INFO',"shopsetting_db_get(): START { name=[$name] category=[$category] \n}");

	$param_arr=_init_db_params();

	// Where Clause
	$where_clause="WHERE name = ? AND shop_id=".SHOP_ID;
	$param_arr=_db_prepare_param($param_arr,"s","name",$name,true);

	if ( $category != '' ) {
		$where_clause.=" AND category=? ";
		$param_arr=_db_prepare_param($param_arr,"s","category",$category,true);
	}

	$resp=execSQL("SELECT 
						value
					FROM 
						tShopSetting 
					$where_clause "
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"shopsetting_db_get(): END");

	if ( $resp[0]['STATUS'] != 'OK' ) {  
		LOG_MSG('ERROR',"shopsetting_db_get(): There was an error loading name=[$name]");
		return false;
	}

	if ( $resp[0]['NROWS'] != 1 ) {
		LOG_MSG('INFO',"shopsetting_get(): No value found for [$name]");
		return false;
	}

	return $resp[0]['value'];
}


?>