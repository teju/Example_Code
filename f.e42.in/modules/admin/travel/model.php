<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_travel_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_travel_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$domain=get_arg($_GET,"domain");
	$logo=get_arg($_GET,"logo");
	$mobile=get_arg($_GET,"mobile");
	$address=get_arg($_GET,"address");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_travel_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Phone No",$mobile,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar")){
		LOG_MSG('ERROR',"do_travel_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_travel_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&mobile=$mobile&address=$address&";
	


	$ROW=db_travel_select(
		"",
			
			$name,
			$domain,
			$logo,
			$mobile,
			$address,
			$created_dt);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the travel. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=travel&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No travels found! <br />Click on <strong>Add travel</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_travel_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_travel_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_travel_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the travel ID
		$travel_id=get_arg($_GET,"travel_id");

		// Validate the ID
		if (
			!validate("travel Id",$travel_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_travel_view(): Invalid travel ID [$travel_id]!");
			return;
		}

		// Get from DB
		$ROW=db_travel_select($travel_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the travel. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No travel found! <br />Click on <strong>Add travel</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_travel_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_travel_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_travel_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_travel_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$name=get_arg($_POST,"name");
	$domain=get_arg($_POST,"domain");
	$logo=get_arg($_POST,"logo");
	$mobile=get_arg($_POST,"mobile");
	$address=get_arg($_POST,"address");
	$created_dt=get_arg($_POST,"created_dt");
	LOG_MSG('DEBUG',"do_travel_save(): Got args");
	



	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar") ||
		!validate("Phone No",$mobile,0,18,"bigint") ||
		!validate("Address",$address,0,500,"varchar")){
		LOG_MSG('ERROR',"do_travel_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_travel_save(): Validated args");

	if ( $_FILES['logo']['name'] != '' ) {
		$temp_image=$_FILES['logo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$logo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}
	}

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_travel_insert(
								$name,
								$domain,
								$logo,
								$mobile,
								$address,					
								$created_dt	);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The travel <strong>$name</strong> is already in use. Please enter a different travel<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the travel <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_travel_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New travel <strong>$name</strong> added successfully");
			break;
		case "UPDATE":
				$travel_id=get_arg($_POST,"travel_id");

			// Validate travel_id
			if (
				!validate("travel Id",$travel_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_travel_save(): Failed to validate PK");
				return;
			}

			$ROW=db_travel_update(
								$travel_id,
								$name,
								$domain,
								$logo,
								$mobile,
								$address,
								$created_dt
														);
					
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the travel <strong>$travel_id</strong> .");
				return;
			}
			add_msg("SUCCESS","travel <strong>$travel_id</strong> updated successfully");
			break;
	}

	// Upload the image after inserting as we need the org_id
	if ( $_FILES['logo']['name'] != '' && !upload_image("logo",IMG_DIR."travel/$logo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_travel_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_travel_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_travel_delete(): START POST=".print_r($_POST,true));

	$travel_id=get_arg($_POST,"travel_id");

	// Validate travel_id
	if (
		!validate("travel Id",$travel_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_travel_delete($travel_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The travel <strong>$travel_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the travel <strong>$travel_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","travel <strong>$travel_id</strong> has been removed.");
	LOG_MSG('INFO',"do_travel_delete(): END");

}

?>
