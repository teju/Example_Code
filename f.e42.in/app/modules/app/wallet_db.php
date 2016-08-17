<?php

function db_wallet_select(  $student_id="",
							$group_id="",
							$limit="") {

		LOG_MSG('INFO',"db_wallet_select() : Start {
							student_id=[$student_id]
							group_id=[$group_id]
							limit=[$limit]}" );
		
	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;
	
	// WHERE CLAUSE
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $group_id !== "" ) { 
		$where_clause.=$seperator." group_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $limit !== "" ) { 
		$limit="LIMIT $limit";
	}

	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_wallet_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						balance_amount,
						created_dt,
						amount,
						group_id,
						transaction_type
						FROM 
							tWallet
							$where_clause
						ORDER BY 
							wallet_id DESC
					$limit"
					,$param_arr['params'], 
						false);

		$resp[0]['IS_SEARCH']=$is_search;
		LOG_MSG('INFO',"db_wallet_select(): END");
		return $resp;
	}

function db_wallet_insert( $student_id,
							$location_id,
							$group_id,
							$imei,
							$description,
							$transaction_type,
							$amount,
							$balance_amount ) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_wallet_insert(): START { 
							student_id=[$student_id],
							location_id=[$location_id],
							group_id=[$group_id],
							imei=[$imei],
							description=[$description],
							transaction_type=[$transaction_type],
							amount=[$amount],
							balance_amount=[$balance_amount] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id);
	$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","description",$description);
	$param_arr=_db_prepare_param($param_arr,"s","transaction_type",$transaction_type);
	$param_arr=_db_prepare_param($param_arr,"s","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","balance_amount",$balance_amount);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",date("Y-m-d H:i:s"));
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tWallet
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_studentlog_insert(): END");
	return $resp;						
}

//Function to insert into nfctag table
function db_nfctag_insert( $nfc_tag_id,
							$id_number ) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_nfctag_insert(): START { 
							nfc_tag_id=[$nfc_tag_id],
							id_number=[$id_number] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","nfc_tag_id",$nfc_tag_id);
	$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tNFCTag
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_nfctag_insert(): END");
	return $resp;
}


//Function to insert into nfctag table
function db_student_insert( $name,
							$id_number,
							$phone_no) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_student_insert(): START { 
							name=[$name],
							id_number=[$id_number]
							phone_no=[$phone_no] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number);
	$param_arr=_db_prepare_param($param_arr,"s","phone",$phone_no);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",1);

	$resp=execSQL("INSERT INTO 
						tStudent
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_insert(): END");
	return $resp;
}

function db_location_select( $imei="") {

		LOG_MSG('INFO',"db_location_select() : Start {
							imei=[$imei] }" );

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $imei !== "" ) { 
		$where_clause.=$seperator." imei = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","imei",$imei,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_location_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						location_id,
						imei
					FROM 
						tImeiLocation
						$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_location_select(): END");
	return $resp;
}

function db_location_group_select( $location_id="") {

		LOG_MSG('INFO',"db_location_group_select() : Start {
							location_id=[$location_id] }" );

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $location_id !== "" ) { 
		$where_clause.=$seperator." location_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_location_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						location_id,
						group_id
					FROM 
						tLocationGroup
						$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_location_group_select(): END");
	return $resp;
}

function db_group_select( $group_id="") {

		LOG_MSG('INFO',"db_group_select() : Start {
							group_id=[$group_id] }" );

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	if ( $group_id !== "" ) { 
		$where_clause.=$seperator." group_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);
		$seperator="AND ";
		$is_search=true;
	}

	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						name,
						credit_limit
					FROM 
						tWalletGroup
						$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_group_select(): END");
	return $resp;
}
?>