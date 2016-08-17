<?php

/**********************************************************************/
/* SELECT VEHICLE get_app_validate_vehicle_json RECORD                  */
/**********************************************************************/
function get_app_validate_vehicle_json() {

	//global $ROW,$ERROR_MESSAGE;
	$json_response=array();
	$json_response['message']='';
	$json_response['status']='ERROR';
	LOG_MSG('INFO',"get_app_validate_vehicle_json(): START GET=".print_r($_GET,true));

	$nfc_tag_id=get_arg($_GET,'nfc_tag_id');
	$imei=get_arg($_GET,'imei');

	// Validate the args nfc_tag_id and imei
	if ( !validate("NFC Tag Id",$nfc_tag_id,1,45,"varchar") ||
		 !validate("IMEI no",$imei,1,45,"varchar")||
		 !validate("Domain",DOMAIN,1,200,"varchar")) {
		$json_response['message']='Error';
		echo json_encode($json_response);
		exit;
	}

	db_transaction_start();

	if ( ($travel_id=db_get_list('LIST','travel_id','tTravel',"domain='".DOMAIN."'")) === false ) {
		$json_response['message']=' Error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_app_validate_vehicle_json(): Error while fetching travel_id domain=[".DOMAIN."]");
		exit;
	}

	if ( $travel_id === '' ) {
		$json_response['message']='Error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_app_validate_vehicle_json(): travel_id=[$travel_id] for domain=[".DOMAIN."] cannot be empty");
		exit;
	}

	// Vehicle
	$tagsticker_row=db_tagsticker_select($nfc_tag_id,$travel_id);
	if ( $tagsticker_row[0]['STATUS'] != "OK" ) {
		$json_response['message']=' There was an error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_tagsticker_select(): Select row failed");
		exit;
	}

	if ( $tagsticker_row[0]['NROWS'] == 0 ) {
		$json_response['message']='Invalid Vehicle';
		LOG_MSG('ERROR','No NFC tag sticker found for the nfc_tag_id=[$nfc_tag_id]');
		echo json_encode($json_response);
		exit;
	} 

	/* Required later - Do not remove
	// If found, then check for the existence of supervisor 
	$supervisor_row=db_supervisor_select($imei);
	if ( $supervisor_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_supervisor_select(): Select row failed");
		exit;
	}

	if ( $supervisor_row[0]['NROWS'] == 0 ) {
		$json_response['message']='Invalid Vehicle';
		LOG_MSG('ERROR','No supervisor for the imei=[$imei]');
		echo json_encode($json_response);
		exit;
	} 	*/

	// Step 2: Check the vehicle validation 
	// A vehicle is invalid for the following
	// 1. If no sticker no found for that vehicle
	// 2. If no Supervisor assigned to it
	// 3. If no Driver assigned to it
	// 4. If Is active is zero
	// Get data from DB

	
	$vehicle_row=db_vehicle_select($tagsticker_row[0]['sticker_no'],$travel_id);
	if ( $vehicle_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the vehicle details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_vehicle_select(): Select row failed");
		exit;
	}

	//add_msg("SUCCESS","Vehicle details fetched successfully.");

	if ( $vehicle_row[0]['NROWS'] == 0 ) {
		$json_response['message']='Invalid Vehicle ';
		LOG_MSG('ERROR','No row found for the vehicle for the sticker no=['.$tagsticker_row[0]['sticker_no'].']');
		echo json_encode($json_response);
		exit;
	}

	// Get the image_capture_range 
	$setting_row=db_setting_select("image_capture_range",$travel_id);
	if ( $setting_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_vehicle_select(): Select row failed");
		exit;
	}

	// Check whether image should bew captured or not
	$is_capture_image=false;
	if ( $setting_row[0]['NROWS'] > 0 ) {
		switch($setting_row[0]['value']) {
			case "always":
				$is_capture_image=true;
				break;
			case "frequently":
				$image_capture_range=2;
				break;
			case "sometimes":
				$image_capture_range=4;
				break;
			case "rarely":
				$image_capture_range=8;
				break;
		}
	}

	if ( $setting_row[0]['value'] != "always" && $setting_row[0]['value'] != "never" ) {
		LOG_MSG('INFO',"db_vehicle_select(): image_capture_range=[$image_capture_range]");
		// Check the captured image row available within the range
		$search_row=db_get_capture_image_count($vehicle_row[0]['reg_no'],$image_capture_range);
		if ( $setting_row[0]['STATUS'] != "OK" ) {
			$json_response['message']='There was an error fetching the setting details 1';
			echo json_encode($json_response);
			LOG_MSG('ERROR',"db_vehicle_select(): Select row failed");
			exit;
		}

		$no_image_count=0;
		for ( $i=0;$i<$search_row[0]['NROWS'];$i++ ) {
			if ( $search_row[$i]['fuel_image'] == 'no_image.jpg' || $search_row[$i]['odometer_image'] == 'no_image.jpg' ) $no_image_count++;
		}
		LOG_MSG('INFO',"db_vehicle_select(): no_image_count=[$no_image_count] image_capture_range=[$image_capture_range]");
		if ( $search_row[0]['NROWS'] == 0 || $no_image_count == $image_capture_range ) {
			$is_capture_image=true;
		}
	}

	// Get fuel rate of vehicle
	$fuel_rate=0.00;
	$filling_station="";
	$filling_station_row=db_filling_station_select($imei,$travel_id);
	if ( $filling_station_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_fuel_rate_select(): Select row failed");
		exit;
	}
	if( $filling_station_row[0]['NROWS'] == 1 ) {
		$filling_station=$filling_station_row[0]['location'];
		$fuel_rate=$filling_station_row[0]['fuel_rate'];
	} 

	// Get from master setting if not found
	if ( $fuel_rate == 0.00 ) {
	$setting_fuel_rate=db_setting_select("upcoming_fuel_rate",$travel_id);
	if ( $setting_fuel_rate[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_fuel_rate_select(): Select row failed");
		exit;
	}
	if( $setting_fuel_rate[0]['NROWS'] == 1 ) {
		$fuel_rate=$setting_fuel_rate[0]['value'];
	} 
	}

	$description="Active";
	$vehicle_status=1;
	if ( $vehicle_row[0]['is_active'] == 0 ) {
		$description ="Vehicle status is inactive.";
		$vehicle_status=0;
	}
	if ( $vehicle_row[0]['driver_active'] == 0 ) {
		$description .=" Driver status is inactive.";
		$vehicle_status=0;
	}
	if ( $vehicle_row[0]['supervisor_active'] == 0 ) {
		$description .=" Supervisor status is inactive.";
		$vehicle_status=0;
	}

	$account_time_end=db_setting_select("account_time_end",$travel_id);
	if ( $account_time_end[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"db_vehicle_select(): Select row failed");
		exit;
	}

	$current_time=date("H:i");
	$current_date_time=date("Y-m-d H:i:s");
	//if( strtotime($current_time) < strtotime($account_time_end[0]['value']) ) 
		//$vehicle_row[0]['accountability_date']=$current_date_time;
	//else
	if ( $account_time_end[0]['NROWS'] != 0 ) {
		$hour=date("H",strtotime($account_time_end[0]['value']));
		$vehicle_row[0]['accountability_date']= date('Y-m-d H:i', strtotime($current_date_time ." -$hour hour")); 
	} else {
		$vehicle_row[0]['accountability_date']=  date('Y-m-d H:i:s') ; 
	}

	// Store the searched vehicle details
	$resp=db_search_insert(	$vehicle_row[0]['reg_no'],
							$imei,
							$vehicle_row[0]['vehicle_model'],
							$vehicle_status,
							$description,
							$vehicle_row[0]['driver name'],
							$vehicle_row[0]['contact no'],
							$vehicle_row[0]['owner_ph_no'],
							$vehicle_row[0]['driver_sal'],
							$vehicle_row[0]['cleaner_name'],
							$vehicle_row[0]['cleaner_salary'],
							$vehicle_row[0]['supervisor name'],
							$vehicle_row[0]['supervisor_phone_no'],
							$vehicle_row[0]['client_name'],
							$vehicle_row[0]['client_mobile'],
							$vehicle_row[0]['day fuel limit in ltrs'],
							$vehicle_row[0]['month fuel limit in ltrs'],
							$filling_station,
							$fuel_rate,
							$vehicle_row[0]['accountability_date'],
							$travel_id);		
	if ( $resp['STATUS'] != "OK" ) {
		switch ($resp["SQL_ERROR_CODE"]) {
			case 1062: // unique key
				$json_response["message"]="The Search is already in use. Please enter a different Search<br/>";
				break;
			default:
				$json_response["message"]="There was an error adding the Search <strong></strong>.";
				break;
		}
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_search_save(): Add args failed!".print_r($json_response,true));
		exit;
	}
	//add_msg("SUCCESS","New Search <strong></strong> added successfully");

	db_transaction_end();

	LOG_MSG('INFO',"get_app_validate_vehicle_json(): END");
	$json_response['status']='OK';
	$json_response['is_capture_image']=$is_capture_image;
	$json_response['row']=$vehicle_row;
	$json_response['search_id']=$resp['INSERT_ID'];
	$json_response['message']='Valid';

	//Send json response to response handler 
	echo json_encode($json_response);
	exit;

}

/**********************************************************************/
/*                           INSERT FUEL                              */
/**********************************************************************/
function app_search_save_json() {

	LOG_MSG('INFO',"app_search_save_json(): START GET".print_r($_GET,true));

	$json_response['message']='';
	$json_response['status']='ERROR';

	// Get the args
	$search_id=get_arg($_GET,'search_id');
	$fuel_filled=get_arg($_GET,'fuel_filled');
	$odometer_reading=get_arg($_GET,'odometer_reading');

	//validadte the args

	if (
		!validate("Search ID",$search_id,1,11,"int") ||
		($fuel_filled != "" && !validate("Fuel Filled",$fuel_filled,1,30,"varchar")) || 
		($odometer_reading != "" && !validate("Odometer Reading",$odometer_reading,1,30,"varchar")) ||
		!validate("Domain",DOMAIN,1,200,"varchar")) {
		LOG_MSG('ERROR',"app_search_save_json(): Validate args failed!");
		 return;
	} 

	LOG_MSG('DEBUG',"app_search_save_json(): Validated args");

	if ( ($travel_id=db_get_list('LIST','travel_id','tTravel',"domain='".DOMAIN."'")) === false ) {
		$json_response['message']=' Error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_app_validate_vehicle_json(): Error while fetching travel_id domain=[".DOMAIN."]");
		exit;
	}

	if ( $travel_id === '' ) {
		$json_response['message']='Error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_app_validate_vehicle_json(): travel_id=[$travel_id] for domain=[".DOMAIN."] cannot be empty");
		exit;
	}

	//define('TRAVEL_ID',$travel_id);

	// Store the details
	$resp=db_search_update(	$search_id,
				$fuel_filled,
				$odometer_reading);
	if ( $resp['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error updating the Search <strong></strong> .");
		return;
	}

        if ( $odometer_reading != '' ) {
        // Send SMS
        $search_row=db_get_list("ARRAY","reg_no,vehicle_model,owner_ph_no,supervisor_phone_no,fuel_filled,odometer_reading","tSearch","search_id=$search_id");
        if ( $search_row[0]['STATUS'] != "OK" ) {
                $json_response['message']="There was an error getting the Search details<strong></strong> .";
                echo json_encode($json_response);
                exit;
        }

        $message="Fuel of ".$search_row[0]['fuel_filled']." ltrs has been filled by vehicle ".$search_row[0]['reg_no']." model ".$search_row[0]['vehicle_model']." and Odometer recorded as ".$search_row[0]['odometer_reading']." on ".date('Y-M-d h:ia');
        if ( $search_row[0]['owner_ph_no'] != '' && !send_sms($search_row[0]['owner_ph_no'],$message) ) {
                        $json_response['message']="There was an error updating the Search <strong></strong> .";
                        echo json_encode($json_response);
                        exit;
        }

        if ( $search_row[0]['supervisor_phone_no'] != '' && !send_sms($search_row[0]['supervisor_phone_no'],$message) ) {
                        $json_response['message']="There was an error updating the Search <strong></strong> .";
                        echo json_encode($json_response);
                        exit;
        }
        }

	add_msg("SUCCESS","Search <strong></strong> updated successfully");
	$json_response['message']='Search updated successfully';
	$json_response['status']='OK';
	echo json_encode($json_response);

	// LOG END here
	LOG_MSG('INFO',"app_search_save_json(): END");
	exit;

}

function app_image_store() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"app_image_store(): START");

	$json_response['message']='';
	$json_response['status']='ERROR';

	if ( ($travel_id=db_get_list('LIST','travel_id','tTravel',"domain='".DOMAIN."'")) === false ) {
		$json_response['message']=' Error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_app_validate_vehicle_json(): Error while fetching travel_id domain=[".DOMAIN."]");
		exit;
	}

	if ( $travel_id === '' ) {
		$json_response['message']='Error fetching the vehicle details';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_app_validate_vehicle_json(): travel_id=[$travel_id] for domain=[".DOMAIN."] cannot be empty");
		exit;
	}

	// Get image type 
	$image_type=get_arg($_POST,"image_type");
	$json_response['image_type']=$image_type;
	

	// Path to move uploaded files
	$target_path = "../media/".DOMAIN."/images/$image_type/";

	if ( isset($_FILES['image']['name']) ) {

		$image_name=$_FILES['image']['name'];
		$target_path = $target_path.$image_name;	// ../media/images/IMAG_20323_324

		// Throws exception incase file is not being moved
		if ( !move_uploaded_file($_FILES['image']['tmp_name'], $target_path) ) {
			// make error flag true
			$json_response['message'] = 'Could not move the file!';
			echo json_encode($json_response);
			LOG_MSG("INFO","app_image_store(): fuel_image=[$fuel_image], target_path=[$target_path]");
			exit;
		}

		$search_id=get_arg($_POST,'search_id');

		// Since either one image comes from App, initialize the parameter to empty
		$fuel_image="";
		$odometer_image="";

		// Assign the values to either fuel or odometer image based on the image type
		switch ( $image_type ) {
			case "fuel":
				$fuel_image=$image_name;
				break;
			case "odometer":
				$odometer_image=$image_name;
				break;
			default:
				$json_response['message'] = 'Invalid Image Type';
				echo json_encode($json_response);
				LOG_MSG("INFO","app_image_store(): image_type=[$image_type]");
				exit;
		}

		if ( !validate("Search ID",$search_id,1,11,"int") ||
			 ($fuel_image != "" && !validate("Fuel Image",$fuel_image,1,200,"varchar")) ||
			 ($odometer_image != "" && !validate("Odometer Image",$odometer_image,1,200,"varchar")) ||
				!validate("Domain",DOMAIN,1,200,"varchar")) {
				$json_response['message'] = $ERROR_MESSAGE;
				echo json_encode($json_response);
				LOG_MSG("INFO","app_image_store(): image_type=[$image_type]");
				exit;
		}

		$resp=db_search_update(	$search_id,
								"",
								"",
								$fuel_image,
								$odometer_image);
		if ( $resp['STATUS'] != "OK" ) {
			$json_response['status'] = 'There was an error updating the Search';
			echo json_encode($json_response);
			exit;
		}
	}

	$json_response['message']='Image uploaded successfully';
	$json_response['status']='OK';

	// Echo final json response to client
	echo json_encode($json_response);

	exit;

}

