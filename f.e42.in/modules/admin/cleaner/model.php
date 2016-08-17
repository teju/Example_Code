<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_cleaner_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_cleaner_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$photo=get_arg($_GET,"photo");
	$mobile=get_arg($_GET,"mobile");
	$address=get_arg($_GET,"address");
	$salary=get_arg($_GET,"salary");
	LOG_MSG('DEBUG',"do_cleaner_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$mobile,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("Salary",$salary,0,10,"decimal") ){
		LOG_MSG('ERROR',"do_cleaner_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_cleaner_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&photo=$photo&mobile=$mobile&address=$address&salary=$salary&";
	


	$ROW=db_cleaner_select(
		"",
			
			$name,
			$photo,
			$mobile,
			$address,
			$salary);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the cleaner. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=cleaner&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No cleaners found! <br />Click on <strong>Add cleaner</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_cleaner_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_cleaner_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_cleaner_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the cleaner ID
		$cleaner_id=get_arg($_GET,"cleaner_id");

		// Validate the ID
		if (
			!validate("cleaner Id",$cleaner_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_cleaner_view(): Invalid cleaner ID [$cleaner_id]!");
			return;
		}

		// Get from DB
		$ROW=db_cleaner_select($cleaner_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the cleaner. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No cleaner found! <br />Click on <strong>Add cleaner</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_cleaner_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_cleaner_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_cleaner_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_cleaner_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$name=get_arg($_POST,"name");
	$photo=get_arg($_POST,"photo");
	$mobile=get_arg($_POST,"mobile");
	$address=get_arg($_POST,"address");
	$salary=get_arg($_POST,"salary");
	LOG_MSG('DEBUG',"do_cleaner_save(): Got args");



	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$mobile,0,18,"bigint") ||
		!validate("Address",$address,0,500,"varchar")||
		!validate("Salary",$salary,0,500,"varchar")){
		LOG_MSG('ERROR',"do_cleaner_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_cleaner_save(): Validated args");

	if ( $_FILES['photo']['name'] != '' ) {
		$temp_image=$_FILES['photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}
	}

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_cleaner_insert(
								$name,
								$photo,
								$mobile,
								$address,					
								$salary	);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The cleaner <strong>$name</strong> is already in use. Please enter a different cleaner<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the cleaner <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_cleaner_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New cleaner <strong>$name</strong> added successfully");
			break;
		case "UPDATE":
				$cleaner_id=get_arg($_POST,"cleaner_id");

			// Validate cleaner_id
			if (
				!validate("cleaner Id",$cleaner_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_cleaner_save(): Failed to validate PK");
				return;
			}

			$ROW=db_cleaner_update(
								$cleaner_id,
								$name,
								$photo,
								$mobile,
								$address,
								$salary
														);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the cleaner <strong>$cleaner_id</strong> .");
				return;
			}
			add_msg("SUCCESS","cleaner <strong>$cleaner_id</strong> updated successfully");
			break;
	}

	// Upload the image after inserting as we need the org_id
	if ( $_FILES['photo']['name'] != '' && !upload_image("photo",IMG_DIR."cleaner/$photo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_cleaner_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_cleaner_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_cleaner_delete(): START POST=".print_r($_POST,true));

	$cleaner_id=get_arg($_POST,"cleaner_id");

	// Validate cleaner_id
	if (
		!validate("cleaner Id",$cleaner_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_cleaner_delete($cleaner_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The cleaner <strong>$cleaner_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the cleaner <strong>$cleaner_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","cleaner <strong>$cleaner_id</strong> has been removed.");
	LOG_MSG('INFO',"do_cleaner_delete(): END");

}

?>
