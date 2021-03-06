<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_client_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_client_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$photo=get_arg($_GET,"photo");
	$mobile=get_arg($_GET,"mobile");
	$address=get_arg($_GET,"address");
	LOG_MSG('DEBUG',"do_client_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$mobile,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar")){
		LOG_MSG('ERROR',"do_client_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_client_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&photo=$photo&mobile=$mobile&address=$address&";


	$ROW=db_client_select(
		"",
			
			$name,
			$photo,
			$mobile,
			$address);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the client. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=client&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Clients found! <br />Click on <strong>Add Client</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_client_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_client_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_client_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the client ID
		$client_id=get_arg($_GET,"client_id");

		// Validate the ID
		if (
			!validate("Client Id",$client_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_client_view(): Invalid client ID [$client_id]!");
			return;
		}

		// Get from DB
		$ROW=db_client_select($client_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the client. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Client found! <br />Click on <strong>Add client</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_client_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_client_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_client_save(): START (mode=$mode) POST=".print_r($_POST,true));
	//LOG_MSG('INFO',"do_client_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$name=get_arg($_POST,"name");
	$photo=get_arg($_POST,"photo");
	$mobile=get_arg($_POST,"mobile");
	$address=get_arg($_POST,"address");
	LOG_MSG('DEBUG',"do_client_save(): Got args");


	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$mobile,0,18,"bigint") ||
		!validate("Address",$address,0,500,"varchar")){
		LOG_MSG('ERROR',"do_client_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_client_save(): Validated args");

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
	db_transaction_start();
	switch($mode) {
		case "ADD":
			$ROW=db_client_insert(
								$name,
								$photo,
								$mobile,
								$address					);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The client <strong>$name</strong> is already in use. Please enter a different client<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the client <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_client_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New client <strong>$name</strong> added successfully");
			if ( $ROW['NROWS'] == 1 && !app_sync('tClient',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
				return;
			}
			break;
		case "UPDATE":
			$client_id=get_arg($_POST,"client_id");
			// Validate client_id
			if (
				!validate("client Id",$client_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_client_save(): Failed to validate PK");
				return;
			}

			$ROW=db_client_update(
								$client_id,
								$name,
								$photo,
								$mobile,
								$address
														);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the client <strong>$client_id</strong> .");
				return;
			}
			add_msg("SUCCESS","client <strong>$client_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tClient',$client_id,'U',TRAVEL_ID )) {
				return;
			}
			break;
	}

	// Upload the image after inserting as we need the org_id
	if ( $_FILES['photo']['name'] != '' && !upload_image("photo",IMG_DIR."client/$photo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}
	db_transaction_end();
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_client_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_client_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_client_delete(): START POST=".print_r($_POST,true));

	$client_id=get_arg($_POST,"client_id");

	// Validate client_id
	if (
		!validate("client Id",$client_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_client_delete($client_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The client <strong>$client_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the client <strong>$client_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","client <strong>$client_id</strong> has been removed.");
	if( $ROW['NROWS'] == 1 && !app_sync('tClient',$client_id,'D',TRAVEL_ID )) {
		return;
	}
	LOG_MSG('INFO',"do_client_delete(): END");

}





?>
