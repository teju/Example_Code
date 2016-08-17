<?php

function go_sync_to_db() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_sync_to_db(): START");
	$json['message']='';
	$json['status']='ERROR';

	$arr=json_decode(get_arg($_POST,"logJSON"),true);
	LOG_MSG('INFO',"go_sync_to_db(): START GET=".print_r($arr,true));
	$sync_studentlog_id=array();
	$count = sizeof($arr);

	$loginsert_count=0;
	for ( $i=0;$i<$count;$i++ ) {

		$studentlog_id=get_arg($arr[$i],"studentlog_id");
		$student_id=get_arg($arr[$i],"student_id");
		$time_in=get_arg($arr[$i],"time_in");
		$time_out=get_arg($arr[$i],"time_out");
		$time_of_day=get_arg($arr[$i],"time_of_day");
		$reg_no=get_arg($arr[$i],"reg_no");
		$st_reg_no=get_arg($arr[$i],"st_reg_no");
		$client_name=get_arg($arr[$i],"client_name");
		$route=get_arg($arr[$i],"route");
		$check_in_image=get_arg($arr[$i],"check_in_image");
		$check_out_image=get_arg($arr[$i],"check_out_image");
		$latitude=get_arg($arr[$i],"latitude");
		$longitude=get_arg($arr[$i],"longitude");
		$address=get_arg($arr[$i],"address");
		$travel_id=get_arg($arr[$i],"travel_id");

		// Validate parameters as normal strings 
		if (
			!validate("StudentLog Id",$studentlog_id,1,200,"varchar") ||
			!validate("Student Id",$student_id,1,200,"varchar") ||
			!validate("Time In",$time_in,1,200,"varchar") ||
			!validate("Time Out",$time_out,0,200,"varchar") ||
			!validate("Time Of Day",$time_of_day,1,200,"varchar") ||
			!validate("Reg No",$reg_no,0,200,"varchar") ||
			!validate("Student Reg No",$st_reg_no,0,200,"varchar") ||
			!validate("Client Name",$client_name,0,200,"varchar") ||
			!validate("Check In Image",$check_in_image,0,200,"varchar") ||
			!validate("Check Out Image",$check_out_image,0,200,"varchar") ||
			!validate("Latitude",$latitude,0,200,"varchar") ||
			!validate("Longitude",$longitude,0,200,"varchar") ||
			!validate("Address",$address,0,200,"varchar") ||
			!validate("Travel Id",$travel_id,1,100,"varchar") ){
			LOG_MSG('ERROR',"go_sync_to_db(): Validate args failed!");
			return;
		}
		LOG_MSG('DEBUG',"go_sync_to_db(): Validated args");

		db_transaction_start();

		$studentlog_row=db_syncstudentlog_select($student_id,$time_in);
		LOG_MSG('INFO',"go_sync_to_db(): studentlog_row ".print_r($studentlog_row,true));
		if ( $studentlog_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"do_student_login(): Error logging the student with student_id=[$student_id] ");
			$json['message']="Invalid Card";
			echo json_encode($json);
			exit;
		}

		$student_row=db_student_select("",$student_id);
		if ( $student_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"do_student_login(): Error fetching the student or no row found for id_number=[".$nfctag_resp[0]['id_number']."] ");
			$json['message']="No student found to sync";
			echo json_encode($json);
			exit;
		}

		if ( $student_row[0]['NROWS'] == 0 ) {
			LOG_MSG('INFO',"do_student_login(): NO ROWS FOUND for student_id=[$student_id]");
			$sync_studentlog_id[$loginsert_count]=$studentlog_id;
			$loginsert_count++;
			continue;
		}

		// Since the time of day is not checking in the app side by Tejaswini
		// We are handling it he. Should be removed when releasing the latest app
		if ( $studentlog_row[0]['NROWS'] > 0 && 
			$time_of_day == "Morning" && 
			strtotime($time_out) > strtotime(date("Y-m-d 11:59:59")) && 
			$time_out != "0000-00-00 00:00:00" ) {
			$studentlog_row_evening=db_syncstudentlog_select($student_id,$time_out);
			if ( $studentlog_row_evening[0]['STATUS'] !== 'OK' ) {
				LOG_MSG('ERROR',"do_student_login(): Error logging the student with student_id=[$student_id] ");
				$json['message']="Invalid Card";
				echo json_encode($json);
				exit;
			}
			if ( get_arg($studentlog_row_evening[0],'time_of_day') == "EVENING" ) {
				$sync_studentlog_id[$loginsert_count]=$studentlog_id;
				$loginsert_count++;
				continue;
			}
			$resp=db_synclog_insert($student_id,
									$time_out,
									"0000-00-00 00:00:00",
									"EVENING",
									$reg_no,
									$st_reg_no,
									$client_name,
									$route,
									$check_in_image,
									$check_out_image,
									$latitude,
									$longitude,
									$address,
									$travel_id);
			if ( $resp['STATUS'] !== 'OK' ) {
				LOG_MSG('ERROR',"db_synclog_insert(): Error while inserting the new row");
				$json['message']="Error while logging. Please contact customer care";
				echo json_encode($json);
				exit;
			}
			$time_out="0000-00-00 00:00:00";
		}

		if($studentlog_row[0]['NROWS'] == 0 ) {
			$resp=db_synclog_insert($student_id,
										$time_in,
										$time_out,
										$time_of_day,
										$reg_no,
										$st_reg_no,
										$client_name,
										$route,
										$check_in_image,
										$check_out_image,
										$latitude,
										$longitude,
										$address,
										$travel_id);			if ( $resp['STATUS'] !== 'OK' ) {
					LOG_MSG('ERROR',"db_synclog_insert(): Error while inserting the new row");
					$json['message']="Error while logging. Please contact customer care";
					echo json_encode($json);
					exit;
				}

			// sending email upon check-in
			if ( $student_row[0]['email_id'] != "" ) {
				send_email($student_row[0]['email_id'],'admin@element42.in','','','Boarding Notification',$student_row[0]['name']." boarded in at ".date('Y-m-d H:i:s'));
			}
			$sync_studentlog_id[$loginsert_count]=$studentlog_id;
			$loginsert_count++;
		} else {
			$resp=db_sync_studentlog_update($student_id,$time_in,$time_out,$check_out_image,$check_in_image);
			if ( $resp['STATUS'] !== 'OK' ) {
					LOG_MSG('ERROR',"db_sync_studentlog_update(): Error while updating the new row");
					$json['message']="Error while logging. Please contact customer care";
					echo json_encode($json);
					exit;
				}
			// Since check out will be called multiple times, mail should be sent only for one check out 
			// Second time update field values will be same as first, which returns 0 rows
			if ( $student_row[0]['email_id'] != "" && $resp['NROWS'] != 0 ) {
				send_email($student_row[0]['email_id'],' admin@element42.in','','','Boarding Notification',$student_row[0]['name']." boarded out at ".$time_out);
		}
	}
		db_transaction_end();
	}
	$json['status']='OK';
	$json['studentlog_id']=$sync_studentlog_id;
	echo json_encode($json);
	LOG_MSG('INFO',"go_sync_to_db(): JSON ".print_r($sync_studentlog_id,true));

	LOG_MSG('INFO',"go_sync_to_db(): END ");

	exit;
}

