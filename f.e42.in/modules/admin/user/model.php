<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_user_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_user_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$email_id=get_arg($_GET,"email_id");
	$password=get_arg($_GET,"password");
	$phone_no=get_arg($_GET,"phone_no");
	$address=get_arg($_GET,"address");
	$type=get_arg($_GET,"type");
	$is_active=get_arg($_GET,"is_active");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_user_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Email Id",$email_id,0,100,"varchar") ||
		!validate("Password",$password,0,45,"varchar") ||
		!validate("Phone No",$phone_no,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("Type",$type,0,10,"varchar") ||
		!validate("Is Active",$is_active,0,1,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_user_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_user_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&email_id=$email_id&password=$password&phone_no=$phone_no&address=$address&type=$type&is_active=$is_active&created_dt=$created_dt	";


	$ROW=db_user_select(
		"",
			$name,
			$email_id,
			$password,
			$phone_no,
			$address,
			$type,
			$is_active,
			$created_dt	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Users. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=user&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Users found! <br />Click on <strong>Add User</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_user_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_user_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_user_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the User ID
		$user_id=get_arg($_GET,"user_id");

		// Validate the ID
		if ( !validate("User Id",$user_id,1,11,"int") ) { 
			LOG_MSG('ERROR',"go_user_view(): Invalid User ID [$user_id]!");
			return;
		}

		// Get from DB
		$ROW=db_user_select($user_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the User. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Users found! <br />Click on <strong>Add User</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_user_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_user_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_user_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$user_id=get_arg($_POST,"user_id");
	$name=get_arg($_POST,"name");
	$email_id=get_arg($_POST,"email_id");
	$password=get_arg($_POST,"password");
	$phone_no=get_arg($_POST,"phone_no");
	$address=get_arg($_POST,"address");
	$type="ADMIN"; // Only Admin users can be added. Supervisor users are added in the Supervisor section
	$is_active=get_arg($_POST,"is_active");
	LOG_MSG('DEBUG',"do_user_save(): Got args");


	// Validate parameters
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Email Id",$email_id,0,100,"varchar") ||
		!validate("Password",$password,0,45,"varchar") ||
		!validate("Phone No",$phone_no,0,18,"bigint") ||
		!validate("Address",$address,0,500,"varchar") ||
		!validate("Type",$type,1,10,"varchar") ||
		!validate("Is Active",$is_active,0,1,"tinyint") ) {
		LOG_MSG('ERROR',"do_user_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_user_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_user_insert(
								$name,
								$email_id,
								$password,
								$phone_no,
								$address,
								$type,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The User <strong>$user_id</strong> is already in use. Please enter a different User<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the User <strong>$user_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_user_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New User <strong>$user_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate user_id
			if (
				!validate("User Id",$user_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_user_save(): Failed to validate PK");
				return;
			}

			$ROW=db_user_update(
								$user_id,
								$name,
								$email_id,
								$password,
								$phone_no,
								$address,
								$type,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the User <strong>$user_id</strong> .");
				return;
			}
			add_msg("SUCCESS","User <strong>$user_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_user_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_user_delete() {

	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_user_delete(): START POST=".print_r($_POST,true));

	$user_id=get_arg($_POST,"user_id");

	// Validate user_id
	if (
		!validate("User Id",$user_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_user_delete($user_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The User <strong>$user_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the User <strong>$user_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","User <strong>$user_id</strong> has been removed.");
	LOG_MSG('INFO',"do_user_delete(): END");

}





?>