function go_fuel_filled_summary() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_fuel_filled_summary(): START");

	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO', "go_fuel_filled_summary(): START");
	$imei=get_arg($_GET,"imei");

	if( !validate("Imei",$imei,1,45,'varchar') ) {
		LOG_MSG('DEBUG',"go_fuel_filled_summary() : Validate Args Failed");
		$json['message']="Validate args failed";
		echo json_encode($json);
		exit;
	}

	$search_row=db_search_select($imei,date("Y-m-d"));
	if( $search_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('DEBUG', "go_fuel_filled_summary() : Error fetching details or no rows found ");
		$json['message']="Error fetching details or no rows found";
		echo json_encode($json);
		exit;
	}

	for ( $i=0;$i<$search_row[0]['NROWS'];$i++ ) {
		$search_row[$i]['fuel_amount']=$search_row[$i]['fuel_filled'] * $search_row[$i]['fuel_rate'] ;
		$json['fuel_amount']=$search_row[$i]['fuel_amount'];
	}

	$json['fuel_filled']=$search_row[0]['fuel_filled'];
	$json['total_fills']=$search_row[0]['total_fills'];
	$json['NROWS']=$search_row[0]['NROWS'];
	$json['status']='OK';
	LOG_MSG('INFO',"go_fuel_filled_summary(): Json response".print_r($json,true));
	db_transaction_end();
	LOG_MSG('INFO',"go_fuel_filled_summary(): End");

	echo json_encode($json);
	exit;
}

?>
