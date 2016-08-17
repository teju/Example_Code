<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_tagsticker_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_tagsticker_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
		$sticker_no=get_arg($_GET,"sticker_no");
	LOG_MSG('DEBUG',"do_tagsticker_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Sticker No",$sticker_no,0,45,"varchar") 	){
		LOG_MSG('ERROR',"do_tagsticker_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_tagsticker_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="sticker_no=$sticker_no	";


	$ROW=db_tagsticker_select(
		"",
			$sticker_no	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Tag Stickers. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=tagsticker&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Tag Stickers found! <br />Click on <strong>Add Tag Sticker</strong> to create a one.<br />"); 
	}

	// Load foreign key arrays
	if ( '0' !== '0' ) {
		$row_0=db_get_fk_values(
													'0',
													'0'
												);
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_tagsticker_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_tagsticker_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_tagsticker_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Tag Sticker ID
		$nfc_tag_id=get_arg($_GET,"tagsticker_id");

		// Validate the ID
		if (
			!validate("Nfc Tag Id",$nfc_tag_id,1,45,"varchar")
		) { 
			LOG_MSG('ERROR',"go_tagsticker_view(): Invalid Tag Sticker ID [$nfc_tag_id]!");
			return;
		}

		// Get from DB
		$ROW=db_tagsticker_select($tagsticker_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Tag Sticker. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Tag Stickers found! <br />Click on <strong>Add Tag Sticker</strong> to create a one.<br /><br />"); 
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

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_tagsticker_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_tagsticker_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_tagsticker_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
		$nfc_tag_id=get_arg($_POST,"nfc_tag_id");
		$sticker_no=get_arg($_POST,"sticker_no");
	LOG_MSG('DEBUG',"do_tagsticker_save(): Got args");


	// Validate parameters
	if (
		!validate("Sticker No",$sticker_no,1,45,"varchar") 	){
		LOG_MSG('ERROR',"do_tagsticker_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_tagsticker_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_tagsticker_insert(
								$sticker_no						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Tag Sticker <strong>$nfc_tag_id</strong> is already in use. Please enter a different Tag Sticker<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Tag Sticker <strong>$nfc_tag_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_tagsticker_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Tag Sticker <strong>$nfc_tag_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate nfc_tag_id
			if (
				!validate("Nfc Tag Id",$nfc_tag_id,1,45,"varchar")
			) { 
				LOG_MSG('ERROR',"do_tagsticker_save(): Failed to validate PK");
				return;
			}

			$ROW=db_tagsticker_update(
								$nfc_tag_id,
								$sticker_no						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Tag Sticker <strong>$nfc_tag_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Tag Sticker <strong>$nfc_tag_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_tagsticker_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_tagsticker_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_tagsticker_delete(): START POST=".print_r($_POST,true));

	$nfc_tag_id=get_arg($_POST,"nfc_tag_id");
	$nfc_tag_id=get_arg($_POST,"nfc_tag_id");

	// Validate nfc_tag_id
	if (
		!validate("Nfc Tag Id",$nfc_tag_id,1,45,"varchar")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_tagsticker_delete($nfc_tag_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Tag Sticker <strong>$nfc_tag_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Tag Sticker <strong>$nfc_tag_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","Tag Sticker <strong>$nfc_tag_id</strong> has been removed.");
	LOG_MSG('INFO',"do_tagsticker_delete(): END");

}





?>