function app_attendance_sync_image_store() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"app_attendance_sync_image_store(): START");

	// Get image type 
	$image_type=get_arg($_POST,"image_type");
	LOG_MSG('INFO'," image_type=[$image_type]");
	$json_response['image_type']=$image_type;

       if ( $image_type == "student" ) $image_dir="student";
		else $image_dir="guardian";
	// Path to move uploaded files
	$target_path = "../media/".DOMAIN."/images/$image_dir/";

	if ( isset($_FILES['image']['name']) ) {

		$image_name=$_FILES['image']['name'];
		$target_path = $target_path.$image_name;	// ../media/images/IMAG_20323_324

		// Throws exception incase file is not being moved
		if ( !move_uploaded_file($_FILES['image']['tmp_name'], $target_path) ) {
			// make error flag true
			$json_response['message'] = 'Could not move the file!';
			echo json_encode($json_response);
			LOG_MSG("INFO","app_attendance_sync_image_store(): target_path=[$target_path]");
			exit;
		}
	}
	LOG_MSG('INFO',"app_attendance_sync_image_store(): END");

	exit;
}

function go_sync_attendance_log_to_db() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_sync_attendance_log_to_db(): START");
	$json['message']='';
	$json['status']='ERROR';

	$arr=json_decode(get_arg($_POST,"attendancelogJSON"),true);
	LOG_MSG('INFO',"go_sync_attendance_log_to_db(): START Attendance_Log GET=".print_r($arr,true));
	$sync_attendancelog_id=array();
	$count = sizeof($arr);

	db_transaction_start();

	$loginsert_count=0;
	for ( $i=0;$i<$count;$i++ ) {

		$attendancelog_id=get_arg($arr[$i],"attendancelog_id");
		$imei=get_arg($arr[$i],"imei");
		$nfc_tag_id=get_arg($arr[$i],"nfc_tag_id");
		$id_number=get_arg($arr[$i],"id_number");
		$student_id=get_arg($arr[$i],"student_id");
		$latitude=get_arg($arr[$i],"latitude");
		$longitude=get_arg($arr[$i],"longitude");
		$address=get_arg($arr[$i],"address");
		$comments=get_arg($arr[$i],"comments");
		$log_dt=get_arg($arr[$i],"log_dt");

		// Validate parameters as normal strings 
		if (
			!validate("AttendanceLog Id",$attendancelog_id,0,11,"int") ||
			!validate("Imei",$imei,0,200,"varchar") ||
			!validate("nfc tag id",$nfc_tag_id,0,45,"varchar") ||
			!validate("Id number",$id_number,0,200,"varchar") ||
			!validate("student_id",$student_id,0,11,"int") ||
			!validate("latitude",$latitude,0,200,"varchar") ||
			!validate("longitude",$longitude,0,200,"varchar") ||
			!validate("address",$address,0,500,"varchar") ||
			!validate("comments",$comments,0,1000,"varchar") ||
			!validate("log_dt",$log_dt,0,200,"varchar") ){
			LOG_MSG('ERROR',"go_sync_to_db(): Validate args failed!");
			return;
		}
		LOG_MSG('DEBUG',"go_sync_to_db(): Validated args");


		$resp=db_attendance_log_insert(	$imei,
										$nfc_tag_id,
										$id_number,
										$student_id,
										$latitude,
										$longitude,
										$address,
										$comments,
										$log_dt);
		if ( $resp['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"db_synclog_insert(): Error while inserting the new row");
			$json['message']="Error while logging. Please contact customer care";
			echo json_encode($json);
			exit;
		}
		$sync_attendancelog_id[$loginsert_count]=$attendancelog_id;
		$loginsert_count++;
	} 

	db_transaction_end();
	$json['status']='OK';
	$json['sync_attendancelog_id']=$sync_attendancelog_id;
	echo json_encode($json);
	LOG_MSG('INFO',"go_sync_to_db(): JSON ".print_r($sync_attendancelog_id,true));

	LOG_MSG('INFO',"go_sync_to_db(): END ");

	exit;
}

