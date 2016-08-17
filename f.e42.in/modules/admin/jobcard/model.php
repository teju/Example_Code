<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_jobcard_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_jobcard_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$reg_no=get_arg($_GET,"reg_no");
	$job_reference=get_arg($_GET,"job_reference");
	$date=get_arg($_GET,"date") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"date"))) : get_arg($_GET,"date");
	$details=get_arg($_GET,"details");
	$amount=get_arg($_GET,"amount");
	$document=get_arg($_GET,"document");
	LOG_MSG('DEBUG',"do_jobcard_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("reg_no",$reg_no,0,200,"varchar") ||
		!validate("job_reference",$job_reference,0,200,"varchar") ||
		!validate("date",$date,0,10,"varchar") ||
		!validate("details",$details,0,500,"varchar") ||
		!validate("amount",$amount,0,500,"varchar") ||
		!validate("document",$document,0,18,"varchar") ){
		LOG_MSG('ERROR',"do_jobcard_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_jobcard_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="reg_no=$reg_no&job_reference=$job_reference&date=$date&details=$details&amount=$amount&document=$document&";


	$ROW=db_jobcard_select(
		"",
			
			$reg_no,
			$job_reference,
			$date,
			$details,
			$amount,
			$document);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the jobcard. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=jobcard&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No jobcards found! <br />Click on <strong>Add jobcard</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_jobcard_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_jobcard_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_jobcard_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the jobcard ID
		$jobcard_id=get_arg($_GET,"jobcard_id");

		// Validate the ID
		if (
			!validate("jobcard Id",$jobcard_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_jobcard_view(): Invalid jobcard ID [$jobcard_id]!");
			return;
		}

		// Get from DB
		$ROW=db_jobcard_select($jobcard_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the jobcard. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No jobcard found! <br />Click on <strong>Add jobcard</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_jobcard_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_jobcard_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_jobcard_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_jobcard_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$reg_no=get_arg($_POST,"reg_no");
	$job_reference=get_arg($_POST,"job_reference");
	$date=get_arg($_POST,"date") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"date"))) : get_arg($_POST,"date");
	$details=get_arg($_POST,"details");
	$amount=get_arg($_POST,"amount");
	$document=get_arg($_POST,"document");
	LOG_MSG('DEBUG',"do_jobcard_save(): Got args");


	// Validate parameters
	if (
		!validate("reg_no",$reg_no,1,200,"varchar") ||
		!validate("job_reference",$job_reference,0,200,"varchar") ||
		!validate("date",$date,0,10,"varchar") ||
		!validate("details",$details,0,500,"varchar")||
		!validate("amount",$amount,0,500,"varchar")||
		!validate("document",$document,0,500,"varchar")){
		LOG_MSG('ERROR',"do_jobcard_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_jobcard_save(): Validated args");

	

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_jobcard_insert(
								$reg_no,
								$job_reference,
								$date,
								$details,					
								$amount,					
								$document	);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The jobcard <strong>$name</strong> is already in use. Please enter a different jobcard<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the jobcard <strong>$jobcard_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_jobcard_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New jobcard <strong>$reg_no</strong> added successfully");
			break;
		case "UPDATE":
			// Validate jobcard_id
			
				$jobcard_id=get_arg($_POST,"jobcard_id");

			if (
				!validate("jobcard Id",$jobcard_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_jobcard_save(): Failed to validate PK");
				return;
			}

			$ROW=db_jobcard_update(
								$jobcard_id,
								$reg_no,
								$job_reference,
								$date,
								$details,
								$amount,
								$document
														);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the jobcard <strong>$jobcard_id</strong> .");
				return;
			}
			add_msg("SUCCESS","jobcard <strong>$jobcard_id</strong> updated successfully");
			break;
	}

	// Upload the image after inserting as we need the org_id
	//if ( $_FILES['photo']['name'] != '' && !upload_image("photo",IMG_DIR."jobcard/$photo") ) {
		//add_msg("ERROR","Error while uploading the image");
		//return;
	//}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_jobcard_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_jobcard_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_jobcard_delete(): START POST=".print_r($_POST,true));

	$jobcard_id=get_arg($_POST,"jobcard_id");

	// Validate jobcard_id
	if (
		!validate("jobcard Id",$jobcard_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_jobcard_delete($jobcard_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The jobcard <strong>$jobcard_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the jobcard <strong>$jobcard_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","jobcard <strong>$jobcard_id</strong> has been removed.");
	LOG_MSG('INFO',"do_jobcard_delete(): END");

}





?>
