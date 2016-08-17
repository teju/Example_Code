<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_wallet_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_wallet_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$student_id=get_arg($_GET,"student_id");
	$student_name=get_arg($_GET,"student_name");
	$location_id=get_arg($_GET,"location_id");
	$location_name=get_arg($_GET,"location_name");
	$imei=get_arg($_GET,"imei");
	$description=get_arg($_GET,"description");
	$transaction_type=get_arg($_GET,"transaction_type");
	$amount=get_arg($_GET,"amount");
	$balance_amount=get_arg($_GET,"balance_amount");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_wallet_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Student Id",$student_id,0,11,"int") ||
		!validate("Student Name",$student_name,0,200,"varchar") ||
		!validate("Location Id",$location_id,0,11,"int") ||
		!validate("Imei",$imei,0,200,"varchar") ||
		!validate("Description",$description,0,500,"varchar") ||
		!validate("Transaction Type",$transaction_type,0,"'CR','DR'",'enum')  ||
		!validate("Amount",$amount,0,12,"decimal") ||
		!validate("Balance Amount",$balance_amount,0,12,"decimal")){
		LOG_MSG('ERROR',"do_wallet_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_wallet_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="student_id=$student_id&location_id=$location_id&imei=$imei&description=$description&transaction_type=$transaction_type&amount=$amount&balance_amount=$balance_amount";
	


	$ROW=db_wallet_select(
		"",
			$student_id,
			$student_name,
			$location_id,
			$location_name,
			$imei,
			$description,
			$transaction_type,
			$amount,
			$balance_amount,
			$created_dt);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the wallet. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=wallet&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No wallets found! <br />Click on <strong>Add wallet</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_wallet_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_wallet_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_wallet_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the wallet ID
		$wallet_id=get_arg($_GET,"wallet_id");

		// Validate the ID
		if (
			!validate("wallet Id",$wallet_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_wallet_view(): Invalid wallet ID [$wallet_id]!");
			return;
		}

		// Get from DB
		$ROW=db_wallet_select($wallet_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the wallet. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No wallet found! <br />Click on <strong>Add wallet</strong> to create a one.<br /><br />"); 
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
	// To get student details
    if ( ($row_student=db_get_list('ARRAY','name,student_id','tStudent','travel_id='.TRAVEL_ID)) === false ) return;
    if ( ($row_location=db_get_list('ARRAY','location_name,location_id','tLocation','travel_id='.TRAVEL_ID)) === false ) return;

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_wallet_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_wallet_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_wallet_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Do we have a search string?
	// Get all the args from $_GET
	$student_id=get_arg($_POST,"student_id");
	$location_id=get_arg($_POST,"location_id");
	$imei=get_arg($_POST,"imei");
	$description=get_arg($_POST,"description");
	$transaction_type=get_arg($_POST,"transaction_type");
	$amount=get_arg($_POST,"amount");
	$balance_amount=get_arg($_POST,"balance_amount");
	$created_dt=get_arg($_POST,"created_dt");
	LOG_MSG('DEBUG',"do_wallet_list(): Got args");



	// Validate parameters as normal strings 
	if (
		!validate("Student Name",$student_id,0,200,"varchar") ||
		!validate("Location Id",$location_id,0,11,"int") ||
		!validate("Imei",$imei,0,200,"varchar") ||
		!validate("Description",$description,0,500,"varchar") ||
		!validate("Transaction Type",$transaction_type,0,"'CR','DR'",'enum')  ||
		!validate("Amount",$amount,0,12,"decimal") ||
		!validate("Balance Amount",$balance_amount,0,12,"decimal")){
		LOG_MSG('ERROR',"do_wallet_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_wallet_list(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_wallet_insert(
								$student_id,
								$location_id,
								$imei,
								$description,
								$transaction_type,
								$amount,
								$balance_amount,
								$created_dt);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The wallet <strong>$name</strong> is already in use. Please enter a different wallet<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the wallet <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_wallet_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New wallet <strong>$student_id</strong> added successfully");
			break;
		case "UPDATE":
				$wallet_id=get_arg($_POST,"wallet_id");

			// Validate wallet_id
			if (
				!validate("wallet Id",$wallet_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_wallet_save(): Failed to validate PK");
				return;
			}

			$ROW=db_wallet_update(
								$wallet_id,
								$student_id,
								$location_id,
								$imei,
								$description,
								$transaction_type,
								$amount,
								$balance_amount,
								$created_dt);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the wallet <strong>$wallet_id</strong> .");
				return;
			}
			add_msg("SUCCESS","wallet <strong>$wallet_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_wallet_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_wallet_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_wallet_delete(): START POST=".print_r($_POST,true));

	$wallet_id=get_arg($_POST,"wallet_id");

	// Validate wallet_id
	if (
		!validate("wallet Id",$wallet_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_wallet_delete($wallet_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The wallet <strong>$wallet_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the wallet <strong>$wallet_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","wallet <strong>$wallet_id</strong> has been removed.");
	LOG_MSG('INFO',"do_wallet_delete(): END");

}

?>
