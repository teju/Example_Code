<?php


// SELECT
function db_certificate_select(
			$certificate_id="",
			$usn="",
			$name="",
			$status="",
			$nfc_tag_id="") {

	LOG_MSG('INFO',"db_certificate_select: START { 
							certificate_id=[$certificate_id],
							usn=[$usn],
							name=[$name],
							status=[$status],
							nfc_tag_id=[$nfc_tag_id]	\n}");

	$param_arr=_init_db_params();
	$seperator=" AND";
	if ( $nfc_tag_id == "" && isset($_SESSION['org_id']) ) $where_clause="WHERE o.org_id=".$_SESSION['org_id'];
	else { 
	$where_clause="";
	$seperator="WHERE";
	}
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $certificate_id !== "" ) { 
		$where_clause.=$seperator." certificate_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id,true);
	} else {
		if ( $usn !== "" ) { 
			$where_clause.=$seperator." c.usn LIKE ? ";
			$param_arr=_db_prepare_param($param_arr,"s","usn","%".$usn."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $name !== "" ) { 
			$where_clause.=$seperator." c.name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $nfc_tag_id !== "" ) { 
			$where_clause.=$seperator." nt.nfc_tag_id = ? ";
			$param_arr=_db_prepare_param($param_arr,"s","nfc_tag_id",$nfc_tag_id,true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $status !== "" ) { 
			$where_clause.=$seperator." c.status = ? ";
			$param_arr=_db_prepare_param($param_arr,"s","status",$status,true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( (isset($_SESSION['org_id']) && $where_clause === "WHERE o.org_id=".$_SESSION['org_id']) || $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_certificate_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						c.certificate_id,
						c.usn,
						c.name,
						c.photo,
						c.status,
						o.org_id,
						o.name AS org_name,
						o.logo AS org_logo,
						nt.nfc_tag_id
					FROM 
						tCertificate c
						LEFT OUTER JOIN tNFCTag nt ON(c.usn=nt.usn)
						LEFT OUTER JOIN tOrg o ON(c.org_id=o.org_id)
					$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_certificate_select(): END");
	return $resp;
}


// INSERT
function db_certificate_insert(
							$usn,
							$name,
							$photo,
							$status) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_certificate_insert(): START { 
							usn=[$usn],
							name=[$name],
							status=[$status],
							photo=[$photo]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","usn",$usn);
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"s","status",$status);
	$param_arr=_db_prepare_param($param_arr,"i","org_id",$_SESSION['org_id']);

	$resp=execSQL("INSERT INTO 
						tCertificate
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_certificate_insert(): END");
	return $resp;

}



// UPDATE
function db_certificate_update(	
							$certificate_id,
							$usn,
							$name,
							$photo,
							$status) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_certificate_update(): START {
							certificate_id=[$certificate_id],
							usn=[$usn],
							name=[$name],
							photo=[$photo],
							status=[$status]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","usn",$usn);
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","photo",$photo);
	$param_arr=_db_prepare_param($param_arr,"s","status",$status);

	// For the where clause
	$where_clause=" WHERE certificate_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id,true);


	$resp=execSQL("UPDATE  
						tCertificate
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_certificate_update(): END");
	return $resp;
}



// DELETE
function db_certificate_delete($certificate_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_certificate_delete(): START { certificate_id=[$certificate_id]");

	// For the where clause
	$where_clause=" WHERE certificate_id=?";
	$param_arr=_db_prepare_param($param_arr,"i","certificate_id",$certificate_id,true);


	$resp=execSQL("DELETE FROM  
						tCertificate"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_certificate_delete(): END");
	return $resp;
}



// SELECT by roll no
function db_certificatetag_select(
								$roll_no="",$usn="") {

	LOG_MSG('INFO',"db_certificatetag_select: START { 
												roll_no=[$roll_no],
												usn=[$usn]\n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","tag_value",$roll_no,true);

	$resp=execSQL("SELECT 
						c.certificate_id
					FROM 
						tCertificate c
						LEFT OUTER JOIN tCertTag ct ON(ct.certificate_id=c.certificate_id)
						LEFT OUTER JOIN tTag t ON(ct.tag_id=t.tag_id)
					WHERE
						c.usn= ? AND 
						t.tag_name='Roll No' AND 
						t.tag_value = ? AND 
						c.org_id=".$_SESSION['org_id']
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_certificatetag_select(): END");
	return $resp;
}

// SELECT
function db_userimei_select(
			$imei="") {


	LOG_MSG('INFO',"db_userimei_select: START { 
							imei=[$imei]\n}");



	$param_arr=_init_db_params();
	$where_clause="";
	$seperator="WHERE ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required

		if ( $imei !== "" ) { 
			$where_clause.=$seperator." imei like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","imei","%".$imei."%",true);
			$seperator="AND ";
			$is_search=true;
		}

	// No where clause
	if ( $where_clause === "" ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_userimei_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						user_id,
						imei,
						created_dt
					FROM 
					tUserIMEI
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_userimei_select(): END");
	return $resp;
}


?>
