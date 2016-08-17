<?php


// SELECT
function db_tagsticker_select(
			$nfc_tag_id="",
			$sticker_no="") {


	LOG_MSG('INFO',"db_tagsticker_select: START { 
							nfc_tag_id=[$nfc_tag_id],
							sticker_no=[$sticker_no]	\n}");



	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $nfc_tag_id !== "" ) { 
		$where_clause.=$seperator." nfc_tag_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","nfc_tag_id",$nfc_tag_id,true);
	} else {
		if ( $sticker_no !== "" ) { 
			$where_clause.=$seperator." sticker_no like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","sticker_no","%".$sticker_no."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_tagsticker_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						nfc_tag_id,
						sticker_no				FROM 
					tTagSticker
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_tagsticker_select(): END");
	return $resp;
}


// INSERT
function db_tagsticker_insert(
							$sticker_no) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_tagsticker_insert(): START { 
							sticker_no=[$sticker_no]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"s","sticker_no",$sticker_no);

	$resp=execSQL("INSERT INTO 
						tTagSticker
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tagsticker_insert(): END");
	return $resp;

}



// UPDATE
function db_tagsticker_update(	
							$nfc_tag_id,
							$sticker_no) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_tagsticker_update(): START {
							nfc_tag_id=[$nfc_tag_id],
							sticker_no=[$sticker_no]	\n}");

	// Add params to params_arr
		$param_arr=_db_prepare_param($param_arr,"s","sticker_no",$sticker_no);

	// For the where clause
	$where_clause=" WHERE nfc_tag_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","nfc_tag_id",$nfc_tag_id,true);


	$resp=execSQL("UPDATE  
						tTagSticker
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tagsticker_update(): END");
	return $resp;
}



// DELETE
function db_tagsticker_delete($nfc_tag_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_tagsticker_delete(): START { nfc_tag_id=[$nfc_tag_id]");

	// For the where clause
	$where_clause=" WHERE nfc_tag_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","nfc_tag_id",$nfc_tag_id,true);


	$resp=execSQL("DELETE FROM  
						tTagSticker"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tagsticker_delete(): END");
	return $resp;
}


?>
