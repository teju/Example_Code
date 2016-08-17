<?php


// SELECT
function db_jobcard_select(
			$jobcard_id="",
			$reg_no="",
			$job_reference="",
			$date="",
			$details="",
			$amount="",
			$document="") {


	LOG_MSG('INFO',"db_jobcard_select: START { 
							jobcard_id=[$jobcard_id],
							reg_no=[$reg_no],
							job_reference=[$job_reference],
							date=[$date],
							details=[$details],
							amount=[$amount],
							document=[$document]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $jobcard_id !== "" ) { 
		$where_clause.=$seperator." jobcard_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","jobcard_id",$jobcard_id,true);
	} else {
		if ( $reg_no !== "" ) { 
			$where_clause.=$seperator." reg_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","reg_no","%".$reg_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $job_reference !== "" ) { 
			$where_clause.=$seperator." job_reference like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","job_reference","%".$job_reference."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $date !== "" ) { 
			$where_clause.=$seperator." date like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","date","%".$date."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $details !== "" ) { 
			$where_clause.=$seperator." details like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","details","%".$details."%",true);
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
	LOG_MSG('INFO',"db_jobcard_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						jobcard_id,
						reg_no,
						job_reference,
						date,
						details,			 
						amount,			 
						document			FROM 
					tJobCard
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_jobcard_select(): END");
	return $resp;
}


// INSERT
function db_jobcard_insert(
							$reg_no,
							$job_reference,
							$date,
							$details,
							$amount,
							$document) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_jobcard_insert(): START { 
							reg_no=[$reg_no],
							job_reference=[$job_reference],
							date=[$date],
							details=[$details]	
							amount=[$amount]	
							document=[$document]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","job_reference",$job_reference);
	$param_arr=_db_prepare_param($param_arr,"s","date",$date);
	$param_arr=_db_prepare_param($param_arr,"s","details",$details);
	$param_arr=_db_prepare_param($param_arr,"s","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","document",$document);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tJobCard
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_jobcard_insert(): END");
	return $resp;

}



// UPDATE
function db_jobcard_update(
							$jobcard_id,
							$reg_no,
							$job_reference,
							$date,
							$details,
							$amount,
							$document) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_jobcard_update(): START {
							jobcard_id=[$jobcard_id],
							reg_no=[$reg_no],
							job_reference=[$job_reference],
							date=[$date],
							details=[$details]	
							amount=[$amount]	
							document=[$document]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","jobcard_id",$jobcard_id);
	$param_arr=_db_prepare_param($param_arr,"s","reg_no",$reg_no);
	$param_arr=_db_prepare_param($param_arr,"s","job_reference",$job_reference);
	$param_arr=_db_prepare_param($param_arr,"s","date",$date);
	$param_arr=_db_prepare_param($param_arr,"s","details",$details);
	$param_arr=_db_prepare_param($param_arr,"s","amount",$amount);
	$param_arr=_db_prepare_param($param_arr,"s","document",$document);

	// For the where clause
	$where_clause=" WHERE jobcard_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","jobcard_id",$jobcard_id,true);


	$resp=execSQL("UPDATE  
						tJobCard
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_jobcard_update(): END");
	return $resp;
}



// DELETE
function db_jobcard_delete($jobcard_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_jobcard_delete(): START { jobcard_id=[$jobcard_id]");

	// For the where clause
	$where_clause=" WHERE jobcard_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","jobcard_id",$jobcard_id,true);


	$resp=execSQL("DELETE FROM  
						tJobCard"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_jobcard_delete(): END");
	return $resp;
}


?>
