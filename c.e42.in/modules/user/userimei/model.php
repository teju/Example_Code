<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_userimei_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_userimei_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
		$imei=get_arg($_GET,"imei");
		$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_userimei_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Imei",$imei,0,45,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_userimei_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_userimei_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="
		imei=$imei&
		created_dt=$created_dt	";


	$ROW=db_userimei_select(
		"",
			$imei,
			$created_dt	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the User I M E Is. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=userimei&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No User I M E Is found! <br />Click on <strong>Add User I M E I</strong> to create a one.<br />"); 
	}

	// Load foreign key arrays
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

	LOG_MSG('INFO',"go_userimei_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_userimei_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_userimei_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the User I M E I ID
		$imei=get_arg($_GET,"userimei_id");

		// Validate the ID
		if (
			!validate("User Id",$user_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_userimei_view(): Invalid User I M E I ID [$imei]!");
			return;
		}

		// Get from DB
		$ROW=db_userimei_select($userimei_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the User I M E I. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No User I M E Is found! <br />Click on <strong>Add User I M E I</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_userimei_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_userimei_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_userimei_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
		$user_id=get_arg($_POST,"user_id");
		$imei=get_arg($_POST,"imei");
		$created_dt=get_arg($_POST,"created_dt");
	LOG_MSG('DEBUG',"do_userimei_save(): Got args");


	// Validate parameters
	if (
		!validate("Imei",$imei,1,45,"varchar") ||
		!validate("Created Dt",$created_dt,1,30,"datetime") 	){
		LOG_MSG('ERROR',"do_userimei_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_userimei_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_userimei_insert(
								$imei,
								$created_dt						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The User I M E I <strong>$imei</strong> is already in use. Please enter a different User I M E I<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the User I M E I <strong>$imei</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_userimei_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New User I M E I <strong>$imei</strong> added successfully");
			break;
		case "UPDATE":
			// Validate imei
			if (
				!validate("User Id",$user_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_userimei_save(): Failed to validate PK");
				return;
			}

			$ROW=db_userimei_update(
								$user_id,
								$imei,
								$created_dt						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the User I M E I <strong>$imei</strong> .");
				return;
			}
			add_msg("SUCCESS","User I M E I <strong>$imei</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_userimei_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_userimei_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_userimei_delete(): START POST=".print_r($_POST,true));

	$imei=get_arg($_POST,"imei");
	$imei=get_arg($_POST,"imei");

	// Validate imei
	if (
		!validate("User Id",$user_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_userimei_delete($imei);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The User I M E I <strong>$imei</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the User I M E I <strong>$imei</strong>");
		}
		return;
	}

	add_msg("SUCCESS","User I M E I <strong>$imei</strong> has been removed.");
	LOG_MSG('INFO',"do_userimei_delete(): END");

}





?>
