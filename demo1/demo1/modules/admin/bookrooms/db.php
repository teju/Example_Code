<?php

// SELECT
function db_room_select(
			$room_id="",
			$room_name="") {

	LOG_MSG('INFO',"db_wallet_select: START { 
							room_id=[$room_id],
							room_name=[$room_name] \n}");
							
	$param_arr=_init_db_params();
	$where_clause="WHERE room_name=$room_name AND room_id = $room_id";
	$param_arr['params']=array();

	LOG_MSG('INFO',"db_room_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						room_name,
						room_id,
						is_booked
					FROM 
						bookrooms
					".$where_clause
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_room_select(): END");
	return $resp;
}

/*
// UPDATE
function db_wallet_update(	
							$wallet_id,
							$student_id,
							$location_id,
							$imei,
							$description,
							$transaction_type,
							$amount,
							$balance_amount) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_wallet_update(): START {
							wallet_id=[$wallet_id],
							student_id=[$student_id],
							location_id=[$location_id],
							imei=[$imei],
							description=[$description],
							transaction_type=[$transaction_type],
							amount=[$amount],
							balance_amount=[$balance_amount]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","description",$description);
	$param_arr=_db_prepare_param($param_arr,"s","transaction_type",$transaction_type);
	$param_arr=_db_prepare_param($param_arr,"s","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","balance_amount",$balance_amount);
	
	// For the where clause
	$where_clause=" WHERE wallet_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","wallet_id",$wallet_id,true);


	$resp=execSQL("UPDATE  
						tWallet
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_wallet_update(): END");
	return $resp;
}*/

?>
