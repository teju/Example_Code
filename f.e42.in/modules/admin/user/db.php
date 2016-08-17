<?php


// SELECT
function db_user_select(
			$user_id="",
			$name="",
			$email_id="",
			$password="",
			$phone_no="",
			$address="",
			$type="",
			$is_active="",
			$created_dt="") {


	LOG_MSG('INFO',"db_user_select: START { 
							user_id=[$user_id],
							name=[$name],
							email_id=[$email_id],
							password=[$password],
							phone_no=[$phone_no],
							address=[$address],
							type=[$type],
							is_active=[$is_active],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE type='ADMIN' AND  travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $user_id !== "" ) { 
		$where_clause.=$seperator." user_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $email_id !== "" ) { 
			$where_clause.=$seperator." email_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","email_id","%".$email_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $password !== "" ) { 
			$where_clause.=$seperator." password like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","password","%".$password."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $phone_no !== "" ) { 
			$where_clause.=$seperator." phone_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","phone_no","%".$phone_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $address !== "" ) { 
			$where_clause.=$seperator." address like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","address","%".$address."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $type !== "" ) { 
			$where_clause.=$seperator." type like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","type","%".$type."%",true);
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
	if ( $where_clause === "WHERE type='ADMIN' AND  travel_id=".TRAVEL_ID) {
		$param_arr['params']=array();
	}
	else
	
	
	LOG_MSG('INFO',"db_user_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						user_id,
						name,
						email_id,
						password,
						phone_no,
						address,
						type,
						is_active,
						created_dt				FROM 
					tUser
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_user_select(): END");
	return $resp;
}


// INSERT
function db_user_insert(
							$name,
							$email_id,
							$password,
							$phone_no,
							$address,
							$type,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_user_insert(): START { 
							name=[$name],
							email_id=[$email_id],
							password=[$password],
							phone_no=[$phone_no],
							address=[$address],
							type=[$type],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id);
	$param_arr=_db_prepare_param($param_arr,"s","password",$password);
	$param_arr=_db_prepare_param($param_arr,"i","phone_no",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","type",$type);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",date("Y-m-d H:i:s"));

	$resp=execSQL("INSERT INTO 
						tUser
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_user_insert(): END");
	return $resp;

}



// UPDATE
function db_user_update(	
							$user_id,
							$name,
							$email_id,
							$password,
							$phone_no,
							$address,
							$type,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_user_update(): START {
							user_id=[$user_id],
							name=[$name],
							email_id=[$email_id],
							password=[$password],
							phone_no=[$phone_no],
							address=[$address],
							type=[$type],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id);
	if ( $password != '' ) $param_arr=_db_prepare_param($param_arr,"s","password",$password);
	$param_arr=_db_prepare_param($param_arr,"i","phone_no",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","type",$type);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);

	// For the where clause
	$where_clause=" WHERE user_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);


	$resp=execSQL("UPDATE  
						tUser
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_user_update(): END");
	return $resp;
}



// DELETE
function db_user_delete($user_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_user_delete(): START { user_id=[$user_id]");

	// For the where clause
	$where_clause=" WHERE user_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);


	$resp=execSQL("DELETE FROM  
						tUser"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_user_delete(): END");
	return $resp;
}


?>
