<?php


// SELECT
function db_certtag_select(	$certificate_id='',$tag_id='',$roll_no='',$is_standard_tag=0) {

	LOG_MSG('INFO',"db_certtag_select: START { 
							certificate_id=[$certificate_id]	\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE is_active = 1 AND org_id = 33";
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $certificate_id !== "" ) { 
		$where_clause.=$seperator." ct.certificate_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id,true);
	}
 	if ( $tag_id !== "" ) { 
		$where_clause.=$seperator." t.tag_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","tag_id",$tag_id,true);
	} 
	if ( $roll_no !== "" ) {
		$where_clause.=$seperator." ct.roll_no = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","roll_no",$roll_no,true);
	} 
	if ( $is_standard_tag !== "" ) {
		$where_clause.=$seperator." t.is_standard_tag = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","is_standard_tag",$is_standard_tag,true);
	}
	// No where clause
	if ( $where_clause === "WHERE is_active = 1 AND org_id = 33" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_certtag_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						ct.certificate_id,
						t.tag_id,
						t.tag_name,
						t.tag_value
					FROM 
						tCertTag ct
						LEFT OUTER JOIN tTag t ON(ct.tag_id=t.tag_id)
					$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_certtag_select(): END");
	return $resp;
}


// INSERT
function db_certtag_insert(
							$certificate_id,
							$tag_id) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_certtag_insert(): START { 
							certificate_id=[$certificate_id],
							tag_id=[$tag_id]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id);
	$param_arr=_db_prepare_param($param_arr,"i","tag_id",$tag_id);

	$resp=execSQL("INSERT INTO 
						tCertTag
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_certtag_insert(): END");
	return $resp;

}



// DELETE
function db_certtag_delete($certificate_id="",$tag_id="") {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_certtag_delete(): START { certificate_id=[$certificate_id], tag_id=[$tag_id]");

	// For the where clause
	$where_clause="";
	$seperator=" WHERE";

	if ( $certificate_id == "" && $tag_id == "" ) {
		add_msg("ERROR","Either Certificate or Tag is mandatory");
		$resp["STATUS"]="ERROR";
		return $resp;
	}

	if ( $certificate_id !== "" ) { 
		$where_clause.=$seperator." certificate_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id,true);
		$seperator="AND ";
		$is_search=true;
	}
	if ( $tag_id !== "" ) { 
		$where_clause.=$seperator." tag_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","tag_id",$tag_id,true);
		$seperator="AND ";
		$is_search=true;
	}


	$resp=execSQL("DELETE FROM  
						tCertTag"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_certtag_delete(): END");
	return $resp;
}


?>
