<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_supervisor_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_supervisor_list(): START GET=".print_r($_GET,true));

	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$photo=get_arg($_GET,"photo");
	$phone_no=get_arg($_GET,"phone_no");
	$address=get_arg($_GET,"address");
	$imei=get_arg($_GET,"imei");
	$start_dt=get_arg($_GET,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"start_dt"))) : get_arg($_GET,"start_dt");
	$end_dt=get_arg($_GET,"end_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"end_dt"))) : get_arg($_GET,"end_dt");
	$is_active=get_arg($_GET,"is_active");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_supervisor_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$phone_no,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("Imei",$imei,0,45,"varchar") ||
		!validate("Start Dt",$start_dt,0,30,"date") ||
		!validate("End Dt",$end_dt,0,30,"date") ||
		!validate("Is Active",$is_active,0,1,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_supervisor_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_supervisor_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&photo=$photo&phone_no=$phone_no&address=$address&imei=$imei&start_dt=$start_dt&end_dt=$end_dt&is_active=$is_active&created_dt=$created_dt";


	$ROW=db_supervisor_select(
		"",
			$name,
			$photo,
			$phone_no,
			$address,
			$imei,
			$start_dt,
			$end_dt,
			$is_active,
			$created_dt);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Supervisors. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=supervisor&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Supervisors found! <br />Click on <strong>Add Supervisor</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_supervisor_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_supervisor_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_supervisor_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Supervisor ID
		$supervisor_id=get_arg($_GET,"supervisor_id");

		// Validate the ID
		if (
			!validate("Supervisor Id",$supervisor_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_supervisor_view(): Invalid Supervisor ID [$supervisor_id]!");
			return;
		}

		// Get from DB
		$ROW=db_supervisor_select($supervisor_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Supervisor. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Supervisors found! <br />Click on <strong>Add Supervisor</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_supervisor_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_supervisor_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_supervisor_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$email_id=get_arg($_POST,"email_id");
	$password=get_arg($_POST,"password");
	$supervisor_id=get_arg($_POST,"supervisor_id");
	$name=get_arg($_POST,"name");
	$photo=get_arg($_POST,"photo");
	$phone_no=get_arg($_POST,"phone_no");
	$address=get_arg($_POST,"address");
	$imei=get_arg($_POST,"imei");
	$start_dt=get_arg($_POST,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"start_dt"))) : get_arg($_POST,"start_dt");
	$end_dt=get_arg($_POST,"end_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"end_dt"))) : get_arg($_POST,"end_dt");
	$is_active=get_arg($_POST,"is_active");
	LOG_MSG('DEBUG',"do_supervisor_save(): Got args");


	// Validate parameters
	if (
		( !validate("Email Id",$email_id,0,100,"varchar")) ||
		( $password != '' && !validate("Password",$password,5,100,"varchar")) ||
		!validate("Name",$name,1,200,"varchar") ||
		!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$phone_no,0,18,"bigint") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("Imei",$imei,1,45,"varchar") ||
		!validate("Start Dt",$start_dt,0,30,"varchar") ||
		!validate("End Dt",$end_dt,0,30,"varchar") ||
		!validate("Is Active",$is_active,1,1,"tinyint") ){
		LOG_MSG('ERROR',"do_supervisor_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_supervisor_save(): Validated args");

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

	db_transaction_start();

	$user_id='';
	if ( $email_id != '' ) {
		// As we need to consider even Supervisor as a user if needed, we have to add Supervisor as a user
		// Get user_id
		if ( ($user_id=db_get_list('LIST','user_id','tUser',"email_id='$email_id' AND travel_id=".TRAVEL_ID)) === false ) return;

		// No user found THEN Create new user for the supplier with supplier permissions
		if ( $user_id == '' ) {
				// Insert Supplier user in tUser
				$user_resp=db_user_insert(
											$name,
											$email_id,
											$password,
											$phone_no,
											$address,
											"SUPERVISOR",
											$is_active);
			if ( $user_resp['STATUS'] != "OK" ) {
					switch ($user_resp["SQL_ERROR_CODE"]) {
							case 1062: // unique key
									add_msg("ERROR","The Email ID <strong>$email_id</strong> is already in use. Please enter a different Email ID");
									break;
							default:
									add_msg("ERROR","There was an error error creating user for the Supplier <strong>$name</strong>.");
									break;
					}
					LOG_MSG('ERROR',"do_supplier_save(): Add row failed");
					return;
			}
			$user_id=get_arg($user_resp,'INSERT_ID');
		}
	}

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_supervisor_insert(
								$user_id,
								$name,
								$photo,
								$phone_no,
								$address,
								$imei,
								$start_dt,
								$end_dt,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Supervisor <strong>$supervisor_id</strong> is already in use. Please enter a different Supervisor<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Supervisor <strong>$supervisor_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_supervisor_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Supervisor <strong>$supervisor_id</strong> added successfully");
			if ( $ROW['NROWS'] == 1 && !app_sync('tSupervisor',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
				return;
			}
			break;
		case "UPDATE":
			// Validate supervisor_id
			if (
				!validate("Supervisor Id",$supervisor_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_supervisor_save(): Failed to validate PK");
				return;
			}

			$ROW=db_supervisor_update(
								$supervisor_id,
								$user_id,
								$name,
								$photo,
								$phone_no,
								$address,
								$imei,
								$start_dt,
								$end_dt,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Supervisor <strong>$supervisor_id</strong> .");
				return;
			}

			// Update the user data if available
			if ( $user_id != '' ) {
				$ROW=db_user_update(
									$user_id,
									$name,
									$email_id,
									$password,
									$phone_no,
									$address,
									"SUPERVISOR",
									$is_active						);
				if ( $ROW['STATUS'] != "OK" ) {
					add_msg("ERROR","There was an error updating the User <strong>$user_id</strong> .");
					return;
				}
			}

			add_msg("SUCCESS","Supervisor <strong>$supervisor_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tSupervisor',$supervisor_id,'U',TRAVEL_ID)) {
				return;
			}
			break;
	}

	db_transaction_end();

	// Upload the image after inserting as we need the org_id
	if ( $_FILES['photo']['name'] != '' && !upload_image("photo",IMG_DIR."supervisor/$photo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_supervisor_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_supervisor_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_supervisor_delete(): START POST=".print_r($_POST,true));

	$supervisor_id=get_arg($_POST,"supervisor_id");

	// Validate supervisor_id
	if (
		!validate("Supervisor Id",$supervisor_id,1,11,"int")
	) { return; }

	db_transaction_start();
	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_supervisor_delete($supervisor_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Supervisor <strong>$supervisor_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Supervisor <strong>$supervisor_id</strong>");
		}
		return;
	}
	if( $ROW['NROWS'] == 1 && !app_sync('tSupervisor',$supervisor_id,'D',TRAVEL_ID)) {
		 return;
	}
	db_transaction_end();

	add_msg("SUCCESS","Supervisor <strong>$supervisor_id</strong> has been removed.");
	LOG_MSG('INFO',"do_supervisor_delete(): END");

}





?>
