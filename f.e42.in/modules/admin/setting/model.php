<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_setting_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE, $IMAGE_CAPTURE_RANGE;
	LOG_MSG('INFO',"go_setting_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$value=get_arg($_GET,"value");
	$effective_date=get_arg($_GET,"effective_date") != '' ? date('Y-m-d h:i:s',strtotime(get_arg($_GET,"effective_date"))) : get_arg($_GET,"effective_date");
	LOG_MSG('DEBUG',"do_setting_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,100,"varchar") ||
		!validate("Value",$value,0,100,"varchar")||
		!validate("Effective date",$effective_date,0,45,"varchar") 	){
		LOG_MSG('ERROR',"do_setting_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_setting_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&value=$value$effective_date=$effective_date	";


	$ROW=db_setting_select(
		"",
			$name,
			$value,
			$effective_date	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Settings. Please try again later. <br/>");
		return;
	}
	


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=setting&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Settings found! <br />Click on <strong>Add Setting</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_setting_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_setting_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE, $IMAGE_CAPTURE_RANGE;
	LOG_MSG('INFO',"go_setting_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Setting ID
		$setting_id=get_arg($_GET,"setting_id");

		// Validate the ID
		if ( !validate("Setting",$setting_id,1,11,"int") ) { 
			LOG_MSG('ERROR',"go_setting_view(): Invalid Setting ID [$setting_id]!");
			return;
		}

		// Get from DB
		$ROW=db_setting_select($setting_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Setting. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Settings found! <br />Click on <strong>Add Setting</strong> to create a one.<br /><br />"); 
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

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_setting_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_setting_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_setting_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Adding setting should be restricted
	if ( $mode == "ADD" ) {
		add_msg("ERROR","Sorry! You do not have the previlege to perform this action.");
		return;
	}

	// Get all the args from $_POST
	$setting_id=get_arg($_POST,"setting_id");
	$name=get_arg($_POST,"name");
	$value=get_arg($_POST,"value");
	$effective_date=date('Y-m-d H:i:s',strtotime(get_arg($_POST,"effective_date")));	
	LOG_MSG('DEBUG',"do_setting_save(): Got args");


	// Validate parameters
	if (
		!validate("Name",$name,0,100,"varchar") ||
		!validate("Value",$value,0,100,"varchar") ||
		!validate("Effective date",$effective_date,0,45,"varchar") ) {
		LOG_MSG('ERROR',"do_setting_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_setting_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	db_transaction_start();
	switch($mode) {
		case "ADD":
			$ROW=db_setting_insert(
								$name,
								$value,
								$effective_date						);
								
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Setting <strong>$setting_id</strong> is already in use. Please enter a different Setting<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Setting <strong>$setting_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_setting_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Setting <strong>$setting_id</strong> added successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tSetting',$ROW['INSERT_ID'],'A',TRAVEL_ID)) {
				return;
			}
			break;
		case "UPDATE":
			// Validate setting_id
			if (
				!validate("Setting Id",$setting_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_setting_save(): Failed to validate PK");
				return;
			}

			$ROW=db_setting_update(
								$setting_id,
								$value,
								$effective_date						);
								
							
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Setting <strong>$setting_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Setting <strong>$setting_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1) {
				app_sync('tSetting',$setting_id,'U',TRAVEL_ID) ;
			}
			break;
	}
	db_transaction_end();
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_setting_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_setting_delete() {

	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_setting_delete(): START POST=".print_r($_POST,true));

	$setting_id=get_arg($_POST,"setting_id");

	// Validate setting_id
	if (
		!validate("Setting Id",$setting_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	db_transaction_start();
	$ROW=db_setting_delete($setting_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Setting <strong>$setting_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Setting <strong>$setting_id</strong>");
		}
		return;
	}
	if( $ROW['NROWS'] == 1 && !app_sync('tSetting',$setting_id,'D',TRAVEL_ID)) {
		 return;
	}
	db_transaction_end();
	add_msg("SUCCESS","Setting <strong>$setting_id</strong> has been removed.");
	LOG_MSG('INFO',"do_setting_delete(): END");

}





?>
