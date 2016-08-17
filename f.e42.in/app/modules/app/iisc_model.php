<?php
// go_iisc_login()
function go_iisc_login() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_iisc_login(): START");

	// Initialize json
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"go_student_login(): START GET=".print_r($_GET,true));

	// Get Params
	$nfc_tag_id=get_arg($_GET,'nfc_tag_id');
	$imei=get_arg($_GET,'imei');

	if ( !validate("NFC Tag ID",$nfc_tag_id,1,45,'varchar') || 
		 !validate("IMEI",$imei,1,45,'varchar') ) {
		LOG_MSG('DEBUG',"go_iisc_login(): VALIDATE ARGS FAILED");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}
	LOG_MSG('DEBUG',"go_iisc_login(): Validated args");

	$json['nfc_tag_id']=$nfc_tag_id;
	$json['imei']=$imei;
	db_transaction_start();

	// STEP 1: Check whether vehicle is valid 
	$vehicle_row=db_att_vehicle_select($imei);
	if ( $vehicle_row[0]['STATUS'] !== 'OK' || $vehicle_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching vehicle or no row found imei=[$nfc_tag_id] ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	// STEP 2: Check whether nfc_tag_id is valid 
	$nfctag_resp=db_nfctag_select($nfc_tag_id);
	if ( $nfctag_resp[0]['STATUS'] !== 'OK' || $nfctag_resp[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error logging the student with nfc_tag_id=[$nfc_tag_id] ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}
	$type=$nfctag_resp[0]['type']; 
	// STEP 3: Get the student id for nfc_tag_id based on the id_number 
	$student_row=db_student_select($nfctag_resp[0]['id_number']);
	if ( $student_row[0]['STATUS'] !== 'OK' || $student_row[0]['NROWS'] !== 1 ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found for id_number=[".$nfctag_resp[0]['id_number']."] ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$imei_location_row=db_iisc_location_select($imei);
	if ( $imei_location_row[0]['STATUS'] !== 'OK' || $imei_location_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$location_group_row=db_iisc_location_group_select($imei_location_row[0]['location_id']);
	if ( $location_group_row[0]['STATUS'] !== 'OK' || $location_group_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$student_group_row=db_student_group_select($student_row[0]['student_id']);
	if ( $student_group_row[0]['STATUS'] !== 'OK' || $student_group_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$supervisor_row=db_att_supervisor_select($imei);
	if ( $supervisor_row[0]['STATUS'] !== 'OK' || $supervisor_row[0]['NROWS'] != 1 ) {
		LOG_MSG('ERROR',"go_student_login(): Error fetching supervisor or no row found imei=[$imei] ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$group_row=db_iisc_group_select($student_group_row[0]['group_id']);
	if ( $group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the group or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$location_row=db_sync_location_select($imei_location_row[0]['location_id']);
	if ( $location_group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the location or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}
	$client_row=db_att_client_select();
	if ( $client_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_student_login(): Error fetching client");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	if($student_group_row[0]['group_id'] != $location_group_row[0]['group_id']) {
		LOG_MSG('ERROR',"go_iisc_login(): Invalid location student group is"
		.$student_group_row[0]['group_id'] ." Location Group is ".$location_group_row[0]['group_id']  );
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	// Get the attendance_image_capture_range
		$setting_row=db_attendance_setting_select("attendance_image_capture_range");
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
					$attendance_image_capture_range=2;
					break;
				case "sometimes":
					$attendance_image_capture_range=4;
					break;
				case "rarely":
					$attendance_image_capture_range=8;
					break;
			}
		}

		if ( $setting_row[0]['value'] != "always" && $setting_row[0]['value'] != "never" ) {
			LOG_MSG('INFO',"db_vehicle_select(): attendance_image_capture_range=[$attendance_image_capture_range]");
			// Check the captured image row available within the range
			$student_log=db_get_attendance_capture_image_count($student_row[0]['student_id'],$attendance_image_capture_range);
			if ( $student_log[0]['STATUS'] != "OK" ) {
				$json_response['message']='There was an error fetching the setting details 1';
				echo json_encode($json_response);
				LOG_MSG('ERROR',"db_vehicle_select(): Select row failed");
				exit;
			}
			$no_image_count=0;
			for ( $i=0;$i<$student_log[0]['NROWS'];$i++ ) {
				if ( $student_log[$i]['student_image'] == 'no_image.jpg') $no_image_count++;
			}
			LOG_MSG('INFO',"db_vehicle_select(): no_image_count=[$no_image_count] image_capture_range=[$image_capture_range]");
			// 1. when new student comes for first time no of rows will be 0 so image must be captured
			// 2. if image capture range is equal to no of rows with no images then image must be captured
			if ( $student_log[0]['NROWS'] == 0 || $no_image_count == $image_capture_range ) {
				$is_capture_image=true;
			}
		}

	$json['tag_id']=$nfc_tag_id;
	$json['imei']=$imei;
	$json['student_id']=$student_row[0]['student_id'];
	$json['is_active']=$student_row[0]['is_active'];
	$json['exp_dt']=$student_row[0]['exp_dt'];
	$json['name']=$student_row[0]['name'];
	$json['guardian_name']=$student_row[0]['guardian_name'];
	$json['id_number']=$student_row[0]['id_number'];
	$json['phone']=$student_row[0]['phone']; 
	$json['email_id']=$student_row[0]['email_id']; 
	$json['photo']=$student_row[0]['student_photo']; 
	$json['guardian_photo']=$student_row[0]['guardian_photo']; 
	$json['type']=$type; 
	$json['student_reg_no']=$student_row[0]['reg_no']; 
	$json['route']=$vehicle_row[0]['route']; 
	$json['client_name']=$student_row[0]['client_name']; 
	$json['setting_row']=$setting_row;
	$json['travel_id']=TRAVEL_ID;
	$json['vehicle_row']=$vehicle_row;
	$json['nfc_tag_row']=$nfctag_resp;
	$json['supervisor_row']=$supervisor_row;
	$json['client_row']=$client_row;
	$json['st_reg_no']=$student_row[0]['reg_no'];
	$json['time_in']=date('Y-m-d H:i:s');
	$json['is_capture_image']=$is_capture_image;
	$json['status']="OK";
	$json['imei_location_row']=$imei_location_row;
	$json['location_group_row']=$location_group_row;
	$json['student_group_row']=$student_group_row;
	$json['location_row']=$location_row;
	$json['group_row']=$group_row;
	$json['created_dt']=date("Y-m-d H:i:s");

	// STEP 3: Check whether the current time is morning or evening
	$time_of_day = date('H:i:s') <= '11:59:59' ? 'MORNING' : 'EVENING';
	LOG_MSG('INFO',"go_iisc_login(): time_of_day=[$time_of_day]");

	// STEP 5: Check for the previous logs
	$log_row=db_iisclog_select("",$student_row[0]['student_id'],date('Y-m-d'));
	if ( $log_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error logging the student with nfc_tag_id=[$nfc_tag_id] ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	// Init required to store in tiisclog
	$reg_no=$vehicle_row[0]['reg_no'];
	$today=date("Y-m-d H:ia");
	// First Time in, Insert a new row if the current 
	if ( $log_row[0]['NROWS'] == 0 ) {

		$resp=db_iisclog_insert($student_row[0]['student_id'],$reg_no,date("Y-m-d H:i:s"));
		if ( $resp['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"go_iisc_login(): Error while inserting the new row");
			$json['message']="Error while logging. Please contact customer care";
			echo json_encode($json);
			exit;
		}

		$iisclog_id=$resp['INSERT_ID'];
		db_transaction_end();

		//send_email('nteju37@gmail.com','tejaswini@cloudnix.com','','','Check In','Student checked in');
		$json['status']='OK';
		$json['studentlog_id']=$resp['INSERT_ID'];
		$json['message']="Check In - $reg_no at $today";
		$json['arrival']="Check In";
		$json['success']="yes";
		echo json_encode($json);
		exit;
	}

	// If already time in and the tap is within 5 minutes, return true 
	// Else update the row to store the time out time
	if ( $log_row[0]['NROWS'] > 0 ) {
		$time_interval=3; // Two or more consecutive taps within a time frame of 3 minutes to be ignored
		$from_time=strtotime($log_row[0]['time_in']);
		$to_time=strtotime(date("Y-m-d H:i:s"));
		$total_elapsed_min=round(abs($to_time - $from_time) / 60,2);
		LOG_MSG('INFO',"go_iisc_login(): time_interval=[$time_interval] total_elapsed_min=[$total_elapsed_min]");

		if ( $total_elapsed_min <= $time_interval ) {
			$json['status']='OK';
			$json['message']="Check In - $reg_no at $to_time";
			$json['arrival']="Check In";
			$json['success']="yes";
			$json['is_capture_image']=false;
			LOG_MSG('INFO',"go_iisc_login(): Logging again within a time interval of [$time_interval min]");
			echo json_encode($json);
			exit;
		}

		$resp=db_iisclog_insert($student_row[0]['student_id'],$reg_no,date("Y-m-d H:i:s"));
		if ( $resp['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"go_iisc_login(): Error while inserting the new row");
			$json['message']="Error while logging. Please contact customer care";
			echo json_encode($json);
			exit;
		}
		$iisclog_id=$resp['INSERT_ID'];
		db_transaction_end();

		//send_email('nteju37@gmail.com','tejaswini@cloudnix.com','','','Check In','Student checked in');
		$json['status']='OK';
		$json['studentlog_id']=$resp['INSERT_ID'];
		$json['message']="Check In - $reg_no at $today";
		$json['arrival']="Check In";
		$json['success']="yes";
		echo json_encode($json);
		exit;
	}
}

function iisc_app_attendance_image_store() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"app_attendance_image_store(): START");

	$json_response['message']='';
	$json_response['status']='ERROR';

	// Get image type 
	$image_type=get_arg($_POST,"image_type");
	LOG_MSG('INFO'," image_type=[$image_type]");
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

		$iisclog_id=get_arg($_POST,'studentlog_id');

		if ( !validate("Student ID",$iisclog_id,1,11,"int") ||
			  !validate("Image",$image_name,1,200,"varchar") ) {
				$json_response['message'] = $ERROR_MESSAGE;
				echo json_encode($json_response);
				LOG_MSG("INFO","app_image_store(): image_type=[$image_type]");
				exit;
		}

		$resp=db_iisclog_update(	$iisclog_id,
									$image_name);
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

?>
