<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_vehicle_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_vehicle_list(): START GET=".print_r($_GET,true));

	// Do we have a search string?
	// Get all the args from $_GET
	$client_name=get_arg($_GET,"client_name");
	$reg_no=get_arg($_GET,"reg_no");
	$route=get_arg($_GET,"route");
	$vehicle_model=get_arg($_GET,"vehicle_model");
	$type=get_arg($_GET,"type");
	$sticker_no=get_arg($_GET,"sticker_no");
	$rc_no=get_arg($_GET,"rc_no");
	$rc_exp_dt=get_arg($_GET,"rc_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"rc_exp_dt"))) : get_arg($_GET,"rc_exp_dt");
	$daily_fuel_lmt=get_arg($_GET,"daily_fuel_lmt");
	$monthly_fuel_lmt=get_arg($_GET,"monthly_fuel_lmt");
	$insurance_ref_no=get_arg($_GET,"insurance_ref_no");
	$insurance_exp_dt=get_arg($_GET,"insurance_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"insurance_exp_dt"))) : get_arg($_GET,"insurance_exp_dt");
	$road_tax_ref_no=get_arg($_GET,"road_tax_ref_no");
	$road_tax_exp_dt=get_arg($_GET,"road_tax_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"road_tax_exp_dt"))) : get_arg($_GET,"road_tax_exp_dt");
	$permit=get_arg($_GET,"permit");
	$permit_exp_dt=get_arg($_GET,"permit_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"permit_exp_dt"))) : get_arg($_GET,"permit_exp_dt");
	$authorization=get_arg($_GET,"authorization");
	$authorization_exp_dt=get_arg($_GET,"authorization_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"authorization_exp_dt"))) : get_arg($_GET,"authorization_exp_dt");
	$start_dt=get_arg($_GET,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"start_dt"))) : get_arg($_GET,"start_dt");
	$end_dt=get_arg($_GET,"end_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"end_dt"))) : get_arg($_GET,"end_dt");
	$driver_id=get_arg($_GET,"driver_id");
	$cleaner_id=get_arg($_GET,"cleaner_id");
	$supervisor_id=get_arg($_GET,"supervisor_id");
	$client_id=get_arg($_GET,"client_id");
	$is_active=get_arg($_GET,"is_active");
	$created_dt=get_arg($_GET,"created_dt");
	
	LOG_MSG('DEBUG',"do_vehicle_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("Reg No",$reg_no,0,100,"varchar") ||
		!validate("Vehicle Model",$vehicle_model,0,45,"varchar") ||
		!validate("Type",$type,0,10,"varchar") ||
		!validate("Sticker No",$sticker_no,0,45,"varchar") ||
		!validate("Rc No",$rc_no,0,45,"varchar") ||
		!validate("Rc Exp Dt",$rc_exp_dt,0,10,"varchar") ||
		!validate("Daily Fuel Lmt",$daily_fuel_lmt,0,10,"varchar") ||
		!validate("Monthly Fuel Lmt",$monthly_fuel_lmt,0,10,"varchar") ||
		!validate("Insurance Ref No",$insurance_ref_no,0,45,"varchar") ||
		!validate("Insurance Exp Dt",$insurance_exp_dt,0,10,"varchar") ||
		!validate("Road Tax Ref No",$road_tax_ref_no,0,45,"varchar") ||
		!validate("Road Tax Exp Dt",$road_tax_exp_dt,0,10,"varchar") ||
		!validate("permit",$permit,0,200,"varchar") ||
		!validate("permit_exp_dt",$permit_exp_dt,0,10,"varchar") ||
		!validate("authorization",$authorization,0,200,"varchar") ||
		!validate("authorization_exp_dt",$authorization_exp_dt,0,10,"varchar") ||
		!validate("Start Dt",$start_dt,0,30,"varchar") ||
		!validate("End Dt",$end_dt,0,30,"varchar") ||
		!validate("Driver Id",$driver_id,0,11,"varchar") ||
		!validate("Supervisor Id",$supervisor_id,0,11,"varchar") ||
		!validate("Client Id",$client_id,0,11,"varchar") ||
		!validate("Cleaner Id",$cleaner_id,0,11,"varchar") ||
		!validate("Is Active",$is_active,0,1,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") 	){
		LOG_MSG('ERROR',"do_vehicle_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_vehicle_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="reg_no=$reg_no&vehicle_model=$vehicle_model&type=$type&sticker_no=$sticker_no&rc_no=$rc_no&rc_exp_dt=$rc_exp_dt&daily_fuel_lmt=$daily_fuel_lmt&monthly_fuel_lmt=$monthly_fuel_lmt&insurance_ref_no=$insurance_ref_no&insurance_exp_dt=$insurance_exp_dt&road_tax_ref_no=$road_tax_ref_no&road_tax_exp_dt=$road_tax_exp_dt&permit=$permit&permit_exp_dt=$permit_exp_dt&authorization=$authorization&authorization_exp_dt=$authorization_exp_dt&start_dt=$start_dt&end_dt=$end_dt&driver_id=$driver_id&cleaner_id=$cleaner_id&supervisor_id=$supervisor_id&client_id=$client_id&is_active=$is_active&created_dt=$created_dt	";


	$ROW=db_vehicle_select(
		"",
			$reg_no,
			$route,
			$vehicle_model,
			$type,
			$sticker_no,
			$rc_no,
			$rc_exp_dt,
			$daily_fuel_lmt,
			$monthly_fuel_lmt,
			$insurance_ref_no,
			$insurance_exp_dt,
			$road_tax_ref_no,
			$road_tax_exp_dt,
			$permit,
			$permit_exp_dt,
			$authorization,
			$authorization_exp_dt,
			$start_dt,
			$end_dt,
			$driver_id,
			$cleaner_id,
			$supervisor_id,
			$client_id,
			$is_active,
			$created_dt,
			$client_name);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Vehicles. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=vehicle&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Vehicles found! <br />Click on <strong>Add Vehicle</strong> to create a one.<br />"); 
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

	LOG_MSG('INFO',"go_vehicle_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_vehicle_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_vehicle_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Vehicle ID
		$vehicle_id=get_arg($_GET,"vehicle_id");

		// Validate the ID
		if (
			!validate("Vehicle Id",$vehicle_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_vehicle_view(): Invalid Vehicle ID [$vehicle_id]!");
			return;
		}

		// Get from DB
		$ROW=db_vehicle_select($vehicle_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Vehicle. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Vehicles found! <br />Click on <strong>Add Vehicle</strong> to create a one.<br /><br />"); 
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

	// To get vehicledoc details
	if ( $mode == 'EDIT' ) {
		if ( ($row_vehicledoc=db_get_list('ARRAY','doc_name,vehicledoc_id','tVehicleDoc',"vehicle_id=$vehicle_id AND travel_id=".TRAVEL_ID)) === false ) return;
		
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_vehicle_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_vehicle_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_vehicle_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$reg_no=get_arg($_POST,"reg_no");
	$route=get_arg($_POST,"route");
	$vehicle_model=get_arg($_POST,"vehicle_model");
	$doc_name=get_arg($_POST,"doc_name");
	$type=get_arg($_POST,"type");
	$sticker_no=get_arg($_POST,"sticker_no");
	$rc_no=get_arg($_POST,"rc_no");
	$rc_exp_dt=get_arg($_POST,"rc_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"rc_exp_dt"))) : get_arg($_POST,"rc_exp_dt");
	$daily_fuel_lmt=get_arg($_POST,"daily_fuel_lmt");
	$monthly_fuel_lmt=get_arg($_POST,"monthly_fuel_lmt");
	$insurance_ref_no=get_arg($_POST,"insurance_ref_no");
	$insurance_exp_dt=get_arg($_POST,"insurance_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"insurance_exp_dt"))) : get_arg($_POST,"insurance_exp_dt");
	$road_tax_ref_no=get_arg($_POST,"road_tax_ref_no");
	$road_tax_exp_dt=get_arg($_POST,"road_tax_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"road_tax_exp_dt"))) : get_arg($_POST,"road_tax_exp_dt");
	$permit=get_arg($_POST,"permit");
	$permit_exp_dt=get_arg($_POST,"permit_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"permit_exp_dt"))) : get_arg($_POST,"permit_exp_dt");
	$authorization=get_arg($_POST,"authorization");
	$authorization_exp_dt=get_arg($_POST,"authorization_exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"authorization_exp_dt"))) : get_arg($_POST,"authorization_exp_dt");
	$start_dt=get_arg($_POST,"start_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"start_dt"))) : get_arg($_POST,"start_dt");
	$end_dt=get_arg($_POST,"end_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"end_dt"))) : get_arg($_POST,"end_dt");
	$driver_id=get_arg($_POST,"driver_id");
	$supervisor_id=get_arg($_POST,"supervisor_id");
	$is_active=get_arg($_POST,"is_active");
	$client_id=get_arg($_POST,"client_id");
	$cleaner_id=get_arg($_POST,"cleaner_id");
	
	LOG_MSG('DEBUG',"do_vehicle_save(): Got args");


	// Validate parameters
	if (
		!validate("Reg No",$reg_no,1,100,"varchar") ||
		!validate("Vehicle Model",$vehicle_model,1,45,"varchar") ||
		!validate("Type",$type,1,10,"varchar") ||
		!validate("Sticker No",$sticker_no,1,45,"varchar") ||
		!validate("Rc No",$rc_no,1,45,"varchar") ||
		!validate("Rc Exp Dt",$rc_exp_dt,1,10,"date") ||
		!validate("Daily Fuel Lmt",$daily_fuel_lmt,0,10,"decimal") ||
		!validate("Monthly Fuel Lmt",$monthly_fuel_lmt,0,10,"decimal") ||
		!validate("Insurance Ref No",$insurance_ref_no,0,45,"varchar") ||
		!validate("Insurance Exp Dt",$insurance_exp_dt,0,10,"date") ||
		!validate("Road Tax Ref No",$road_tax_ref_no,0,45,"varchar") ||
		!validate("Road Tax Exp Dt",$road_tax_exp_dt,0,10,"date") ||
		!validate("permit",$permit,0,200,"varchar") ||
		!validate("permit_exp_dt",$permit_exp_dt,0,10,"varchar") ||
		!validate("authorization",$authorization,0,200,"varchar") ||
		!validate("authorization_exp_dt",$authorization_exp_dt,0,10,"varchar") ||
		!validate("Start Dt",$start_dt,0,30,"varchar") ||
		!validate("End Dt",$end_dt,0,30,"varchar") ||
		!validate("Driver Id",$driver_id,0,11,"int") ||
		!validate("Supervisor Id",$supervisor_id,0,11,"int") ||
		!validate("Client Id",$client_id,0,11,"int") ||
		!validate("Cleaner Id",$cleaner_id,0,11,"int") ||
		!validate("Is Active",$is_active,1,1,"tinyint")){
		LOG_MSG('ERROR',"do_vehicle_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_vehicle_save(): Validated args");

	db_transaction_start();
	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_vehicle_insert(
								$reg_no,
								$route,
								$vehicle_model,
								$type,
								$sticker_no,
								$rc_no,
								$rc_exp_dt,
								$daily_fuel_lmt,
								$monthly_fuel_lmt,
								$insurance_ref_no,
								$insurance_exp_dt,
								$road_tax_ref_no,
								$road_tax_exp_dt,
								$permit,
								$permit_exp_dt,
								$authorization,
								$authorization_exp_dt,
								$start_dt,
								$end_dt,
								$driver_id,
								$supervisor_id,
								$client_id,
								$cleaner_id,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Vehicle <strong>$reg_no</strong> is already in use. Please enter a different Vehicle<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Vehicle <strong>$reg_no</strong>.");
				}
				LOG_MSG('ERROR',"do_vehicle_save(): Add args failed!");
				return;
			}
			$vehicle_id=$ROW["INSERT_ID"];
			add_msg("SUCCESS","New Vehicle <strong>$vehicle_id</strong> added successfully");
			if ( $ROW['NROWS'] == 1 && !app_sync('tVehicle',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
				return;
			}
			break;
		case "UPDATE":
			$vehicle_id=get_arg($_POST,"vehicle_id");
			// Validate vehicle_id
			if (
				!validate("Vehicle Id",$vehicle_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_vehicle_save(): Failed to validate PK");
				return;
			}

			$ROW=db_vehicle_update(
								$vehicle_id,
								$reg_no,
								$route,
								$vehicle_model,
								$type,
								$sticker_no,
								$rc_no,
								$rc_exp_dt,
								$daily_fuel_lmt,
								$monthly_fuel_lmt,
								$insurance_ref_no,
								$insurance_exp_dt,
								$road_tax_ref_no,
								$road_tax_exp_dt,
								$permit,
								$permit_exp_dt,
								$authorization,
								$authorization_exp_dt,
								$start_dt,
								$end_dt,
								$driver_id,
								$supervisor_id,
								$client_id,
								$cleaner_id,
								$is_active						);
								if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Vehicle <strong>$vehicle_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Vehicle <strong>$vehicle_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tVehicle',$vehicle_id,'U',TRAVEL_ID) ) {
				return;
			}
			break;
	}

	// Add Vechicle documents
	
	if ( $_FILES['doc']['name'] != '' ) {
		$temp_image=$_FILES['doc']['name'];

		$ext = pathinfo($temp_image, PATHINFO_EXTENSION);
		$uploaded_doc_name = basename($temp_image, ".".$ext);									// Extract name
		$doc_name=substr( make_clean_url( $uploaded_doc_name ), 0, 130 ).".".date('YmdHis').".".$ext;

		$ROW=db_vehicledoc_insert(
							$doc_name,
							$vehicle_id);
		if ( $ROW['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error adding the Vehicle Document for <strong>$reg_no</strong>.");
			LOG_MSG('ERROR',"do_vehicle_save(): Add vehicledoc failed!");
			return;
		}
		// Upload the image after inserting as we need the org_id
		if ( !copy(get_arg($_FILES['doc'],'tmp_name'),UPLOAD_DIR."vehicledoc/$doc_name") ) {
			add_msg("ERROR","Error while uploading the image");
			return;
		}
	}

	db_transaction_end();

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_vehicle_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_vehicle_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_vehicle_delete(): START POST=".print_r($_POST,true));

	$vehicle_id=get_arg($_POST,"vehicle_id");

	// Validate vehicle_id
	if (
		!validate("Vehicle Id",$vehicle_id,1,11,"int")
	) { return; }

	db_transaction_start();
	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_vehicle_delete($vehicle_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The Vehicle <strong>$vehicle_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the Vehicle <strong>$vehicle_id</strong>");
		}
		return;
	}
	if( $ROW['NROWS'] == 1 && !app_sync('tVehicle',$vehicle_id,'D',TRAVEL_ID)) {
		 return;
	}
	db_transaction_end();

	add_msg("SUCCESS","Vehicle <strong>$vehicle_id</strong> has been removed.");
	LOG_MSG('INFO',"do_vehicle_delete(): END");

}
function do_vehicle_save_json(){

	if(!has_user_permission(__FUNCTION__)) return; 

	LOG_MSG('INFO',"do_vehicle_save_json(): START (mode=$mode) POST=".print_r($_POST,true));

	$json_response['message']='';
	$json_response['status']='ERROR';

	$vehicle_id=get_arg($_POST,"vehicle_id");
	$is_active=get_arg($_POST,"is_active");

	if (
		!validate("Vehicle Id",$vehicle_id,1,11,"int") ||
		!validate("Is Active",$is_active,1,1,"tinyint")){
		LOG_MSG('ERROR',"do_vehicle_save(): Validate args failed!");
		 return;
	} 

			$ROW=db_vehicle_isactive_update(
								$vehicle_id,
								$is_active						);
			if ( $ROW['STATUS'] != "OK" ) {
					$json_response["message"]="There was an error updating the Vehicle <strong>$vehicle_id</strong> .";
				return;
			}
				$json_response["message"]="Vehicle <strong>$vehicle_id</strong> updated successfully";
			break;

			$json_response['message']='Search updated successfully';
			$json_response['status']='OK';
			echo json_encode($json_response);

	// LOG END here
	LOG_MSG('INFO',"do_vehicle_save_json(): END");
	exit;
}

?>
