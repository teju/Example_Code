<?php


// SELECT
function db_order_select(
			$order_id="",
			$name="",
			$logo="") {

	LOG_MSG('INFO',"db_order_select: START { 
							order_id=[$order_id],
							name=[$name],
							logo=[$logo]	\n}");

	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $order_id !== "" ) { 
		$where_clause.=$seperator." order_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","order_id",$order_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $logo !== "" ) { 
			$where_clause.=$seperator." logo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","logo","%".$logo."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_order_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						order_id,
						name,
						logo
					FROM 
						torder 
					$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_order_select(): END");
	return $resp;
}


// INSERT
function db_order_insert(
							$name,
							$email_id,
							$mobile,
							$amount,
							$pmt_status,
							$pmt_type,
							$order_status) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_order_insert(): START { 
							name=[$name],
							email_id=[$email_id],
							mobile=[$mobile],
							amount=[$amount],
							pmt_status=[$pmt_status],
							pmt_type=[$pmt_type],
							order_status=[$order_status]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$name);
	$param_arr=_db_prepare_param($param_arr,"s","mobile",$name);
	$param_arr=_db_prepare_param($param_arr,"d","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","pmt_status",$pmt_status);
	$param_arr=_db_prepare_param($param_arr,"s","pmt_type",$pmt_type);
	$param_arr=_db_prepare_param($param_arr,"s","order_status",$order_status);
	$param_arr=_db_prepare_param($param_arr,"s","ordered_dt",date("Y-m-d H:i:s"));
	$param_arr=_db_prepare_param($param_arr,"i","org_id",$_SESSION["org_id"]);

	$resp=execSQL("INSERT INTO 
						tOrder
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_order_insert(): END");
	return $resp;

}



// UPDATE
function db_order_update(	
							$order_id,
							$pmt_status,
							$pmt_type,
							$order_status,
							$comments,
							$ordered_dt) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_order_update(): START { 
							order_id=[$order_id]
							pmt_status=[$pmt_status],
							pmt_type=[$pmt_type],
							order_status=[$order_status],
							comments=[$comments],
							ordered_dt=[$ordered_dt]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","pmt_status",$pmt_status);
	$param_arr=_db_prepare_param($param_arr,"s","pmt_type",$pmt_type);
	$param_arr=_db_prepare_param($param_arr,"s","order_status",$order_status);
	$param_arr=_db_prepare_param($param_arr,"s","comments",$comments);
	$param_arr=_db_prepare_param($param_arr,"s","ordered_dt",$ordered_dt);
	$param_arr=_db_prepare_param($param_arr,"s","last_modified",date("Y-m-d H:i:s"));

	// For the where clause
	$where_clause=" WHERE order_id = ? AND org_id=".$_SESSION["org_id"];
	$param_arr=_db_prepare_param($param_arr,"i","order_id",$order_id,true);


	$resp=execSQL("UPDATE  
						torder
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_order_update(): END");
	return $resp;
}



// DELETE
function db_order_delete($order_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_order_delete(): START { order_id=[$order_id]");

	// For the where clause
	$where_clause=" WHERE order_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","order_id",$order_id,true);


	$resp=execSQL("DELETE FROM  
						torder"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_order_delete(): END");
	return $resp;
}


?>
