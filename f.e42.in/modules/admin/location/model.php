<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_location_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_location_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$location_name=get_arg($_GET,"location_name");
	LOG_MSG('DEBUG',"do_location_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Location Name",$location_name,0,200,"varchar")  ){
		LOG_MSG('ERROR',"do_location_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_location_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="location_name=$location_name";

	$ROW=db_location_select(
		"",
			$location_name);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the location. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=location&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No locations found! <br />Click on <strong>Add location</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_location_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_location_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_location_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the location ID
		$location_id=get_arg($_GET,"location_id");
		$imei=get_arg($_GET,"imei");

		// Validate the ID
		if (
			!validate("location Id",$location_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_location_view(): Invalid location ID [$location_id]!");
			return;
		}

		// Get from DB
		$ROW=db_location_select($location_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the location. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No location found! <br />Click on <strong>Add location</strong> to create a one.<br /><br />"); 
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
		case "DELETE IMEI":
				$_do="remove_imei";
				$disabled="disabled";
				break;
		case "VIEW":
		default:
				$disabled="disabled";
				break;
	}
	// To get location details
	if ( $mode != "ADD" ) {
		$imei_location_row = db_imei_location_select($location_id);
		if( $imei_location_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows found for id number ");
			$json['message']="Invalid Card";
			echo json_encode($json);
			exit;
		}
	}
	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_location_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_location_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_location_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_location_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$location_name=get_arg($_POST,"location_name");
	LOG_MSG('DEBUG',"do_location_save(): Got args");



	// Validate parameters
	if (
		!validate(" Location Name",$location_name,1,200,"varchar")){
		LOG_MSG('ERROR',"do_location_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_location_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_location_insert(
								$location_name);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The location <strong>$name</strong> is already in use. Please enter a different location<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the location <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_location_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New location <strong>$location_name</strong> added successfully");
			if ( $ROW['NROWS'] == 1 && !app_sync('tLocation',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
				return;
			}
			break;
		case "UPDATE":
				$location_id=get_arg($_POST,"location_id");

			// Validate location_id
			if (
				!validate("location Id",$location_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_location_save(): Failed to validate PK");
				return;
			}

			$ROW=db_location_update(
								$location_id,
								$location_name);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the location <strong>$location_id</strong> .");
				return;
			}
			add_msg("SUCCESS","location <strong>$location_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tLocation',$location_id,'U',TRAVEL_ID) ) {
				return;
			}
			break;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_location_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_location_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_location_delete(): START POST=".print_r($_POST,true));

	$location_id=get_arg($_POST,"location_id");

	// Validate location_id
	if (
		!validate("location Id",$location_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_location_delete($location_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The location <strong>$location_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the location <strong>$location_id</strong>");
		}
		return;
	}
	if( $ROW['NROWS'] == 1 && !app_sync('tLocation',$location_id,'D',TRAVEL_ID)) {
		 return;
	}
	add_msg("SUCCESS","location <strong>$location_id</strong> has been removed.");
	LOG_MSG('INFO',"do_location_delete(): END");

}

function do_location_imei_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW,$ERROR_MESSAGE;
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"do_location_imei_delete(): START POST=".print_r($_POST,true));

	$imei=get_arg($_POST,"imei");

	// Validate location_id
	if (
		!validate("Imei",$imei,1,45,"varchar")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_location_imei_delete($imei);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_location_imei_save(): Error while inserting the new row");
		$json['message']="Error while inserting. Please contact customer care";
		echo json_encode($json);
		exit;
	}
	if( $ROW['NROWS'] == 1 && !app_sync('tImeiLocation',$imei,'D',TRAVEL_ID)) {
		return;
	}
	$json['status']="OK";
	$json['message']="successfully deleted";
	echo json_encode($json);
	exit;
	LOG_MSG('INFO',"do_location_imei_delete(): END");

}

function do_location_imei_save() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW, $ERROR_MESSAGE;
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"do_location_imei_save(): START POST=".print_r($_POST,true));

	$location_id=get_arg($_POST,"location_id");
	$imei=get_arg($_POST,"imei");

	// Validate location_id
	if ( !validate("location Id",$location_id,1,11,"int") ) {
		LOG_MSG('DEBUG',"go_student_login(): VALIDATE ARGS FAILED");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;	
	}

	$ROW=db_location_imei_insert($location_id,$imei);
	if ( $ROW['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_location_imei_save(): Error while inserting the new row");
		$json['message']="Error while inserting. Please contact customer care";
		echo json_encode($json);
		exit;
	}
	if ( $ROW['NROWS'] == 1 && !app_sync('tImeiLocation',$imei,'A',TRAVEL_ID) ) {
		return;
	}
	$json['status']="OK";
	$json['message']="success";
	$json['imei']=$imei;
	echo json_encode($json);
	LOG_MSG('INFO',"do_location_imei_save() : Json response ".print_r($json,true));
	LOG_MSG('INFO',"do_location_imei_save(): END");
	exit;

}

?>
