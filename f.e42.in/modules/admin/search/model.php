<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_search_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_search_list(): START GET=".print_r($_GET,true));

	// Do we have a search string?
	// Get all the args from $_GET
	$reg_no=get_arg($_GET,"reg_no");
	$imei=get_arg($_GET,"imei");
	$vehicle_model=get_arg($_GET,"vehicle_model");
	$vehicle_status=get_arg($_GET,"vehicle_status");
	$description=get_arg($_GET,"description");
	$driver_name=get_arg($_GET,"driver_name");
	$driver_phone_no=get_arg($_GET,"driver_phone_no");
	$owner_ph_no=get_arg($_GET,"owner_ph_no");
	$driver_sal=get_arg($_GET,"driver_sal");
	$cleaner_name=get_arg($_GET,"cleaner_name");
	$cleaner_salary=get_arg($_GET,"cleaner_salary");
	$supervisor_name=get_arg($_GET,"supervisor_name");
	$supervisor_phone_no=get_arg($_GET,"supervisor_phone_no");
	$client_name=get_arg($_GET,"client_name");
	$client_mobile=get_arg($_GET,"client_mobile");
	$daily_fuel_lmt=get_arg($_GET,"daily_fuel_lmt");
	$monthly_fuel_lmt=get_arg($_GET,"monthly_fuel_lmt");
	$filling_station=get_arg($_GET,"filling_station");
	$fuel_rate=get_arg($_GET,"fuel_rate");
	$fuel_filled=get_arg($_GET,"fuel_filled");
	$odometer_reading=get_arg($_GET,"odometer_reading");
	$fuel_image=get_arg($_GET,"fuel_image");
	$odometer_image=get_arg($_GET,"odometer_image");
	$accountability_date=get_arg($_GET,"accountability_date");
	$created_dt=get_arg($_GET,"created_dt");
	LOG_MSG('DEBUG',"do_search_list(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Vehicle No",$reg_no,0,20,"varchar") ||
		!validate("Vehicle Status",$vehicle_status,0,11,"varchar") ||
		!validate("Driver Name",$driver_name,0,200,"varchar") ||
		!validate("Daily Fuel Lmt",$daily_fuel_lmt,0,10,"varchar") ||
		!validate("Monthly Fuel Lmt",$monthly_fuel_lmt,0,10,"varchar") ||
		!validate("Fuel Filled",$fuel_filled,0,10,"varchar") ||
		!validate("Odometer Reading",$odometer_reading,0,45,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_search_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_search_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="reg_no=$reg_no&vehicle_status=$vehicle_status&driver_name=$driver_name&daily_fuel_lmt=$daily_fuel_lmt&monthly_fuel_lmt=$monthly_fuel_lmt&fuel_filled=$fuel_filled&odometer_reading=$odometer_reading&created_dt=$created_dt";

	$ROW=db_search_select(
		"",
			$reg_no,
			$imei,
			$vehicle_model,
			$vehicle_status,
			$description,
			$driver_name,
			$driver_phone_no,
			$owner_ph_no,
			$driver_sal,
			$cleaner_name,
			$cleaner_salary,
			$supervisor_name,
			$supervisor_phone_no,
			$client_name,
			$client_mobile,
			$daily_fuel_lmt,
			$monthly_fuel_lmt,
			$filling_station,
			$fuel_rate,
			$fuel_filled,
			$odometer_reading,
			$fuel_image,
			$odometer_image,
			$accountability_date,
			$created_dt	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Searchs. Please try again later. <br/>");
		return;
	}

	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=search&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');

	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No cleaners found! <br />Click on <strong>Add cleaner</strong> to create a one.<br />"); 
	}

	// To get driver details
	if ( ($row_driver=db_get_list('ARRAY','name,driver_id','tDriver','travel_id='.TRAVEL_ID)) === false ) return;

	// To get supervisor details
	if ( ($row_supervisor=db_get_list('ARRAY','name,supervisor_id','tSupervisor','travel_id='.TRAVEL_ID)) === false ) return;

	// To get client details
	if ( ($row_client=db_get_list('ARRAY','name,client_id','tClient','travel_id='.TRAVEL_ID)) === false ) return;

	// To get cleaner details
	if ( ($row_cleaner=db_get_list('ARRAY','name,cleaner_id','tCleaner','travel_id='.TRAVEL_ID)) === false ) return;

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_search_list(): END");
}

