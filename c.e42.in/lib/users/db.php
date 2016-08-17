<?php


// DB LOGIN
function db_do_login($email_id,$password) {

	LOG_MSG('INFO',"db_do_login(): START");
	$param_arr=_init_db_params();
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","password",$password,true);

	// Create Query
	$resp=execSQL("SELECT 
						u.user_id,
						u.type,
						u.fname,
						u.mobile,
						s.shop_id,
						s.domain,
						s.created_dt
					FROM
						tUser u 
						LEFT OUTER JOIN tShop AS s on (u.shop_id = s.shop_id)
					WHERE 
						email_id=? 
						AND password=?"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_do_login(): END");
	return $resp;
}


function db_permission_select($user_id,$function,$mode) {

LOG_MSG('INFO',"db_permission_select(): START { user_id=[$user_id],
												function=[$function],
												mode=[$mode]\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE ";
	
	$where_clause.=" up.user_id=? AND p.function = ? AND p.mode=? ";
	$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","function",$function,true);
	$param_arr=_db_prepare_param($param_arr,"s","mode",$mode,true);

	
	$resp=execSQL("SELECT 
						up.perm_id,
						p.perm_name
					FROM 
						tUserPerm as up
						LEFT OUTER JOIN tPerm as p ON (up.perm_id=p.perm_id) 
					".$where_clause
					,$param_arr['params'], 
					false); 
						
	//echo "<br><pre> RESP=".print_r($resp,true)."</pre><br>";
	LOG_MSG('INFO',"db_permission_select(): END");					
	return $resp;

}



function db_user_password_update($email_id,$password) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_user_password_update(): START {
							password=[$password],
							email_id=[$email_id]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","password",$password);

	// For the where clause
	$where_clause=" WHERE email_id=? ";
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id,true);

	$resp=execSQL("UPDATE  
						tUser
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_user_password_update(): END");
	return $resp;

}

function db_useractivity_insert_update($domain,
								$email_id,
								$activity) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_useractivity_insert_update(): START {
													domain=[$domain],
													email_id=[$email_id],
													activity=[$activity]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","domain",$domain,true);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","activity",$activity,true);

	$resp=execSQL("INSERT INTO 
							tShopActivity (domain,email_id,activity)
						VALUES 
							(?,?,?)
				ON DUPLICATE KEY UPDATE 
							counter=counter+1"
							,$param_arr['params'], 
							true);

	LOG_MSG('INFO',"db_useractivity_insert_update(): END");
	return $resp;

}

?>