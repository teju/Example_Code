<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_driver_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_driver_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$photo=get_arg($_GET,"photo");
	$phone_no=get_arg($_GET,"phone_no");
	$owner_ph_no=get_arg($_GET,"owner_ph_no");
	$address=get_arg($_GET,"address");
	$license_no=get_arg($_GET,"license_no");
	$license_exp_dt=get_arg($_GET,"license_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"license_exp_dt"))) : get_arg($_GET,"license_exp_dt");
	$salary=get_arg($_GET,"salary");
	$start_dt=get_arg($_GET,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"start_dt"))) : get_arg($_GET,"start_dt");
	$end_dt=get_arg($_GET,"end_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"end_dt"))) : get_arg($_GET,"end_dt");
	$is_active=get_arg($_GET,"is_active");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_driver_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$phone_no,0,18,"varchar") ||
		!validate("Owner Phone No",$owner_ph_no,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("License No",$license_no,0,45,"varchar") ||
		!validate("License Exp Dt",$license_exp_dt,0,10,"varchar") ||
		!validate("Salary",$salary,0,10,"decimal") ||
		!validate("Start Dt",$start_dt,0,30,"varchar") ||
		!validate("End Dt",$end_dt,0,30,"varchar") ||
		!validate("Is Active",$is_active,0,1,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_driver_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_driver_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&photo=$photo&phone_no=$phone_no&owner_ph_no=$owner_ph_no&address=$address&license_no=$license_no&license_exp_dt=$license_exp_dt&salary=$salary&start_dt=$start_dt&end_dt=$end_dt&is_active=$is_active&created_dt=$created_dt";



	$ROW=db_driver_select(
		"",
			$name,
			$photo,
			$phone_no,
			$owner_ph_no,
			$address,
			$license_no,
			$license_exp_dt,
			$salary,
			$start_dt,
			$end_dt,
			$is_active,
			$created_dt);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Drivers. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=driver&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Drivers found! <br />Click on <strong>Add Driver</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_driver_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_driver_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_driver_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Driver ID
		$driver_id=get_arg($_GET,"driver_id");

		// Validate the ID
		if (
			!validate("Driver Id",$driver_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_driver_view(): Invalid Driver ID [$driver_id]!");
			return;
		}

		// Get from DB
		$ROW=db_driver_select($driver_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Driver. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Drivers found! <br />Click on <strong>Add Driver</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_driver_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_driver_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_driver_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$driver_id=get_arg($_POST,"driver_id");
	$name=get_arg($_POST,"name");
	$photo=get_arg($_POST,"photo");
	$phone_no=get_arg($_POST,"phone_no");
	$owner_ph_no=get_arg($_POST,"owner_ph_no");
	$address=get_arg($_POST,"address");
	$license_no=get_arg($_POST,"license_no");
	$license_exp_dt=get_arg($_POST,"license_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"license_exp_dt"))) : get_arg($_POST,"license_exp_dt");
	$salary=get_arg($_POST,"salary");
	$start_dt=get_arg($_POST,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"start_dt"))) : get_arg($_POST,"start_dt");
	$end_dt=get_arg($_POST,"end_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"end_dt"))) : get_arg($_POST,"end_dt");
	$is_active=get_arg($_POST,"is_active");
	LOG_MSG('DEBUG',"do_driver_save(): Got args");


	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$phone_no,0,18,"bigint") ||
		!validate("Owner Phone No",$owner_ph_no,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("License No",$license_no,1,45,"varchar") ||
		!validate("License Exp Dt",$license_exp_dt,1,10,"date") ||
		!validate("Start Dt",$start_dt,0,30,"varchar") ||
		!validate("End Dt",$end_dt,0,30,"varchar") ||
		!validate("Is Active",$is_active,1,1,"tinyint") 	){
		LOG_MSG('ERROR',"do_driver_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_driver_save(): Validated args");

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
			$ROW=db_driver_insert(
								$name,
								$photo,
								$phone_no,
								$owner_ph_no,
								$address,
								$license_no,
								$license_exp_dt,
								$salary,
								$start_dt,
								$end_dt,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Driver <strong>$driver_id</strong> is already in use. Please enter a different Driver<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Driver <strong>$driver_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_driver_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Driver <strong>$driver_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate driver_id
			if (
				!validate("Driver Id",$driver_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_driver_save(): Failed to validate PK");
				return;
			}

			$ROW=db_driver_update(
								$driver_id,
								$name,
								$photo,
								$phone_no,
								$owner_ph_no,
								$address,
								$license_no,
								$license_exp_dt,
								$salary,
								$start_dt,
								$end_dt,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Driver <strong>$driver_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Driver <strong>$driver_id</strong> updated successfully");
			break;
	}

	// Upload the image after inserting as we need the org_id
	if ( $_FILES['photo']['name'] != '' && !upload_image("photo",IMG_DIR."driver/$photo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_driver_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_driver_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_driver_delete(): START POST=".print_r($_POST,true));

	$driver_id=get_arg($_POST,"driver_id");

	// Validate driver_id
	if (
		!validate("Driver Id",$driver_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_driver_delete($driver_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Driver <strong>$driver_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Driver <strong>$driver_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","Driver <strong>$driver_id</strong> has been removed.");
	LOG_MSG('INFO',"do_driver_delete(): END");

}





?>
