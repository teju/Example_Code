<?php


// SELECT
function db_wallet_select(
			$wallet_id="",
			$user_id="",
			$created_dt="",
			$description="",
			$transaction_type="",
			$amount="",
			$balance_amount="") {


	LOG_MSG('INFO',"db_wallet_select: START { 
							wallet_id=[$wallet_id],
							user_id=[$user_id],
							created_dt=[$created_dt],
							description=[$description],
							transaction_type=[$transaction_type],
							amount=[$amount],
							balance_amount=[$balance_amount]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE org_id=".$_SESSION['org_id'];
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $wallet_id !== "" ) { 
		$where_clause.=$seperator." wallet_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","wallet_id",$wallet_id,true);
	} else {
		if ( $user_id !== "" ) { 
			$where_clause.=$seperator." user_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","user_id","%".$user_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}

		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $description !== "" ) { 
			$where_clause.=$seperator." description like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","description","%".$description."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $transaction_type !== "" ) { 
			$where_clause.=$seperator." transaction_type like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","transaction_type","%".$transaction_type."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $amount !== "" ) { 
			$where_clause.=$seperator." amount like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","amount","%".$amount."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $balance_amount !== "" ) { 
			$where_clause.=$seperator." balance_amount like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","balance_amount","%".$balance_amount."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE org_id=".$_SESSION['org_id'] ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_wallet_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						wallet_id,
						user_id,
						created_dt,
						description,
						transaction_type,
						amount,
						balance_amount				
					FROM 
						tWallet
						$where_clause
						ORDER BY 
							wallet_id "
					,$param_arr['params'], 
						false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_wallet_select(): END");
	return $resp;
}


// INSERT
function db_wallet_insert(
							$user_id,
							$imei,
							$created_dt,
							$description,
							$transaction_type,
							$amount,
							$balance_amount,
							$comment ) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_wallet_insert(): START { 
							user_id=[$user_id],
							imei=[$imei],
							created_dt=[$created_dt],
							description=[$description],
							transaction_type=[$transaction_type],
							amount=[$amount],
							balance_amount=[$balance_amount],
							comment=[$comment]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id);
	$param_arr=_db_prepare_param($param_arr,"s","imei",$imei);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);
	$param_arr=_db_prepare_param($param_arr,"s","description",$description);
	$param_arr=_db_prepare_param($param_arr,"s","transaction_type",$transaction_type);
	$param_arr=_db_prepare_param($param_arr,"d","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"d","balance_amount",$balance_amount);
	$param_arr=_db_prepare_param($param_arr,"s","comment",$comment);
	$param_arr=_db_prepare_param($param_arr,"i","org_id",$_SESSION['org_id']);

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
							$user_id,
							$created_dt,
							$description,
							$transaction_type,
							$amount,
							$balance_amount) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_wallet_update(): START {
							wallet_id=[$wallet_id],
							user_id=[$user_id],
							created_dt=[$created_dt],
							description=[$description],
							transaction_type=[$transaction_type],
							amount=[$amount],
							balance_amount=[$balance_amount]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id);
		$param_arr=_db_prepare_param($param_arr,"s","created_dt",$created_dt);
		$param_arr=_db_prepare_param($param_arr,"s","description",$description);
		$param_arr=_db_prepare_param($param_arr,"s","transaction_type",$transaction_type);
		$param_arr=_db_prepare_param($param_arr,"d","amount",$amount);
		$param_arr=_db_prepare_param($param_arr,"d","balance_amount",$balance_amount);

	// For the where clause
	$where_clause=" WHERE wallet_id=? AND org_id=".$_SESSION['org_id'];
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
	$where_clause=" WHERE wallet_id=? AND org_id=".$_SESSION['org_id'];
	$param_arr=_db_prepare_param($param_arr,"i","wallet_id",$wallet_id,true);


	$resp=execSQL("DELETE FROM  
						tWallet"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_wallet_delete(): END");
	return $resp;
}
// SELECT
function db_userimei_select(
			$user_id="",
			$imei="",
			$created_dt="") {


	LOG_MSG('INFO',"db_userimei_select: START { 
							user_id=[$user_id],
							imei=[$imei],
							created_dt=[$created_dt]	\n}");



	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $user_id !== "" ) { 
		$where_clause.=$seperator." um.user_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","user_id",$user_id,true);
	} else {
		if ( $imei !== "" ) { 
			$where_clause.=$seperator." um.imei like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $created_dt !== "" ) { 
			$where_clause.=$seperator." um.created_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","created_dt","%".$created_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_userimei_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						um.user_id,
						um.imei,
						um.created_dt,
						u.email_id email_id
					FROM 
					tUserIMEI um
					LEFT OUTER JOIN tUser u ON(um.user_id=u.user_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_userimei_select(): END");
	return $resp;
}

?>
