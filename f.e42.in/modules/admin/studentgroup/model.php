<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_student_group_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_student_group_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$group_name=get_arg($_GET,"group_name");
	$is_active=get_arg($_GET,"is_active");
	LOG_MSG('DEBUG',"do_student_group_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Group Name",$group_name,0,200,"varchar") ||
		!validate("Is Active",$is_active,0,1,"tinyint") ){
		LOG_MSG('ERROR',"do_student_group_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_student_group_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="group_name=$group_name&is_active=$is_active";

	$ROW=db_student_group_select(
		"",
			$group_name,
			$is_active );
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the student_group. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=studentgroup&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No student_groups found! <br />Click on <strong>Add student_group</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_student_group_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_student_group_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_student_group_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the student_group ID
		$group_id=get_arg($_GET,"group_id");

		// Validate the ID
		if (
			!validate("Group Id",$group_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_student_group_view(): Invalid student_group ID [$group_id]!");
			return;
		}

		// Get from DB
		$ROW=db_student_group_select($group_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the student_group. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No student_group found! <br />Click on <strong>Add student_group</strong> to create a one.<br /><br />"); 
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
	// To get location details
	if ( $mode != "ADD" ) {
		$location_group_row = db_location_group_select($group_id);
		if( $location_group_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows found for id number ");
			$json['message']="Invalid Card";
			echo json_encode($json);
			exit;
		}
	}
    if ( ($row_location=db_get_list('ARRAY','location_name,location_id','tLocation','travel_id='.TRAVEL_ID)) === false ) return;

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_student_group_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_student_group_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_student_group_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$group_name=get_arg($_POST,"group_name");
	$is_active=get_arg($_POST,"is_active");

	LOG_MSG('DEBUG',"do_student_group_save(): Got args");

	// Validate parameters
	if (
		!validate("Group Name",$group_name,1,200,"varchar") ||
		!validate("Is active",$is_active,0,1,"tinyint")){
		LOG_MSG('ERROR',"do_student_group_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_student_group_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_student_group_insert(
								$group_name,
								$is_active);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The student_group <strong>$group_name</strong> is already in use. Please enter a different student_group<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the student_group <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_student_group_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New student_group <strong>$group_name</strong> added successfully");
			if ( $ROW['NROWS'] == 1 && !app_sync('tGroup',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
				return;
			}
			break;
		case "UPDATE":
				$group_id=get_arg($_POST,"group_id");

			// Validate group_id
			if (
				!validate("cleaner Id",$group_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_student_group_save(): Failed to validate PK");
				return;
			}

			$ROW=db_student_group_update(
								$group_id,
								$group_name,
								$is_active);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the student_group <strong>$group_id</strong> .");
				return;
			}
			add_msg("SUCCESS","student_group <strong>$group_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tGroup',$group_id,'U',TRAVEL_ID) ) {
				return;
			}
			break;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_student_group_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_student_group_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_student_group_delete(): START POST=".print_r($_POST,true));

	$group_id=get_arg($_POST,"group_id");

	// Validate group_id
	if (
		!validate("Group Id",$group_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_student_group_delete($group_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The student_group <strong>$group_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the student_group <strong>$group_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","student_group <strong>$group_id</strong> has been removed.");
	if( $ROW['NROWS'] == 1 && !app_sync('tGroup',$group_id,'D',TRAVEL_ID)) {
		 return;
	}
	LOG_MSG('INFO',"do_student_group_delete(): END");

}

function do_group_location_delete() {

	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"do_group_location_delete(): START POST=".print_r($_POST,true));

	$location_id=get_arg($_POST,"location_id");

	// Validate location_id
	if (
		!validate("location_id",$location_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_group_location_delete($location_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_location_imei_save(): Error while deleting the row");
		$json['message']="Error while deleting. Please contact customer care";
		echo json_encode($json);
		exit;
	}
	if ( $ROW['NROWS'] == 1 && !app_sync('tStLocGroup',$location_id,'D',TRAVEL_ID) ) {
		return;
	}
	$json['status']="OK";
	$json['message']="successfully deleted";
	echo json_encode($json);
	exit;	LOG_MSG('INFO',"do_group_location_delete(): END");

}
function do_group_location_save() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW, $ERROR_MESSAGE;
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"do_group_location_save(): START POST=".print_r($_POST,true));

	$group_id=get_arg($_POST,"group_id");
	$location_id=get_arg($_POST,"location_id");

	// Validate group_id
	if ( !validate("group Id",$group_id,1,11,"int") ||
		 !validate("Location Id",$location_id,0,11,"int") ) {
		LOG_MSG('DEBUG',"go_student_login(): VALIDATE ARGS FAILED");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	$ROW=db_group_location_insert($group_id,$location_id);
	if ( $ROW['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_group_location_save(): Error while inserting the new row");
		$json['message']="Error while inserting. Please contact customer care";
		echo json_encode($json);
		exit;
	}
	if ( $ROW['NROWS'] == 1 && !app_sync('tStLocGroup',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
		return;
	}
	$location_group_row = db_location_group_select($group_id,$location_id);
	if( $location_group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows found for id number ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	if( $location_group_row[0]['NROWS'] > 0 ) {
		$json['location_name']=$location_group_row[0]['location_name'];
	}

	$json['status']="OK";
	$json['message']="success";
	$json['location_id']=$location_id;
	echo json_encode($json);
	LOG_MSG('INFO',"do_group_location_save() : Json response ".print_r($json,true));
	LOG_MSG('INFO',"do_group_location_save(): END");
	exit;

}
?>
