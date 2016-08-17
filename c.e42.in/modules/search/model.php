<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_search_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_search_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
		$user_id=get_arg($_GET,"user_id");
		$certificate_id=get_arg($_GET,"certificate_id");
		$search_result=get_arg($_GET,"search_result");
		$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_search_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("User Id",$user_id,0,11,"varchar") ||
		!validate("Certificate Id",$certificate_id,0,11,"varchar") ||
		!validate("Search Result",$search_result,0,200,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_search_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_search_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="
		user_id=$user_id&
		certificate_id=$certificate_id&
		search_result=$search_result&
		created_dt=$created_dt	";


	$ROW=db_search_select(
		"",
			$user_id,
			$certificate_id,
			$search_result,
			$created_dt	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Searchs. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=search&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Searchs found! <br />Click on <strong>Add Search</strong> to create a one.<br />"); 
	}

	// Load foreign key arrays
	if ( 'user_id' !== '0' ) {
		$row_user=db_get_fk_values(
													'tUser',
													'user_id'
												);
	}
	if ( 'certificate_id' !== '0' ) {
		$row_certificate=db_get_fk_values(
													'tCertificate',
													'certificate_id'
												);
	}
	if ( '0' !== '0' ) {
		$row_0=db_get_fk_values(
													'0',
													'0'
												);
	}
	if ( '0' !== '0' ) {
		$row_0=db_get_fk_values(
													'0',
													'0'
												);
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_search_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_search_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_search_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Search ID
		$search_id=get_arg($_GET,"search_id");

		// Validate the ID
		if (
			!validate("Search Id",$search_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_search_view(): Invalid Search ID [$search_id]!");
			return;
		}

		// Get from DB
		$ROW=db_search_select($search_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Search. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Searchs found! <br />Click on <strong>Add Search</strong> to create a one.<br /><br />"); 
			return;
		}
	}

	$disabled="";
	// Setup display parameters
	switch($mode) {
		case "ADD":
				$_do="add";
				break;
		case "EDIT":
				$_do="save";
				break;
		case "DELETE":
				$_do="remove";
				$disabled="disabled";
				break;
		case "VIEW":
		default:
				$disabled="disabled";
				break;
	}


	// Load foreign key arrays
	if ( 'user_id' !== '0' ) {
		$row_user=db_get_fk_values(
													'tUser',
													'user_id'
												);
	}
	if ( 'certificate_id' !== '0' ) {
		$row_certificate=db_get_fk_values(
													'tCertificate',
													'certificate_id'
												);
	}
	if ( '0' !== '0' ) {
		$row_0=db_get_fk_values(
													'0',
													'0'
												);
	}
	if ( '0' !== '0' ) {
		$row_0=db_get_fk_values(
													'0',
													'0'
												);
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_search_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_search_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_search_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
		$search_id=get_arg($_POST,"search_id");
		$user_id=get_arg($_POST,"user_id");
		$certificate_id=get_arg($_POST,"certificate_id");
		$search_result=get_arg($_POST,"search_result");
		$created_dt=get_arg($_POST,"created_dt");
	LOG_MSG('DEBUG',"do_search_save(): Got args");


	// Validate parameters
	if (
		!validate("User Id",$user_id,1,11,"int") ||
		!validate("Certificate Id",$certificate_id,1,11,"int") ||
		!validate("Search Result",$search_result,0,200,"varchar") ||
		!validate("Created Dt",$created_dt,1,30,"datetime") 	){
		LOG_MSG('ERROR',"do_search_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_search_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_search_insert(
								$user_id,
								$certificate_id,
								$search_result,
								$created_dt						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Search <strong>$search_id</strong> is already in use. Please enter a different Search<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Search <strong>$search_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_search_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Search <strong>$search_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate search_id
			if (
				!validate("Search Id",$search_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_search_save(): Failed to validate PK");
				return;
			}

			$ROW=db_search_update(
								$search_id,
								$user_id,
								$certificate_id,
								$search_result,
								$created_dt						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Search <strong>$search_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Search <strong>$search_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_search_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_search_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_search_delete(): START POST=".print_r($_POST,true));

	$search_id=get_arg($_POST,"search_id");
	$search_id=get_arg($_POST,"search_id");

	// Validate search_id
	if (
		!validate("Search Id",$search_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_search_delete($search_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Search <strong>$search_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Search <strong>$search_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","Search <strong>$search_id</strong> has been removed.");
	LOG_MSG('INFO',"do_search_delete(): END");

}





?>
