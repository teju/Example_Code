<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_tripsheet_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_tripsheet_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$reg_no=get_arg($_GET,"reg_no");
	$date=get_arg($_GET,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"date"))) : get_arg($_GET,"date");
	$no_of_trips=get_arg($_GET,"no_of_trips");
	$amount=get_arg($_GET,"amount");
	$document=get_arg($_GET,"document");
	
	LOG_MSG('DEBUG',"do_tripsheet_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Reg no",$reg_no,0,200,"varchar") ||
		!validate("Date",$date,0,200,"varchar") ||
		!validate("No of Trips",$no_of_trips,0,200,"varchar") ||
		!validate("Amount",$amount,0,200,"varchar") ||
		!validate("Document No",$document,0,18,"varchar")  ){
		LOG_MSG('ERROR',"do_tripsheet_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_tripsheet_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="reg_no=$reg_no&date=$date&no_of_trips=$no_of_trips&amount=$amount&document=$document&";


	$ROW=db_tripsheet_select(
		"",
			
			$reg_no,
			$date,
			$no_of_trips,
			$amount,
			$document);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the tripsheet. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=tripsheet&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No tripsheets found! <br />Click on <strong>Add tripsheet</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_tripsheet_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_tripsheet_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_tripsheet_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the tripsheet ID
		$tripsheet_id=get_arg($_GET,"tripsheet_id");

		// Validate the ID
		if (
			!validate("tripsheet Id",$tripsheet_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_tripsheet_view(): Invalid tripsheet ID [$tripsheet_id]!");
			return;
		}

		// Get from DB
		$ROW=db_tripsheet_select($tripsheet_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the tripsheet. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No tripsheet found! <br />Click on <strong>Add tripsheet</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_tripsheet_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_tripsheet_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_tripsheet_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_tripsheet_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$reg_no=get_arg($_POST,"reg_no");
	$date=get_arg($_POST,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"date"))) : get_arg($_POST,"date");
	$no_of_trips=get_arg($_POST,"no_of_trips");
	$amount=get_arg($_POST,"amount");
	$document=get_arg($_POST,"document");
	LOG_MSG('DEBUG',"do_tripsheet_save(): Got args");


	// Validate parameters
	if (
		!validate("reg_no",$reg_no,0,200,"varchar") ||
		!validate("date",$date,0,200,"varchar") ||
		!validate("No of Trips",$no_of_trips,0,200,"varchar") ||
		!validate("Amount",$amount,0,200,"varchar") ||
		!validate("Document No",$document,0,18,"varchar")  ){
		LOG_MSG('ERROR',"do_tripsheet_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_tripsheet_save(): Validated args");


	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_tripsheet_insert(
								$reg_no,
								$date,
								$no_of_trips,
								$amount,
								$document);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The tripsheet <strong>$name</strong> is already in use. Please enter a different tripsheet<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the tripsheet <strong>$tripsheet_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_tripsheet_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New tripsheet <strong>$no_of_trips</strong> added successfully");
			break;
		case "UPDATE":
				$tripsheet_id=get_arg($_POST,"tripsheet_id");

			// Validate tripsheet_id
			if (
				!validate("tripsheet Id",$tripsheet_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_tripsheet_save(): Failed to validate PK");
				return;
			}

			$ROW=db_tripsheet_update(
								$tripsheet_id,
								$reg_no,
								$date,
								$no_of_trips,
								$amount,
								$document);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the tripsheet <strong>$tripsheet_id</strong> .");
				return;
			}
			add_msg("SUCCESS","tripsheet <strong>$tripsheet_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_tripsheet_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_tripsheet_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_tripsheet_delete(): START POST=".print_r($_POST,true));

	$tripsheet_id=get_arg($_POST,"tripsheet_id");

	// Validate tripsheet_id
	if (
		!validate("tripsheet Id",$tripsheet_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_tripsheet_delete($tripsheet_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The tripsheet <strong>$tripsheet_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the tripsheet <strong>$tripsheet_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","tripsheet <strong>$tripsheet_id</strong> has been removed.");
	LOG_MSG('INFO',"do_tripsheet_delete(): END");

}





?>