function go_iisc_sync_to_db() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_iisc_sync_to_db(): START");
	$json['message']='';
	$json['status']='ERROR';

	$arr=json_decode(get_arg($_POST,"logJSON"),true);
	$logarr=json_decode(get_arg($_POST,"attendancelogJSON"),true);
	LOG_MSG('INFO',"go_iisc_sync_to_db(): START GET=".print_r($arr,true));
	LOG_MSG('INFO',"go_iisc_sync_to_db(): START Attendance_Log GET=".print_r($logarr,true));
	$sync_iisclog_id=array();
	$count = sizeof($arr);

	$loginsert_count=0;
	for ( $i=0;$i<$count;$i++ ) {

		$iisclog_id=get_arg($arr[$i],"iisclog_id");
		$student_id=get_arg($arr[$i],"student_id");
		$reg_no=get_arg($arr[$i],"reg_no");
		$log_dt=get_arg($arr[$i],"log_dt");
		$image=get_arg($arr[$i],"image");

		// Validate parameters as normal strings 
		if (
			!validate("IISCLog Id",$iisclog_id,0,11,"int") ||
			!validate("Student Id",$student_id,0,200,"varchar") ||
			!validate("Reg no",$reg_no,0,200,"varchar")  ){
			LOG_MSG('ERROR',"go_sync_to_db(): Validate args failed!");
			return;
		}
		LOG_MSG('DEBUG',"go_sync_to_db(): Validated args");

		db_transaction_start();

		$iisclog_row =db_iisclog_select("",$student_id,"","","",$log_dt);
		if ( $iisclog_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"do_student_login(): Error fetching the student or no row found for id_number=[".$nfctag_resp[0]['id_number']."] ");
			$json['message']="No student found to sync";
			echo json_encode($json);
			exit;
		}

		if($iisclog_row[0]['NROWS'] == 0 ) {
			$resp=db_iisclog_insert($student_id,$reg_no,$log_dt);
			if ( $resp['STATUS'] !== 'OK' ) {
				LOG_MSG('ERROR',"db_synclog_insert(): Error while inserting the new row");
				$json['message']="Error while logging. Please contact customer care";
				echo json_encode($json);
				exit;
			}
			$sync_iisclog_id[$loginsert_count]=$iisclog_id;
			$loginsert_count++;
		} else {
			$resp=db_sync_iisclog_update($student_id,$log_dt,$image);
			if ( $resp['STATUS'] !== 'OK' ) {
					LOG_MSG('ERROR',"db_sync_studentlog_update(): Error while updating the new row");
					$json['message']="Error while logging. Please contact customer care";
					echo json_encode($json);
					exit;
				}
		}
		db_transaction_end();
	}
	$json['status']='OK';
	$json['sync_iisclog_id']=$sync_iisclog_id;
	echo json_encode($json);
	LOG_MSG('INFO',"go_sync_to_db(): JSON ".print_r($sync_iisclog_id,true));

	LOG_MSG('INFO',"go_sync_to_db(): END ");

	exit;
}

?>
