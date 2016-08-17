<?php


// DB LOGIN
function db_do_login($email_id,$password) {

	LOG_MSG('INFO',"db_do_login(): START {
										email_id=[$email_id],
										password=[$password]\n}");
	$param_arr=_init_db_params();
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id,true);
	$param_arr=_db_prepare_param($param_arr,"s","password",$password,true);

	// Create Query
	$resp=execSQL("SELECT 
						u.user_id,
						u.name,
						u.email_id,
						u.password,
						u.phone_no,
						u.address,
						u.type,
						u.is_active,
						u.created_dt,
						s.supervisor_id,
						t.travel_id,
						t.name travel_name,
						t.domain
					FROM
						tUser u
						LEFT OUTER JOIN tSupervisor s ON(s.user_id=u.user_id)
						LEFT OUTER JOIN tTravel t ON(u.travel_id=t.travel_id)
					WHERE 
						u.email_id=? 
						AND u.password=? AND
						t.travel_id=".TRAVEL_ID
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_do_login(): END");
	return $resp;
}

?>
