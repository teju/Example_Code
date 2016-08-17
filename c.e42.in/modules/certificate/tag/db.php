<?php

// SELECT
function db_tag_select(	$tag_id='',
						$tag_name='',
						$tag_value='',
						$is_standard_tag="") {

	LOG_MSG('INFO',"db_tag_select: START { 
							tag_id=[$tag_id],
							tag_name=[$tag_name],
							tag_value=[$tag_value]	\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE org_id=".$_SESSION['org_id'];
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $tag_id !== "" ) { 
		$where_clause.=$seperator." tag_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","tag_id",$tag_id,true);
	} else {
		if ( $tag_name !== "" ) { 
			$where_clause.=$seperator." tag_name = ? ";
			$param_arr=_db_prepare_param($param_arr,"s","tag_name",$tag_name,true);
		}
		if ( $tag_value !== "" ) { 
			$where_clause.=$seperator." tag_value = ? ";
			$param_arr=_db_prepare_param($param_arr,"s","tag_value",$tag_value,true);
		}
		if ( $is_standard_tag !== "" ) { 
			$where_clause.=$seperator." is_standard_tag = ? ";
			$param_arr=_db_prepare_param($param_arr,"i","is_standard_tag",$is_standard_tag,true);
		}
	}

	// No where clause
	if ( $where_clause === "WHERE org_id=".$_SESSION['org_id'] ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_tag_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						tag_id,
						tag_name,
						tag_value,
						is_tag_sync,
						type,
						is_active
					FROM 
						tTag 
					$where_clause"
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_tag_select(): END");
	return $resp;
}


// INSERT
function db_tag_insert(
							$tag_name,
							$tag_value,
							$is_standard_tag,
							$is_tag_sync,
							$type,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_tag_insert(): START { 
							tag_name=[$tag_name],
							tag_value=[$tag_value],
							is_standard_tag=[$is_standard_tag],
							is_tag_sync=[$is_tag_sync],
							type=[$type],
							is_active=[$is_active] \n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","tag_name",$tag_name);
	$param_arr=_db_prepare_param($param_arr,"s","tag_value",$tag_value);
	$param_arr=_db_prepare_param($param_arr,"i","is_standard_tag",$is_standard_tag);
	$param_arr=_db_prepare_param($param_arr,"i","is_tag_sync",$is_tag_sync);
	$param_arr=_db_prepare_param($param_arr,"s","type",$type);
	$param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"i","org_id",$_SESSION['org_id']);

	$resp=execSQL("INSERT INTO 
						tTag
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tag_insert(): END");
	return $resp;

}


// UPDATE
function db_tag_update( $tag_name,
						$is_active = "",
						$is_tag_sync="",
						$type="" ) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_tag_update(): START {
							tag_name=[$tag_name],
							is_active=[$is_active] 
							is_tag_sync=[$is_tag_sync],
							type=[$type] \n}");

	// Add params to params_arr
	if( $is_active !== "" ) $param_arr=_db_prepare_param($param_arr,"i","is_active",$is_active);
	if( $is_tag_sync !== "" ) $param_arr=_db_prepare_param($param_arr,"i","is_tag_sync",$is_tag_sync);
	if( $type !== "" ) $param_arr=_db_prepare_param($param_arr,"s","type",$type);

	// For the where clause
	$where_clause=" WHERE tag_name=? AND org_id=".$_SESSION['org_id'];
	$param_arr=_db_prepare_param($param_arr,"s","tag_name",$tag_name,true);


	$resp=execSQL("UPDATE  
						tTag
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_cleaner_update(): END");
	return $resp;
}




// DELETE
function db_tag_delete($tag_id="",$is_standard_tag,$tag_name="") {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_tag_delete(): START { 
										tag_id=[$tag_id],
										is_standard_tag=[$is_standard_tag],
										tag_name=[$tag_name],			}");

	$where_clause=" WHERE org_id=".$_SESSION['org_id'];
	$seperator=" AND ";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $tag_id !== "" ) { 
		$where_clause.=$seperator." tag_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","tag_id",$tag_id,true);
	}
	if ( $is_standard_tag !== "" ) { 
		$where_clause.=$seperator." is_standard_tag = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","is_standard_tag",$is_standard_tag,true);
	}
	if ( $tag_name !== "" ) { 
		$where_clause.=$seperator." tag_name = ? ";
		$param_arr=_db_prepare_param($param_arr,"s","tag_name",$tag_name,true);
	}

	$resp=execSQL("DELETE FROM  
						tTag"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_tag_delete(): END");
	return $resp;
}


?>
