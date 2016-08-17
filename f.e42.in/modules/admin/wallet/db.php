<?php


// SELECT
function db_wallet_select(
			$wallet_id="",
			$student_id="",
			$student_name="",
			$location_id="",
			$location_name="",
			$imei="",
			$description="",
			$transaction_type="",
			$amount="",
			$balance_amount="",
			$created_dt="") {


	LOG_MSG('INFO',"db_wallet_select: START { 
							wallet_id=[$wallet_id],
							student_id=[$student_id],
							student_name=[$student_name],
							location_id=[$location_id],
							location_name=[$location_name],
							imei=[$imei],
							description=[$description],
							transaction_type=[$transaction_type],
							amount=[$amount],
							balance_amount=[$balance_amount],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE w.travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $wallet_id !== "" ) { 
		$where_clause.=$seperator." w.wallet_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","wallet_id",$wallet_id,true);
	} else {
		if ( $student_id !== "" ) { 
			$where_clause.=$seperator." w.student_id like = ";
			$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $student_name !== "" ) { 
			$where_clause.=$seperator." s.name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$student_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $location_id !== "" ) { 
			$where_clause.=$seperator." w.location_id  = ? ";
			$param_arr=_db_prepare_param($param_arr,"i","location_id",$location_id,true);
			$seperator="AND ";
			$is_search=true;
		}

		if ( $location_name !== "" ) { 
			$where_clause.=$seperator." l.location_name  = ? ";
			$param_arr=_db_prepare_param($param_arr,"i","location_name",$location_name,true);
			$seperator="AND ";
			$is_search=true;
		}
	
		if ( $imei !== "" ) { 
			$where_clause.=$seperator." w.imei like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $description !== "" ) { 
			$where_clause.=$seperator." w.description like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","description","%".$description."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $transaction_type !== "" ) { 
			$where_clause.=$seperator." w.transaction_type like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","transaction_type","%".$transaction_type."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $amount !== "" ) { 
			$where_clause.=$seperator." w.amount like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","amount","%".$amount."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $balance_amount !== "" ) { 
			$where_clause.=$seperator." w.balance_amount like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","balance_amount","%".$balance_amount."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." w.created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE w.travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_wallet_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						w.wallet_id,
						s.name student_name,
						w.student_id,
						w.location_id,
						l.location_name location_name,
						w.imei,
						w.description,
						w.transaction_type,
						w.amount,
						w.balance_amount,
						w.created_dt
					FROM 
						tWallet w
						LEFT OUTER JOIN tStudent s ON(s.student_id=w.student_id AND s.travel_id=w.travel_id)
						LEFT OUTER JOIN tLocation l ON(l.location_id=w.location_id AND l.travel_id=w.travel_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_wallet_select(): END");
	return $resp;
}


// INSERT
function db_wallet_insert(
							$student_id,
							$location_id,
							$imei,
							$description,
							$transaction_type,
							$amount,
							$balance_amount) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_wallet_insert(): START { 
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
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	

	$resp=execSQL("INSERT INTO 
						tWallet
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_wallet_insert(): END");
	return $resp;

}



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
}


// DELETE
function db_wallet_delete($wallet_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_wallet_delete(): START { wallet_id=[$wallet_id]");

	// For the where clause
	$where_clause=" WHERE wallet_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","wallet_id",$wallet_id,true);


	$resp=execSQL("DELETE FROM  
						tWallet"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_wallet_delete(): END");
	return $resp;
}


?>
