<?php
function go_sync_to_sqlite() {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"go_sync_to_sqlite(): START");
	LOG_MSG('INFO',"go_sync_to_sqlite(): START GET=".print_r($_GET,true));

	$imei=get_arg($_GET,'imei');

	db_transaction_start();

	if ( !validate("Imei",$imei,0,200,"varchar") ) {
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Validate args failed!");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	$nfctag_resp=db_nfctag_select();
	if ( $nfctag_resp[0]['STATUS'] !== 'OK') {
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Error logging the student with nfc_tag_id=[$nfc_tag_id] ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$student_row=db_sync_to_sqlite();
	if ( $student_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Error fetching the student or no row found  ");
		$json['message']="No students found to sync";
		echo json_encode($json);
		exit;
	}

	$vehicle_row=db_sync_vehicle_select();
	if ( $vehicle_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Error fetching the supervisor or no rows found  ");
		$json['message']="No supervisor found for imei ".$imei;
		echo json_encode($json);
		exit;
	}

	$client_row=db_att_client_select();
	if ( $client_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_student_login(): Error fetching supervisor or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	// Get the attendance_image_capture_range
	$setting_row=db_attendance_setting_select("attendance_image_capture_range");
	if ( $setting_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Select row failed");
		exit;
	}

	// Check whether image has to sync to server or not when ckicked add from app
	$is_image_sync=0;
	$setting_row_image_sync=db_attendance_setting_select("is_image_sync");
	if ( $setting_row_image_sync[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Select row failed");
		exit;
	}

    if ( $setting_row_image_sync[0]['NROWS'] != 0 ) $is_image_sync=$setting_row_image_sync[0]['value'];

	$supervisor_row=db_att_supervisor_select();
	if ( $supervisor_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_sync_to_sqlite(): Error fetching supervisor or no row found imei=[$imei] ");
		$json['message']="No supervisor row found";
		echo json_encode($json);
		exit;
	}

	db_transaction_end();

	$json['status']='OK';
	$json['student_row']=$student_row;
	$json['vehicle_row']=$vehicle_row;
	$json['setting_row']=$setting_row;
	$json['supervisor_row']=$supervisor_row;
	$json['nfc_tag_row']=$nfctag_resp;
	$json['client_row']=$client_row;
	$json['is_image_sync']=$is_image_sync;

	echo json_encode($json);

	LOG_ARR('INFO','JSON response',$json);
	LOG_MSG('INFO',"go_sync_to_sqlite(): END ");

	exit;
}

function go_update_sqlite() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_update_sqlite(): START");
	$json['message']='';
	$json['status']='ERROR';

	$imei=get_arg($_GET,'imei');

	db_transaction_start();

	if ( !validate("Imei",$imei,0,200,"varchar") ) {
		LOG_MSG('ERROR',"go_update_sqlite(): Validate args failed!");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	//Step 3: if is_sync is 0 then send all the rows from the appsync table to sqlite
	$appsync_row=db_appsync_select();
	if ( $appsync_row[0]['STATUS'] !== 'OK' || $appsync_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_update_sqlite(): Error fetching appSync ");
		$json['message']="Error fetching appSync";
		echo json_encode($json);
		exit;
	}

	//Step 1: Check is_sync is 0 or 1 in tSupervisor for all supervisor
	$supervisor_row = db_att_supervisor_select("",0);
	if ( $supervisor_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_update_sqlite(): Error fetching supervisor or no row found  ");
		$json['message']="No supervisor row found";
		echo json_encode($json);
		exit;
	}

	// If is_sync is 1 delete all the rows from tAppSync
	if($supervisor_row[0]['NROWS'] == 0) {
		$ROW=db_appsync_delete();
		if ( $ROW['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"go_update_sqlite(): Error deleting appsync ");
			$json['message']=$ERROR_MESSAGE;
			echo json_encode($json);
			exit;
		}
	}

	//Step 2: if is_sync of all supervisor is not 0 then check is_sync for 0 or 1 for that particular supervisor using imei
	$supervisor_row=db_att_supervisor_select($imei,0);
	if ( $supervisor_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_update_sqlite(): Error fetching supervisor ");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	if( $supervisor_row[0]['NROWS'] == 0 ) {
		if( $supervisor_row[0]['NROWS'] == 0 ) {
			for ($i=0;$i<$appsync_row[0]['NROWS'];$i++) {
				if( $appsync_row[0]['table_name'] == "tSupervisor" && $appsync_row[0]['status'] == "D" ) {
					LOG_MSG('INFO',"go_update_sqlite(): No supervisor ");
					$supervisor = db_att_supervisor_select("",0,$appsync_row[$i]['primary_id']);
					if ( $supervisor[0]['STATUS'] !== 'OK') {
						LOG_MSG('ERROR',"go_update_sqlite(): Error fetching supervisor ");
						$json['message']="Error fetching vehicle";
						echo json_encode($json);
						exit;
					}
					$appsync_row[$i]['table_row']=$supervisor[0];
					db_transaction_end();
					$json['status']='OK';
					$json['appsync_row']=$appsync_row;
					echo json_encode($json);
					LOG_MSG('INFO',"go_update_sqlite(): JSON ".print_r($json,true));

					LOG_MSG('INFO',"go_update_sqlite(): END ");

					exit;
				}
			}
		}
	}

	//Step 3: if is_sync is 0 then send all the rows from the appsync table to sqlite
	for ($i=0;$i<$appsync_row[0]['NROWS'];$i++) {
		switch($appsync_row[$i]['table_name']) {
			case "tStudent":
				$student = db_sync_to_sqlite( $appsync_row[$i]['primary_id']);
				if ( $student[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching student ");
					$json['message']="Error fetching student";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$student[0];
				break;
			case "tVehicle":
				$vehicle = db_sync_vehicle_select($appsync_row[$i]['primary_id']);
				if ( $vehicle[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching vehicle ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$vehicle[0];
				break;
			case "tSupervisor":
				$supervisor = db_att_supervisor_select("",0,$appsync_row[$i]['primary_id']);
				if ( $supervisor[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching supervisor ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$supervisor[0];
				break;
			case "tSetting":
				$setting = db_attendance_setting_select("attendance_image_capture_range",$appsync_row[$i]['primary_id'] );
				if ( $setting[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching supervisor ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
					$appsync_row[$i]['table_row']=$setting[0];
				break;
			case "tNFCTag":
				$nfc_tag = db_nfctag_select( $appsync_row[$i]['primary_id'] );
				if ( $nfc_tag[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching supervisor ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$nfc_tag[0];
				break;
			case "tClient":
				$client_row = db_att_client_select( $appsync_row[$i]['primary_id'] );
				if ( $client_row[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching client ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$client_row[0];
				break;
			case "tImeiLocation":
				$imei_location_row = db_iisc_location_select($appsync_row[$i]['primary_id'] );
				if ( $imei_location_row[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching client ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$imei_location_row[0];
				break;
			case "tStLocGroup":
				$stlocgrp_row = db_iisc_location_group_select("",$appsync_row[$i]['primary_id'] );
				if ( $stlocgrp_row[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching client ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$stlocgrp_row[0];
				break;
			case "tGroup":
				$group_row = db_iisc_group_select($appsync_row[$i]['primary_id'] );
				if ( $group_row[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching client ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$group_row[0];
				break;
			case "tLocation":
				$location_row = db_sync_location_select($appsync_row[$i]['primary_id'] );
				if ( $location_row[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching client ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$location_row[0];
				break;
			case "tStudentGroup":
				$stgrp_row = db_student_group_select("", $appsync_row[$i]['primary_id'] );
				if ( $stgrp_row[0]['STATUS'] !== 'OK') {
					LOG_MSG('ERROR',"go_update_sqlite(): Error fetching client ");
					$json['message']="Error fetching vehicle";
					echo json_encode($json);
					exit;
				}
				$appsync_row[$i]['table_row']=$stgrp_row[0];
				break;
		}
	}

	db_transaction_end();

	$json['status']='OK';
	$json['appsync_row']=$appsync_row;
	echo json_encode($json);
	LOG_MSG('INFO',"go_update_sqlite(): JSON ".print_r($json,true));

	LOG_MSG('INFO',"go_update_sqlite(): END ");

	exit;
}

function go_update_supervisor() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_update_supervisor(): START");
	$json['message']='';
	$json['status']='ERROR';

	$imei=get_arg($_GET,'imei');

	if ( !validate("Imei",$imei,1,200,"varchar") ) {
		LOG_MSG('ERROR',"go_update_supervisor(): Validate args failed!");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	//Step 1: Check is_sync is 0 or 1 in tSupervisor for all supervisor
	$ROW = db_supervisor_update($imei,1);
	if ( $ROW['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_update_supervisor(): Error fetching supervisor or no row found  ");
		$json['message']="No supervisor row found";
		echo json_encode($json);
		exit;
	}

	$json['status']='OK';
	echo json_encode($json);
	LOG_MSG('INFO',"go_update_supervisor(): JSON ".print_r($json,true));

	LOG_MSG('INFO',"go_update_supervisor(): END ");

	exit;
} 
function go_iisc_sync_to_sqlite() {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"go_iisc_sync_to_sqlite(): START");
	LOG_MSG('INFO',"go_iisc_sync_to_sqlite(): START GET=".print_r($_GET,true));

	$imei=get_arg($_GET,'imei');

	db_transaction_start();

	if ( !validate("Imei",$imei,0,200,"varchar") ) {
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Validate args failed!");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	$nfctag_resp=db_nfctag_select();
	if ( $nfctag_resp[0]['STATUS'] !== 'OK' || $nfctag_resp[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Error logging the student with nfc_tag_id=[$nfc_tag_id] ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$student_row=db_sync_to_sqlite();
	if ( $student_row[0]['STATUS'] !== 'OK' || $student_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Error fetching the student or no row found  ");
		$json['message']="No students found to sync";
		echo json_encode($json);
		exit;
	}

	$vehicle_row=db_sync_vehicle_select();
	if ( $vehicle_row[0]['STATUS'] !== 'OK' || $vehicle_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Error fetching the supervisor or no rows found  ");
		$json['message']="No supervisor found for imei ".$imei;
		echo json_encode($json);
		exit;
	}

	$client_row=db_att_client_select();
	if ( $client_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Error fetching supervisor or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	// Get the attendance_image_capture_range
	$setting_row=db_attendance_setting_select("attendance_image_capture_range");
	if ( $setting_row[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Select row failed");
		exit;
	}

	// Check whether image has to sync to server or not when ckicked add from app
	$is_image_sync=0;
	$setting_row_image_sync=db_attendance_setting_select("is_image_sync");
	if ( $setting_row_image_sync[0]['STATUS'] != "OK" ) {
		$json_response['message']='There was an error fetching the setting details 1';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Select row failed");
		exit;
	}
	$imei_location_row=db_iisc_location_select();
	if ( $imei_location_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$location_group_row=db_iisc_location_group_select();
	if ( $location_group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$group_row=db_iisc_group_select();
	if ( $group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the group or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$location_row=db_sync_location_select();
	if ( $location_group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the location or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	$student_group_row=db_student_group_select();
	if ( $student_group_row[0]['STATUS'] !== 'OK'  ) {
		LOG_MSG('ERROR',"go_iisc_login(): Error fetching the student or no row found ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

    if ( $setting_row_image_sync[0]['NROWS'] != 0 ) $is_image_sync=$setting_row_image_sync[0]['value'];

	$supervisor_row=db_att_supervisor_select();
	if ( $supervisor_row[0]['STATUS'] !== 'OK' || $supervisor_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_iisc_sync_to_sqlite(): Error fetching supervisor or no row found imei=[$imei] ");
		$json['message']="No supervisor row found";
		echo json_encode($json);
		exit;
	}

	db_transaction_end();

	$json['status']='OK';
	$json['student_row']=$student_row;
	$json['vehicle_row']=$vehicle_row;
	$json['setting_row']=$setting_row;
	$json['supervisor_row']=$supervisor_row;
	$json['nfc_tag_row']=$nfctag_resp;
	$json['client_row']=$client_row;
	$json['is_image_sync']=$is_image_sync;
	$json['imei_location_row']=$imei_location_row;
	$json['location_group_row']=$location_group_row;
	$json['student_group_row']=$student_group_row;
	$json['group_row']=$group_row;
	$json['location_row']=$location_row;

	echo json_encode($json);

	LOG_ARR('INFO','JSON response',$json);
	LOG_MSG('INFO',"go_iisc_sync_to_sqlite(): END ");

	exit;
}

?>
