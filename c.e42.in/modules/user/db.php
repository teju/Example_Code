<?php

function db_get_user($email_id,$password="",$auto_login=false) {
	LOG_MSG('INFO',"db_get_user(): START { email[$email_id] pass[$password] auto[$auto_login]\n}");

	$where_clause='';
	$param_arr=_init_db_params();
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id,true);

	if ( !$auto_login ) {
		$where_clause=" AND password = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","password",$password,true);
	}
	LOG_MSG('INFO',"db_get_user(): WHERE CLAUSE = [$where_clause]");


	$resp=execSQL("SELECT 
						u.user_id,
						u.name,
						u.photo,
						u.email_id,
						u.user_type,
						u.is_active,
						o.org_id,
						o.name AS org_name,
						o.logo AS org_logo,
						o.org_id 
					FROM 
						tUser u
						LEFT OUTER JOIN tOrg o ON(u.org_id=o.org_id)
					WHERE 
						email_id = ? 
					".$where_clause
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_user(): END");
	return $resp;

}


// SELECT
function db_user_select(
			$user_id="",
			$name="",
			$photo="",
			$email_id="",
			$password="",
			$otp="",
			$user_type="",
			$is_active="",
			$created_dt="") {


	LOG_MSG('INFO',"db_user_select: START { 
							user_id=[$user_id],
							name=[$name],
							photo=[$photo],
							email_id=[$email_id],
							password=[$password],
							user_type=[$user_type],
							is_active=[$is_active],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	if ( !is_loggedin() ) {
		$where_clause="";
		$seperator=" WHERE";
	} else {
		$where_clause="WHERE org_id=".$_SESSION['org_id'];
		$seperator=" AND";
	}
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
		if ( $photo !== "" ) { 
			$where_clause.=$seperator." photo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","photo","%".$photo."%",true);
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
		if ( $user_type !== "" ) { 
			$where_clause.=$seperator." user_type like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","user_type","%".$user_type."%",true);
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
	if ( $where_clause === "WHERE org_id=".get_arg($_SESSION,'org_id') ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_user_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						user_id,
						name,
						photo,
						email_id,
						password,
						otp,
						user_type,
						is_active,
						created_dt
					FROM 
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
							$photo,
							$email_id,
							$password,
							$user_type,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_user_insert(): START { 
							name=[$name],
							photo=[$photo],
							email_id=[$email_id],
							password=[$password],
							user_type=[$user_type],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id);
	$param_arr=_db_prepare_param($param_arr,"s","password",$password);
	$param_arr=_db_prepare_param($param_arr,"s","user_type",$user_type);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	if ( is_loggedin() ) $param_arr=_db_prepare_param($param_arr,"i","org_id",$_SESSION["org_id"]);

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
							$name='',
							$photo='',
							$email_id='',
							$password='',
							$otp='',
							$user_type='',
							$is_active='',
							$is_remove_photo=false) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_user_update(): START {
							user_id=[$user_id],
							name=[$name],
							photo=[$photo],
							email_id=[$email_id],
							password=[$password],
							otp=[$otp],
							user_type=[$user_type],
							is_active=[$is_active],
							is_remove_photo=[$is_remove_photo]	\n}");

	// Add params to params_arr
	if ( $is_remove_photo ) {
		$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	} else {
		if ( $name !== '' ) $param_arr=_db_prepare_param($param_arr,"s","name",$name);
		if ( $photo !== '' ) $param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
		if ( $email_id !== '' ) $param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id);
		if ( $password !== '' ) $param_arr=_db_prepare_param($param_arr,"s","password",$password);
		if ( $otp !== '' ) $param_arr=_db_prepare_param($param_arr,"s","otp",$otp);
		if ( $user_type !== '' ) $param_arr=_db_prepare_param($param_arr,"s","user_type",$user_type);
		if ( $is_active !== '' ) $param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	}

	// For the where clause
	if ( is_loggedin() ) $where_clause=" WHERE user_id=? AND org_id=".$_SESSION['org_id'];
	else $where_clause=" WHERE user_id = ? ";
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



// UPDATE
function db_user_activate(	$email_id ) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_user_activate(): START {
							email_id=[$email_id]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","is_active",1);

	// For the where clause
	$where_clause=" WHERE email_id=?";
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id,true);

	$resp=execSQL("UPDATE  
						tUser
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_user_activate(): END");
	return $resp;
}



// DELETE
function db_user_delete($user_id='',$org_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_user_delete(): START { user_id=[$user_id]");

	// For the where clause
	$where_clause=" WHERE";
	$seperator='';
	if ( $user_id !== "" ) { 
		$where_clause.=$seperator." user_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);
		$seperator=' AND';
	}
	if ( $org_id !== "" ) { 
		$where_clause.=$seperator." org_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","org_id",$org_id,true);
	}

	$resp=execSQL("DELETE FROM  
						tUser"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_user_delete(): END");
	return $resp;
}


?>
