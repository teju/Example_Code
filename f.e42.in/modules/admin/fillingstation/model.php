<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_filling_station_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_filling_station_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$imei=get_arg($_GET,"imei");
	$name=get_arg($_GET,"name");
	$location=get_arg($_GET,"location");
	$fuel_rate=get_arg($_GET,"fuel_rate");
	LOG_MSG('DEBUG',"go_filling_station_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("IMEI",$imei,0,200,"varchar") ||
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Location",$location,0,18,"varchar") ||
		!validate("Fuel Rate",$fuel_rate,0,500,"varchar")){
		LOG_MSG('ERROR',"go_filling_station_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"go_filling_station_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="imei=$imei&name=$name&location=$location&fuel_rate=$fuel_rate";
	


	$ROW=db_filling_station_select(
		"",
			$imei,
			$name,
			$location,
			$fuel_rate);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the filling_station. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=fillingstation&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No filling station found! <br />Click on <strong>Add filling_station</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_filling_station_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_filling_station_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_filling_station_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the filling_station ID
		$fs_id=get_arg($_GET,"fs_id");

		// Validate the ID
		if (
			!validate("filling_station Id",$fs_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_filling_station_view(): Invalid filling_station ID [$fs_id]!");
			return;
		}

		// Get from DB
		$ROW=db_filling_station_select($fs_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the filling_station. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No filling_station found! <br />Click on <strong>Add filling_station</strong> to create a one.<br /><br />"); 
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

	LOG_MSG('INFO',"go_filling_station_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_filling_station_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_filling_station_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$imei=get_arg($_POST,"imei");
	$name=get_arg($_POST,"name");
	$location=get_arg($_POST,"location");
	$fuel_rate=get_arg($_POST,"fuel_rate");
	LOG_MSG('DEBUG',"do_filling_station_save(): Got args");



	// Validate parameters
	if (
		!validate("IMEI",$imei,0,200,"varchar") ||
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Location",$location,0,18,"varchar") ||
		!validate("Fuel Rate",$fuel_rate,0,500,"varchar")){
		LOG_MSG('ERROR',"do_filling_station_save(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_filling_station_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_filling_station_insert(
								$imei,
								$name,
								$location,
								$fuel_rate);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The filling_station <strong>$name</strong> is already in use. Please enter a different filling_station<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the filling_station <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_filling_station_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New filling_station <strong>$name</strong> added successfully");
			break;
		case "UPDATE":
				$fs_id=get_arg($_POST,"fs_id");

			// Validate fs_id
			if (
				!validate("filling_station Id",$fs_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_filling_station_save(): Failed to validate PK");
				return;
			}

			$ROW=db_filling_station_update(
								$fs_id,
								$imei,
								$name,
								$location,
								$fuel_rate);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the filling_station <strong>$fs_id</strong> .");
				return;
			}
			add_msg("SUCCESS","filling_station <strong>$fs_id</strong> updated successfully");
			break;
	}

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_filling_station_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_filling_station_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_filling_station_delete(): START POST=".print_r($_POST,true));

	$fs_id=get_arg($_POST,"fs_id");

	// Validate fs_id
	if (
		!validate("filling_station Id",$fs_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_filling_station_delete($fs_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The filling_station <strong>$fs_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the filling_station <strong>$fs_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","filling_station <strong>$fs_id</strong> has been removed.");
	LOG_MSG('INFO',"do_filling_station_delete(): END");

}

?>