/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_search_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_search_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Search ID
		$search_id=get_arg($_GET,"search_id");

		// Validate the ID
		if (
			!validate("Search Id",$search_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_search_view(): Invalid Search ID [$search_id]!");
			return;
		}

		// Get from DB
		$ROW=db_search_select($search_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Search. Please try again later. <br/>");
			return;
		}

		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Searchs found! <br />Click on <strong>Add Search</strong> to create a one.<br /><br />"); 
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

	// To get driver details
        if ( ($row_driver=db_get_list('ARRAY','name,driver_id','tDriver','travel_id='.TRAVEL_ID)) === false ) return;

        // To get supervisor details
        if ( ($row_supervisor=db_get_list('ARRAY','name,supervisor_id','tSupervisor','travel_id='.TRAVEL_ID)) === false ) return;

        // To get client details
        if ( ($row_client=db_get_list('ARRAY','name,client_id','tClient','travel_id='.TRAVEL_ID)) === false ) return;

        // To get cleaner details
        if ( ($row_cleaner=db_get_list('ARRAY','name,cleaner_id','tCleaner','travel_id='.TRAVEL_ID)) === false ) return;

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_search_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_search_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_search_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$search_id=get_arg($_POST,"search_id");
	$reg_no=get_arg($_POST,"reg_no");
	$imei=get_arg($_POST,"imei");
	$vehicle_model=get_arg($_POST,"vehicle_model");
	$vehicle_status=get_arg($_POST,"vehicle_status");
	$description=get_arg($_POST,"description");
	$driver_name=get_arg($_POST,"driver_name");
	$driver_phone_no=get_arg($_POST,"driver_phone_no");
	$owner_ph_no=get_arg($_POST,"owner_ph_no");
	$driver_sal=get_arg($_POST,"driver_sal");
	$cleaner_name=get_arg($_POST,"cleaner_name");
	$cleaner_salary=get_arg($_POST,"cleaner_salary");
	$supervisor_name=get_arg($_POST,"supervisor_name");
	$supervisor_phone_no=get_arg($_POST,"supervisor_phone_no");
	$client_name=get_arg($_POST,"client_name");
	$client_mobile=get_arg($_POST,"client_mobile");
	$daily_fuel_lmt=get_arg($_POST,"daily_fuel_lmt");
	$monthly_fuel_lmt=get_arg($_POST,"monthly_fuel_lmt");
	$filling_station=get_arg($_POST,"filling_station");
	$fuel_rate=get_arg($_POST,"fuel_rate");
	$fuel_filled=get_arg($_POST,"fuel_filled");
	$odometer_reading=get_arg($_POST,"odometer_reading");
	$fuel_image=get_arg($_POST,"fuel_image");
	$odometer_image=get_arg($_POST,"odometer_image");
	$accountability_date=get_arg($_POST,"accountability_date");
	$created_dt=get_arg($_POST,"created_dt");
	LOG_MSG('DEBUG',"do_search_save(): Got args");

	// Validate parameters
	if (
		!validate("Vehicle No",$reg_no,0,20,"varchar") ||
		!validate("Vehicle Status",$vehicle_status,0,11,"tinyint") ||
		!validate("Driver Name",$driver_name,0,200,"varchar") ||
		!validate("Daily Fuel Lmt",$daily_fuel_lmt,0,10,"decimal") ||
		!validate("Monthly Fuel Lmt",$monthly_fuel_lmt,0,10,"decimal") ||
		!validate("Fuel Filled",$fuel_filled,0,10,"decimal") ||
		!validate("Odometer Reading",$odometer_reading,0,45,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"datetime") 	){
		LOG_MSG('ERROR',"do_search_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_search_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_search_insert(
								$reg_no,
								$imei,
								$vehicle_model,
								$vehicle_status,
								$description,
								$driver_name,
								$driver_phone_no,
								$owner_ph_no,
								$driver_sal,
								$cleaner_name,
								$cleaner_salary,
								$supervisor_name,
								$supervisor_phone_no,
								$client_name,
								$client_mobile,
								$daily_fuel_lmt,
								$monthly_fuel_lmt,
								$filling_station,
								$fuel_rate,
								$fuel_filled,
								$odometer_reading,
								$fuel_image,
								$odometer_image,
								$accountability_date,
								$created_dt);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Search <strong>$search_id</strong> is already in use. Please enter a different Search<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Search <strong>$search_id</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_search_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Search <strong>$search_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate search_id
			if (
				!validate("Search Id",$search_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_search_save(): Failed to validate PK");
				return;
			}

			$ROW=db_search_update(
								$search_id,
								$reg_no,
								$imei,
								$vehicle_model,
								$vehicle_status,
								$description,
								$driver_name,
								$driver_phone_no,
								$owner_ph_no,
								$driver_sal,
								$cleaner_name,
								$cleaner_salary,
								$supervisor_name,
								$supervisor_phone_no,
								$client_name,
								$client_mobile,
								$daily_fuel_lmt,
								$monthly_fuel_lmt,
								$filling_station,
								$fuel_rate,
								$fuel_filled,
								$odometer_reading,
								$fuel_image,
								$odometer_image,
								$accountability_date,
								$created_dt					);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Search <strong>$search_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Search <strong>$search_id</strong> updated successfully");
			break;
	}
	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_search_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_search_delete() {

	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_search_delete(): START POST=".print_r($_POST,true));

	$search_id=get_arg($_POST,"search_id");
	$search_id=get_arg($_POST,"search_id");

	// Validate search_id
	if (
		!validate("Search Id",$search_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_search_delete($search_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Search <strong>$search_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Search <strong>$search_id</strong>");
		}
		return;
	}

	add_msg("SUCCESS","Search <strong>$search_id</strong> has been removed.");
	LOG_MSG('INFO',"do_search_delete(): END");

}

?>
