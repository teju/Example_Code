<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_org_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_org_list(): START GET=".print_r($_GET,true));

        if ( !is_superuser() ) {
                add_msg('ERROR','Sorry! You do not have sufficient privileges');
                return;
        }

	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$logo=get_arg($_GET,"logo");
	LOG_MSG('DEBUG',"do_org_list(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("logo",$logo,0,100,"varchar") ){
		LOG_MSG('ERROR',"do_org_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_org_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="
		name=$name&
		logo=$logo";


	$ROW=db_org_select(
		"",
			$name,
			$logo);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the orgs. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=org&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No orgs found! <br />Click on <strong>Add org</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_org_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_org_view($mode="EDIT") {

	global $ROW, $TEMPLATE, $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_org_view(): START");

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get args
		$org_id=get_arg($_GET,"org_id");

		// Validate args
		if ( !validate("Org ID",$org_id,1,11,"int") ) {
			LOG_MSG('ERROR',"go_org_view(): VALIDATE ARGS FAILED!");
			return;
		}

		$_SESSION['org_id']=$org_id;

		// Get from DB
		$ROW=db_org_select($org_id);
		// Error selecting
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the org. Please try again later.");
			LOG_ARR('INFO','ROW',$ROW);
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Results found!"); 
			LOG_ARR("INFO","ROW",$ROW);
			return;
		}

	}

	$disabled="";
	// Setup display parameters
	switch($mode) {
		case "ADD":
				if ( !isset($ROW[0]) ) {
					$ROW[0]=array();
					$ROW[0]['logo']='no_image.jpg';
				}
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

	// Initialize organization
	if ( $mode == 'EDIT' && !init_org($org_id) ) {
		add_msg("ERROR","Invalid organization. Please contact customer care");
		show_msgs();
		return;
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_org_view(): END");
}


/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_org_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW,$_GET;

	LOG_MSG('INFO',"do_org_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_org_save(): START FILES=".print_r($_FILES,true));

	if ( $mode == 'ADD' && !is_superuser() ) {
		add_msg('ERROR','Sorry! You do not have sufficient privileges');
		return;
	}

	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$org_id=get_arg($_POST,"org_id");
	$name=get_arg($_POST,"name");
	$logo=get_arg($_POST,"logo");
	LOG_MSG('DEBUG',"do_org_save(): Got args");

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



	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar")	){
		LOG_MSG('ERROR',"do_org_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_org_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_org_insert(
								$name,
								$logo);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The org <strong>$name</strong> is already in use. Please enter a different org<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the org <strong>$name</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_org_save(): Add args failed!");
				return;
			}
			$org_id=$ROW['INSERT_ID'];
			$_GET['org_id']=$org_id;

			// Create the default directories for the organization
			recurse_copy_dir(IMG_DIR."default",IMG_DIR."org/$org_id");

			add_msg("SUCCESS","New org added successfully");
			break;
		case "UPDATE":
			// Validate org_id
			if (
				!validate("org Id",$org_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_org_save(): Failed to validate PK");
				return;
			}

			$ROW=db_org_update(
								$org_id,
								$name,
								$logo);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the org <strong>$org_id</strong> .");
				return;
			}
			add_msg("SUCCESS","org updated successfully");
			break;
	}

	// Upload the image after inserting as we need the org_id
	if ( $_FILES['logo']['name'] != '' && !upload_image("logo",IMG_DIR."org/$org_id/$logo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}

	if ( !is_superuser() ) {
		$GO='modify';
		$_GET['org_id']=$org_id;
		return;
	} else {
		// on success show the list
		$GO='list';
	}

	LOG_MSG('INFO',"do_org_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_org_delete_json() {


	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_org_delete_json(): START POST=".print_r($_POST,true));

	$org_id=get_arg($_POST,"id");

	// Validate org_id
	if ( !validate("org Id",$org_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	db_transaction_start();

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_org_delete($org_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the org';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_org_delete_json(): Delete row failed");
		exit;
	}

	// Since the user table is not a child for the org, we have to manually delete the users 
	$ROW=db_user_delete('',$org_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the user';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_org_delete_json(): Delete row failed");
		exit;
	}

	// Remove directories for the organization
	recurse_remove_dir(IMG_DIR."org/$org_id");

	db_transaction_end();

	$json_response['status']='OK';
	$json_response['id']=$org_id;
	//Send json response to response handler 
	echo json_encode($json_response);

	LOG_MSG('INFO',"do_org_delete_json(): org <strong>$org_id</strong> has been removed.");
	LOG_MSG('INFO',"do_org_delete_json(): END");

	exit;
}


?>
