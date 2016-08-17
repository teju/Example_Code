<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_student_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	
	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_student_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$id_number=get_arg($_GET,"id_number");
	$student_photo=get_arg($_GET,"student_photo");
	$address=get_arg($_GET,"address");
	$phone=get_arg($_GET,"phone");
	$guardian_name=get_arg($_GET,"guardian_name");
	$email_id=get_arg($_GET,"email_id");
	$guardian_photo=get_arg($_GET,"guardian_photo");
	$client_id=get_arg($_GET,"client_id");
	$vehicle_id=get_arg($_GET,"vehicle_id");
	$is_active=get_arg($_GET,"is_active");
	$exp_dt=get_arg($_GET,"exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_GET,"exp_dt"))) : get_arg($_GET,"exp_dt");
	LOG_MSG('DEBUG',"do_student_list(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Phone No",$phone,0,18,"varchar") ||
		!validate("Address",$address,0,500,"varchar")){
		LOG_MSG('ERROR',"do_student_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_student_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="name=$name&phone=$phone&address=$address&";


	$ROW=db_student_select(
		"",
			$name,
			$student_photo,
			$id_number,
			$address,
			$phone,
			$guardian_name,
			$email_id,
			$guardian_photo,
			$client_id,
			$vehicle_id,
			$is_active,
			$exp_dt);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the student. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=student&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');
		
	if ( ($row_client=db_get_list('ARRAY','name,client_id','tClient','travel_id='.TRAVEL_ID)) === false ) return;
	if ( ($row_vehicle=db_get_list('ARRAY','reg_no,vehicle_id','tVehicle','travel_id='.TRAVEL_ID)) === false ) return;



	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No students found! <br />Click on <strong>Add student</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_student_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_student_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_student_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the for
	$ROW[0]['student_photo']='no_image.jpg';
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the student ID
		$student_id=get_arg($_GET,"student_id");

		// Validate the ID
		if (
			!validate("student Id",$student_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_student_view(): Invalid student ID [$student_id]!");
			return;
		}

		// Get from DB
		$ROW=db_student_select($student_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the student. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No student found! <br />Click on <strong>Add student</strong> to create a one.<br /><br />"); 
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
	if ( $mode != "ADD" ) {
		$location_group_row = db_group_select($student_id);
		if( $location_group_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows found for id number ");
			$json['message']="Invalid Card";
			echo json_encode($json);
			exit;
		}
	}
	if ( ($row_client=db_get_list('ARRAY','name,client_id','tClient','travel_id='.TRAVEL_ID)) === false ) return;
	if ( ($row_vehicle=db_get_list('ARRAY','reg_no,vehicle_id','tVehicle','travel_id='.TRAVEL_ID)) === false ) return;
    if ( ($row_group_location=db_get_list('ARRAY','group_name,group_id','tGroup','travel_id='.TRAVEL_ID)) === false ) return;

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_student_view(): END");
}

/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_student_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_student_save(): START (mode=$mode) POST=".print_r($_POST,true));
	//LOG_MSG('INFO',"do_student_save(): START (mode=$mode) _FILES=".print_r($_FILES,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$name=get_arg($_POST,"name");
	$student_photo=get_arg($_POST,"student_photo");
	$id_number=get_arg($_POST,"id_number");
	$address=get_arg($_POST,"address");
	$phone=get_arg($_POST,"phone");
	$guardian_name=get_arg($_POST,"guardian_name");
	$email_id=get_arg($_POST,"email_id");
	$guardian_photo=get_arg($_POST,"guardian_photo");
	$client_id=get_arg($_POST,"client_id");
	$vehicle_id=get_arg($_POST,"vehicle_id");
	$is_active=get_arg($_POST,"is_active");
	$exp_dt=get_arg($_POST,"exp_dt") != '' ? date('Y-m-d',strtotime(get_arg($_POST,"exp_dt"))) : get_arg($_POST,"exp_dt");

	LOG_MSG('DEBUG',"do_student_save(): Got args");


	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar") ||
	//	!validate("Photo",$photo,0,200,"varchar") ||
		!validate("Phone No",$phone,0,18,"bigint") ||
		!validate("Address",$address,0,500,"varchar")){
		LOG_MSG('ERROR',"do_student_save(): Validate args failed!");
		 return;
	} 
	if ( $_FILES['student_photo']['name'] != '' ) {
		$temp_image=$_FILES['student_photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$student_photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}
	}
	/*if ( $_FILES['guardian_photo']['name'] != '' ) {
		$temp_image=$_FILES['guardian_photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		/*if( !$guardian_photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}
	}*/
	LOG_MSG('DEBUG',"do_student_save(): Validated args");

	db_transaction_start();
	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_student_insert(
								$name,
								$student_photo,
								$id_number,
								$address,
								$phone,
								$guardian_name,
								$email_id,
								$guardian_photo,
								$client_id,
								$vehicle_id,
								$exp_dt,
								$is_active);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The student <strong>$name</strong> is already in use. Please enter a different student<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the student <strong></strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_student_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New student <strong>$name</strong> added successfully");
			if ( $ROW['NROWS'] == 1 && !app_sync('tStudent',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
				return;
			}
			break;
		case "UPDATE":
			$student_id=get_arg($_POST,"student_id");
			// Validate student_id
			if (
				!validate("student Id",$student_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_student_save(): Failed to validate PK");
				return;
			}

			$ROW=db_student_update(
								$student_id,
								$name,
								$student_photo,
								$id_number,
								$address,
								$phone,
								$guardian_name,
								$email_id,
								$guardian_photo,
								$client_id,
								$vehicle_id,
								$exp_dt,
								$is_active);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the student <strong>$student_id</strong> .");
				return;
			}
			add_msg("SUCCESS","student <strong>$student_id</strong> updated successfully");
			if( $ROW['NROWS'] == 1 && !app_sync('tStudent',$student_id,'U',TRAVEL_ID )) {
				return;
			}
			break;
	}
	if ( $_FILES['student_photo']['name'] != '' ) {
		$temp_image=$_FILES['student_photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$student_photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}
	}
	if ( $_FILES['student_photo']['name'] != '' && !upload_image("student_photo",IMG_DIR."student/$student_photo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}
/*	if ( $_FILES['guardian_photo']['name'] != '' ) {
		$temp_image=$_FILES['guardian_photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
	/*	if( !$guardian_photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}
	}
	if ( $_FILES['guardian_photo']['name'] != '' && !upload_image("guardian_photo",IMG_DIR."guardian/$guardian_photo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}*/
	db_transaction_end();

	// on success show the list
	$GO="list";
	LOG_MSG('INFO',"do_student_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_student_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;

	LOG_MSG('INFO',"do_student_delete(): START POST=".print_r($_POST,true));

	$student_id=get_arg($_POST,"student_id");

	// Validate student_id
	if (
		!validate("student Id",$student_id,1,11,"int")
	) { return; }

	db_transaction_start();
	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_student_delete($student_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		if ($ROW["SQL_ERROR_CODE"] == 1451 ) {
			add_msg("ERROR","The student <strong>$student_id</strong> is currently used by other entities in the system and cannot be removed.");
		} else {
			add_msg("ERROR","There was an error removing the student <strong>$student_id</strong>");
		}
		return;
	}
	if( $ROW['NROWS'] == 1 && !app_sync('tStudent',$student_id,'D',TRAVEL_ID)) {
		return;
	}
	db_transaction_end();

	add_msg("SUCCESS","student <strong>$student_id</strong> has been removed.");
	LOG_MSG('INFO',"do_student_delete(): END");

}

function do_student_image_delete() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;
	$json['message']='';
	$json['status']='ERROR';

	LOG_MSG('INFO',"do_student_image_delete(): START POST=".print_r($_POST,true));

	$student_id=get_arg($_POST,"student_id");
	$student_photo=get_arg($_POST,"student_photo");

	// Validate student_id
	if (!validate("student Id",$student_id,1,11,"int")) {
		$json['message']="Error while validating.";
		echo json_encode($json);
		exit; 
	}

	db_transaction_start();

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_student_image_update($student_id);
	LOG_MSG('INFO',"do_student_image_delete(): IMAGE=".print_r($ROW,true));
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		$json['message']="Error while deleting. Please contact customer care";
		echo json_encode($json);
		exit;
	}

	if ( !unlink(IMG_DIR.'student/'.$student_photo) ) {
		$json['message']="Error while deleting image from folder. Please contact customer care";
		echo json_encode($json);
		exit;
	}

	db_transaction_end();


	$json['status']="OK";
	$json['message']="success";
	echo json_encode($json);
	LOG_MSG('INFO',"do_group_location_save() : Json response ".print_r($json,true));
	LOG_MSG('INFO',"do_group_location_save(): END");
	exit;
}

function do_group_location_save() {


	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW, $ERROR_MESSAGE;
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"do_group_location_save(): START POST=".print_r($_POST,true));

	$group_id=get_arg($_POST,"group_id");
	$student_id=get_arg($_POST,"student_id");

	// Validate group_id
	if ( !validate("group Id",$group_id,1,11,"int") ||
		 !validate("Student Id",$student_id,0,11,"int") ) {
		LOG_MSG('DEBUG',"do_group_location_save(): VALIDATE ARGS FAILED");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	$ROW=db_group_location_insert($student_id,$group_id);
	if ( $ROW['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_group_location_save(): Error while inserting the new row");
		$json['message']="Error while inserting. Please contact customer care";
		echo json_encode($json);
		exit;
	}
	if ( $ROW['NROWS'] == 1 && !app_sync('tStudentGroup',$ROW['INSERT_ID'],'A',TRAVEL_ID) ) {
		return;
	}
	$location_group_row = db_group_select($student_id,$group_id);
	if( $location_group_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows found for id number ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	if( $location_group_row[0]['NROWS'] > 0 ) {
		$json['group_name']=$location_group_row[0]['group_name'];
	}

	$json['status']="OK";
	$json['message']="success";
	$json['group_id']=$group_id;
	echo json_encode($json);
	LOG_MSG('INFO',"do_group_location_save() : Json response ".print_r($json,true));
	LOG_MSG('INFO',"do_group_location_save(): END");
	exit;

}
function do_group_location_delete() {

	if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ROW;
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"do_group_location_delete(): START POST=".print_r($_POST,true));

	$group_id=get_arg($_POST,"group_id");

	// Validate location_id
	if (
		!validate("group_id",$group_id,1,11,"int")
	) { return; }

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_group_location_delete($group_id);
	if ( $ROW['STATUS'] != "OK" || $ROW['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_location_imei_save(): Error while deleting the row");
		$json['message']="Error while deleting. Please contact customer care";
		echo json_encode($json);
		exit;
	}
	if ( $ROW['NROWS'] == 1 && !app_sync('tStudentGroup',$group_id,'D',TRAVEL_ID) ) {
		return;
	}
	$json['status']="OK";
	$json['message']="successfully deleted";
	echo json_encode($json);
	exit;	LOG_MSG('INFO',"do_group_location_delete(): END");

}
?>
