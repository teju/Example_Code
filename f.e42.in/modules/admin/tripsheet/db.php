<?php


// SELECT
function db_tripsheet_select(
			$tripsheet_id="",
			$reg_no="",
			$date="",
			$no_of_trips="",
			$amount="",
			$document="") {


	LOG_MSG('INFO',"db_tripsheet_select: START { 
							tripsheet_id=[$tripsheet_id],
							reg_no=[$reg_no],
							date=[$date],
							no_of_trips=[$no_of_trips],
							amount=[$amount],
							document=[$document] \n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $tripsheet_id !== "" ) { 
		$where_clause.=$seperator." tripsheet_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","tripsheet_id",$tripsheet_id,true);
	} else {
	if ( $reg_no !== "" ) { 
			$where_clause.=$seperator." reg_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}if ( $date !== "" ) { 
			$where_clause.=$seperator." date like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","date","%".$date."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $no_of_trips !== "" ) { 
			$where_clause.=$seperator." no_of_trips like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","no_of_trips","%".$no_of_trips."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $amount !== "" ) { 
			$where_clause.=$seperator." amount like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","amount","%".$amount."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $document !== "" ) { 
			$where_clause.=$seperator." document like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","document","%".$document."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "WHERE travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_tripsheet_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						tripsheet_id,
						reg_no,
						date,
						no_of_trips,
						amount,
						document
										FROM 
					tTripSheet
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_tripsheet_select(): END");
	return $resp;
}


// INSERT
function db_tripsheet_insert(
							$reg_no,
							$date,
							$no_of_trips,
							$amount,
							$document) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_tripsheet_insert(): START { 
							reg_no=[$reg_no],
							date=[$date],
							no_of_trips=[$no_of_trips],
							amount=[$amount],
							document=[$document]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","date",$date);
	$param_arr=_db_prepare_param($param_arr,"s","no_of_trips",$no_of_trips);
	$param_arr=_db_prepare_param($param_arr,"s","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","document",$document);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);


	$resp=execSQL("INSERT INTO 
						tTripSheet
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tripsheet_insert(): END");
	return $resp;

}



// UPDATE
function db_tripsheet_update(	
							$tripsheet_id,
							$reg_no,
							$date,
							$no_of_trips,
							$amount,
							$document) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_tripsheet_update(): START {
							tripsheet_id=[$tripsheet_id],
							reg_no=[$reg_no],
							date=[$date],
							no_of_trips=[$no_of_trips],
							amount=[$amount],
							document=[$document]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","date",$date);
	$param_arr=_db_prepare_param($param_arr,"i","no_of_trips",$no_of_trips);
	$param_arr=_db_prepare_param($param_arr,"d","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","document",$document);
	
	// For the where clause
	$where_clause=" WHERE tripsheet_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","tripsheet_id",$tripsheet_id,true);


	$resp=execSQL("UPDATE  
						tTripSheet
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tripsheet_update(): END");
	return $resp;
}



// DELETE
function db_tripsheet_delete($tripsheet_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_tripsheet_delete(): START { tripsheet_id=[$tripsheet_id]");

	// For the where clause
	$where_clause=" WHERE tripsheet_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","tripsheet_id",$tripsheet_id,true);


	$resp=execSQL("DELETE FROM  
						tTripSheet"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tripsheet_delete(): END");
	return $resp;
}


?>
