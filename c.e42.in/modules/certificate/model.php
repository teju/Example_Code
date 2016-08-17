<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_certificate_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_certificate_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$usn=get_arg($_GET,"usn");
	$certificate_id=get_arg($_GET,"certificate_id");
	$name=get_arg($_GET,"name");
	$status=get_arg($_GET,"status");
	LOG_MSG('DEBUG',"do_certificate_list(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("USN",$usn,0,45,"varchar") ||
		!validate("Name",$name,0,200,"varchar") ||
		!validate("Status",$status,0,20,"varchar") ){
		LOG_MSG('ERROR',"do_certificate_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_certificate_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="usn=$usn&name=$name&status=$status";

	$ROW=db_certificate_select(
			$certificate_id,
			$usn,
			$name,
			$status);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Certificates. Please try again later. <br/>");
		return;
	}

	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url=BASEURL."/certificate/search/$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Certificates found! <br />Click on <strong>Add Certificate</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_certificate_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_certificate_view($mode="EDIT") {

	global $ROW, $TEMPLATE, $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_certificate_view(): START");

	$ROW[0]=array();
	$ROW[0]['photo']='no_image.jpg';
	$report_mode=get_arg($_GET,"report_mode");
	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get args
		$certificate_id=get_arg($_GET,"certificate_id");
		$usn=get_arg($_GET,"usn");
		$roll_no=get_arg($_GET,"roll_no");


		// Validate args
		if ( (!validate("USN",$usn,0,45,"varchar")) ) {
			LOG_MSG('ERROR',"go_certificate_view(): VALIDATE ARGS FAILED!");
			if ( $report_mode != '' ) {
				$json_response['message']=$ERROR_MESSAGE;
				echo json_encode($json_response);
				exit;
			}
			return;
		}

		// Search by roll no
		if ( $roll_no != '' ) {
			$tag_row=db_certificatetag_select($roll_no,$usn);
			if ( $tag_row[0]['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error loading the Certificates. Please try again later. <br/>");
				return;
			}
			// No rows found
			if ( $tag_row[0]['NROWS'] == 0 ) {
				add_msg("ERROR","No Results found!");
				LOG_ARR("INFO","ROW",$ROW);
				return;
			}

			$certificate_id=$tag_row[0]['certificate_id'];
		}

		// Get from DB
		$ROW=db_certificate_select($certificate_id,$usn);
		// Error selecting
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Certificate. Please try again later.");
			LOG_ARR('INFO','ROW',$ROW);
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Results found!"); 
			LOG_ARR("INFO","ROW",$ROW);
			return;
		}

		$tag_row=db_certtag_select($certificate_id);
		if ( $tag_row[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Certificates. Please try again later. <br/>");
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

	if ( $report_mode != '' && get_arg($ROW[0],'NROWS') > 0 && $report_mode == 'EMAIL' || $report_mode == 'PDF' || $report_mode == 'CSV' ) { 
		if ( $report_mode == 'CSV' ) {
			for ($i=0;$i<get_arg($ROW[0],'NROWS');$i++) {
				$ROW[$i]['created_date']='"'.date('Y-M-d, h:i a',strtotime($ROW[$i]['created_date'])).'"';
			}
			unset($ROW[0]['IS_SEARCH']);
		}
		$ROW['filename']='modules/certificate/certificate.html';
		$ROW['report_name']='Certificate';
		$ROW['tag']=$tag_row;

		$resp=generate($report_mode,$ROW);
		if ( $report_mode == 'EMAIL' ) {
			$json=array();
			$json_response['status']='ERROR';
			if ( $resp === false ) {
				$json_response['message']=$ERROR_MESSAGE;
				echo json_encode($json_response);
				exit;
			}
			$json_response['status']='OK';
			$json_response['to']=get_arg($_GET,"to");
			echo json_encode($json_response);
		}
		exit;
	}

	if ( $mode == "ADD" ) $tag_row=db_get_list('ARRAY','tag_id,tag_name,tag_value','tTag','is_standard_tag = 1 AND org_id='.$_SESSION['org_id']);

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_certificate_view(): END");
}


/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_certificate_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW,$_GET;

	LOG_MSG('INFO',"do_certificate_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_certificate_save(): START FILES=".print_r($_FILES,true));

	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$certificate_id=get_arg($_POST,"certificate_id");
	$usn=get_arg($_POST,"usn");
	$name=get_arg($_POST,"name");
	$photo=get_arg($_POST,"photo");
	$status=get_arg($_POST,"status");
	$tag_id_arr=get_arg($_POST,"tag_id_arr");
	$tag_name_arr=get_arg($_POST,"tag_name_arr");
	$tag_value_arr=get_arg($_POST,"tag_value_arr");
	$tag_id_count = sizeof($tag_id_arr);
	$tag_name_count = sizeof($tag_name_arr);
	LOG_MSG('DEBUG',"do_certificate_save(): Got args");

	if ( $_FILES['photo']['name'] != '' ) {
		$temp_image=$_FILES['photo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$photo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}

		if ( !upload_image("photo",IMG_DIR."org/".$_SESSION['org_id']."/certificate/$photo") ) {
			add_msg("ERROR","Error while uploading the image");
			return;
		}
	}

	// Validate parameters
	if (
		!validate("USN",$usn,1,45,"varchar") ||
		!validate("Name",$name,1,200,"varchar") ||
		!validate("Photo",$photo,1,200,"varchar") ||
		!validate("Status",$status,1,20,"varchar") ){
		LOG_MSG('ERROR',"do_certificate_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_certificate_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_certificate_insert(
								$usn,
								$name,
								$photo,
								$status);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Certificate <strong>$usn</strong> is already in use. Please enter a different Certificate<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Certificate <strong>$usn</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_certificate_save(): Add args failed!");
				return;
			}
			$certificate_id = $ROW['INSERT_ID'];

			for ( $i=0;$i<$tag_name_count;$i++ ) {
				if (
					!validate("Tag Name",$tag_name_arr[$i],0,200,"varchar") ||
					!validate("Tag Name",$tag_id_arr[$i],0,11,"int") ||
					!validate("Tag Value",$tag_value_arr[$i],0,200,"varchar") ){
					LOG_MSG('ERROR',"do_certificate_save(): Validate args failed!");
					 return;
				} 

				// Select tag row. If the no of rows is 0 then insert into ttag table and also insert into tcerttag with the insert id of inserted tag row. if no of rows is 1 then only insert into tcerttag with fetched tag_id from the form.
				$tag_row=db_tag_select("",$tag_name_arr[$i],$tag_value_arr[$i],0);
				if ( $tag_row[0]['STATUS'] != "OK" ) {
					add_msg("ERROR","There was an error while fetching the Tags. Please try again later. <br/>");
					return;
				} 

				if( $tag_row[0]['NROWS'] == 1 ) {
					$tag_id=$tag_row[0]['tag_id'];
				} else {
					$tag_resp = db_tag_insert($tag_name_arr[$i],$tag_value_arr[$i],0);
					if ( $tag_resp['STATUS'] != "OK" ) {
						add_msg("ERROR","There was an error adding the Tag.");
						LOG_MSG('ERROR',"do_certificate_save(): Add args failed!");
						return;
					}
					$tag_id=$tag_resp['INSERT_ID'];
				}

				$certtag=db_certtag_insert( $certificate_id,
										$tag_id);
				if ( $certtag['STATUS'] != "OK" ) {
					add_msg("ERROR","There was an error adding the Certificate Tag <strong>$certificate_id</strong>.");
					LOG_MSG('ERROR',"do_certificate_save(): Add args failed!");
					return;
				}
			}
			add_msg("SUCCESS","New Certificate added successfully");
			break;
		case "UPDATE":
			// Validate certificate_id
			if (
				!validate("Certificate Id",$certificate_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_certificate_save(): Failed to validate PK");
				return;
			}

			// Step 1: Update 
			$ROW=db_certificate_update(
								$certificate_id,
								$usn,
								$name,
								$photo,
								$status);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the certificate the Tag");
				LOG_MSG('INFO',"There was an error deleting the certtag details certificate_id=[$certificate_id]");
				return;
			}

			// STEP 2: Get the atg_id of the certificate before deleting 
			$certtag_row=db_certtag_select($certificate_id);
			if ( $certtag_row[0]['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error deleting the Tag");
				LOG_MSG('INFO',"There was an error fetching the certtag details certificate_id=[$certificate_id]");
				return;
			}

			// STEP 3: Delete all the rows from tcerttag with certificate_id to be updated. 
			$certtag_resp=db_certtag_delete($certificate_id,"");
			if ( $certtag_resp['STATUS'] != 'OK' ) {
				add_msg("ERROR","There was an error deleting the Tag");
				LOG_MSG('INFO',"There was an error deleting the certtag details certificate_id=[$certificate_id]");
				return;
			}

			// STEP 4: Check for the tags to be updated/inserted if the values are same/different
			for ( $i=0;$i<$tag_name_count;$i++ ) {
				if (
					!validate("Tag Name",$tag_name_arr[$i],0,200,"varchar") ||
					!validate("Tag Name",$tag_id_arr[$i],0,11,"int") ||
					!validate("Tag Value",$tag_value_arr[$i],0,200,"varchar") ){
					LOG_MSG('ERROR',"do_certificate_save(): Validate args failed!");
					 return;
				} 

				// Select tag row. If the no of rows is 0 then insert into ttag table and also 
				// insert into tcerttag with the insert id of inserted tag row. if no of rows is 1 
				// then only insert into tcerttag with fetched tag_id from the form.
				$tag_row=db_tag_select("",$tag_name_arr[$i],$tag_value_arr[$i],0);
				if ( $tag_row[0]['STATUS'] != "OK" ) {
					add_msg("ERROR","There was an error while fetching the Tags. Please try again later. <br/>");
					return;
				} 

				if( $tag_row[0]['NROWS'] == 1 ) {
					$tag_id=$tag_row[0]['tag_id'];
				} else {

					// Get the other details of standard tag other than tag name and value
					$tag_row=db_tag_select("",$tag_name_arr[$i],"",1);
					if ( $tag_row[0]['STATUS'] != "OK" ) {
						add_msg("ERROR","There was an error while fetching the Tags. Please try again later. <br/>");
						return;
					}

					// Insert new tag
					$tag_resp = db_tag_insert($tag_name_arr[$i],
												$tag_value_arr[$i],
												0,
												$tag_row[0]["is_tag_sync"],
												$tag_row[0]["type"],
												$tag_row[0]["is_active"]);
					if ( $tag_resp['STATUS'] != "OK" ) {
						add_msg("ERROR","There was an error adding the Tag.");
						LOG_MSG('ERROR',"do_certificate_save(): Add args failed!");
						return;
					}
					$tag_id=$tag_resp['INSERT_ID'];
				}

				$certtag_resp=db_certtag_insert( $certificate_id,
												$tag_id);
				if ( $certtag_resp['STATUS'] != "OK" ) {
					add_msg("ERROR","There was an error adding the Certificate <strong>$certificate_id</strong>.");
					LOG_MSG('ERROR',"do_certificate_save(): Add args failed!");
					return;
				}
			}


			// STEP 5: Check if the existing tag are used by any other tags. If not delete the Tag
			for ( $i=0;$i<$certtag_row[0]["NROWS"];$i++ ) {
				$del_certtag_row=db_certtag_select("",$certtag_row[$i]["tag_id"],"",0);
				if ( $del_certtag_row[0]['STATUS'] != "OK" ) {
					add_msg("ERROR","There was an error while fetching the Tags. Please try again later. <br/>");
					return;
				}

				if ( $del_certtag_row[0]["NROWS"] < 1 ) {
					$certtag_resp=db_tag_delete($certtag_row[$i]["tag_id"],0);
					if ( $certtag_resp['STATUS'] != 'OK' ) {
						$json_response['message']='There was an error deleting the Tag';
						echo json_encode($json_response);
						LOG_MSG('INFO',"There was an error deleting the certtag details certificate_id=[$certificate_id]");
						exit;
					}
				}
			}

			add_msg("SUCCESS","Certificate updated successfully");
			break;
	}

	// on success show the list
	if ( $mode == 'UPDATE' ) $GO='modify'; else $GO='list';
	LOG_MSG('INFO',"do_certificate_save(): END");
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_certificate_delete_json() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_certificate_delete_json(): START POST=".print_r($_POST,true));

	$certificate_id=get_arg($_POST,"id");

	// Validate certificate_id
	if ( !validate("Certificate Id",$certificate_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	db_transaction_start();
	// Check whether the tag present in the tCertTag table
	$certtag_row=db_certtag_select($certificate_id);
	if ( $certtag_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error deleting the Tag';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the certtag details certificate_id=[$certificate_id]");
		exit;
	}

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_certificate_delete($certificate_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the Certificate';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_certificate_delete_json(): Delete row failed");
		exit;
	}

	// Checking for unused tags
	for ( $i=0;$i<$certtag_row[0]['NROWS'];$i++ ) {
		// Check whether the tag present in the tCertTag table
		$certtag_resp=db_certtag_select('',$certtag_row[$i]['tag_id']);
		if ( $certtag_resp[0]['STATUS'] != 'OK' ) {
			$json_response['message']='There was an error deleting the Tag';
			echo json_encode($json_response);
			LOG_MSG('INFO',"do_certificate_delete_json(): There was an error fetching the certtag details tag_id=[".$certtag_row[$i]['tag_id']."]");
			exit;
		}
		// Delete the tag from tTag if not present
		if ( $certtag_resp[0]['NROWS'] == 0 ) {
			$resp=db_tag_delete($certtag_row[$i]['tag_id']);
			if ( $resp['STATUS'] != 'OK' ) {
				$json_response['message']='There was an error deleting the Tag';
				echo json_encode($json_response);
				LOG_MSG('INFO',"do_certificate_delete_json(): There was an error deleting the tag tag_id=[".$certtag_row[$i]['tag_id']."]");
				exit;
			}
		}
	}
	db_transaction_end();

	$json_response['status']='OK';
	$json_response['id']=$certificate_id;
	//Send json response to response handler 
	echo json_encode($json_response);

	LOG_MSG('INFO',"do_product_delete_json(): Certificate <strong>$certificate_id</strong> has been removed.");
	LOG_MSG('INFO',"do_certificate_delete_json(): END");

	exit;
}

/**********************************************************************/
/*                           VERIFY CERTIFICATE                       */
/**********************************************************************/
function get_certificate_json() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	LOG_MSG('INFO',"get_certificate_json(): START");

	if ( !isset($_SESSION['org_id']) ) $_SESSION['org_id']=33;

	// Get args
	$nfc_tag_id=get_arg($_GET,"nfc_tag_id");
	$roll_no=get_arg($_GET,"roll_no");
	$usn=get_arg($_GET,"usn");
	$certificate_id=get_arg($_GET,"certificate_id");

	if ( $nfc_tag_id == '' && $roll_no == '' ) {
		$json_response['message']="Invalid Certificate";
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_certificate_json(): Invalid Certificate nfc_tag_id=[$nfc_tag_id]");
		exit;
	}

	// Search by roll no
	if ( $roll_no != '' ) {

		// Validate args
		if(!validate("Roll no",$roll_no,1,45,"varchar")) {
			LOG_MSG('ERROR',"get_certificate_json(): VALIDATE ARGS FAILED!");
			return;
		}

		$tag_row=db_certificatetag_select($roll_no,$usn);
		if ( $tag_row[0]['STATUS'] != "OK" ) {
			$json_response['message']="There was an error verifying the certificate";
			echo json_encode($json_response);
			LOG_MSG('ERROR',"get_certificate_json(): Error while fetching the Certificate for roll_no=[$roll_no]");
			exit;
		}

		if ( $tag_row[0]['NROWS'] < 1 ) {
			$json_response['message']="Invalid Certificate";
			echo json_encode($json_response);
			LOG_MSG('ERROR',"get_certificate_json(): Invalid Certificate roll_no=[$roll_no]");
			exit;
		}

		$certificate_id=$tag_row[0]['certificate_id'];
		$ROW=db_certificate_select($certificate_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
				$json_response['message']="There was an error verifying the certificate";
				echo json_encode($json_response);
				LOG_MSG('ERROR',"get_certificate_json(): Error while fetching the Certificate for roll_no=[$roll_no]");
				exit;
		}

		if ( $ROW[0]['NROWS'] < 1 ) {
				$json_response['message']="Invalid Certificate";
				echo json_encode($json_response);
				LOG_MSG('ERROR',"get_certificate_json(): Invalid Certificate roll_no=[$roll_no]");
				exit;
		}
		$certificate_id=$ROW[0]['certificate_id'];
	}

	// Get from DB
	if ( $nfc_tag_id != '' ) {

		// Validate args
		if( !validate("NFC Tag ID ",$nfc_tag_id,1,45,"varchar") ) {
			LOG_MSG('ERROR',"get_certificate_json(): VALIDATE ARGS FAILED!");
			return;
		}

		$ROW=db_certificate_select("","","","",$nfc_tag_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			$json_response['message']="There was an error verifying the certificate";
			echo json_encode($json_response);
			LOG_MSG('ERROR',"get_certificate_json(): Error while fetching the Certificate for nfc_tag_id=[$nfc_tag_id]");
			exit;
		}

		if ( $ROW[0]['NROWS'] < 1 ) {
			$json_response['message']="Invalid Certificate";
			echo json_encode($json_response);
			LOG_MSG('ERROR',"get_certificate_json(): Invalid Certificate nfc_tag_id=[$nfc_tag_id]");
			exit;
		}
		$certificate_id=$ROW[0]['certificate_id'];
	}

	// Get cert tag
	$certtag_row=db_certtag_select($ROW[0]['certificate_id']);
	if ( $certtag_row[0]['STATUS'] != "OK" ) {
		$json_response['message']="There was an error verifying the certificate";
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_certificate_json(): Error while fetching the Certificate Tag");
		exit;
	}

	$json_response['org_id']=$ROW[0]['org_id'];
	$json_response['org_name']=$ROW[0]['org_name'];
	$json_response['org_logo']=$ROW[0]['org_logo'];

	$json_response['nfc_tag_id']=$ROW[0]['nfc_tag_id'];
	$json_response['usn']=$ROW[0]['usn'];
	$json_response['name']=$ROW[0]['name'];
	$json_response['photo']=$ROW[0]['photo'];

	// Cert Tags
	$json_response['tag_rows']=$certtag_row[0]['NROWS'];
	for ( $i=0;$i<$certtag_row[0]['NROWS'];$i++ ) {
		$json_response['tag_name'][$i]=$certtag_row[$i]['tag_name'];
		$json_response['tag_value'][$i]=$certtag_row[$i]['tag_value'];
	}

	$json_response['status']='OK';
	$json_response['certificate_id']=$ROW[0]['certificate_id'];
	echo json_encode($json_response);// Send json response to response handler

	LOG_MSG('INFO',"get_certificate_json(): Certificate for $nfc_tag_id fetched successfully.".print_r($json_response,true));
	LOG_MSG('INFO',"get_certificate_json(): END");
	exit;
}

/**********************************************************************/
/*                           REQUEST REPORT                           */
/**********************************************************************/
function do_request_report() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	LOG_MSG('INFO',"do_request_report(): START");

	if ( !isset($_SESSION['org_id']) ) $_SESSION['org_id']=33;

	// Get args
	$nfc_tag_id=get_arg($_POST,"nfc_tag_id");
	$roll_no=get_arg($_POST,"roll_no");
	$imei=get_arg($_POST,"imei");

	if ( $nfc_tag_id == '' && $roll_no == '' ) {
		$json_response['message']="Invalid Certificate";
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_request_report(): Invalid Certificate nfc_tag_id=[$nfc_tag_id]");
		exit;
	}

	//Get cert tag
	$userimei_row=db_userimei_select($imei);
	if ( $userimei_row[0]['STATUS'] != "OK") {
		$json_response['message']="There was an error fetching user with imei";
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_request_report(): Error while fetching the Certificate Tag");
		exit;
	}

	if( $userimei_row[0]['NROWS'] != 0 ) {
		$user_row=db_user_select($userimei_row[0]['user_id']);
		if ( $user_row[0]['STATUS'] != "OK") {
			$json_response['message']="There was an error verifying the certificate";
			echo json_encode($json_response);
			LOG_MSG('ERROR',"do_request_report(): Error while fetching the Certificate Tag");
			exit;
		}
		$json_response['email_id']=$user_row[0]['email_id'];
		$json_response['is_active']=$user_row[0]['is_active'];

		if ( $user_row[0]['is_active'] == 0 ) {

			$cust_otp=rand(100000,999999);

			// Store otp along with time 10 minutes from now 
			// to make sure the otp is valid for only 10 minutes
			$otp=$cust_otp.strtotime("+ 10 minute");

			$resp=db_user_update($user_row[0]['user_id'],"","","","",$otp);
			if ( $resp['STATUS'] != "OK" ) {
				$json_response['message']='There was an error reseting the password. Please try later';
				echo json_encode($json_response);
				LOG_MSG('ERROR',"do_app_user_register(): Delete row failed");
				exit;
			}

			/******************************************************************/
			/* SEND EMAIL                                                     */
			/******************************************************************/
			$subject=" Account - $cust_otp is your verification code for secure access";
			ob_start();
			include('emails/forgot_pass.html');
			$message = ob_get_contents();
			ob_get_clean();

			if (!send_email($user_row[0]['email_id'],SUPPORT_EMAIL_STR,'',SUPPORT_EMAIL,$subject,$message)) {
				LOG_MSG('ERROR',"do_app_user_register(): Failed to send email:\n subject=[$subject]");
				$json_response['message']="There was an error processing your request. Please try after sometime";
				echo json_encode($json_response);
				exit;
			}
		}
	}

	$json_response['status']='OK';
	$json_response['userimei_rows']=$userimei_row[0]['NROWS'];
	echo json_encode($json_response);// Send json response to response handler

	LOG_MSG('INFO',"do_request_report(): Certificate for $nfc_tag_id fetched successfully.".print_r($json_response,true));
	LOG_MSG('INFO',"do_request_report(): END");
	exit;
}

function go_app_send_email() {

	global $ROW, $TEMPLATE, $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_app_send_email(): START");

	$ROW[0]=array();
	$ROW[0]['photo']='no_image.jpg';
	$report_mode=get_arg($_POST,"report_mode");

	// Get args
	$certificate_id=get_arg($_POST,"certificate_id");
	$org_id=get_arg($_POST,"org_id");

	// Validate args
	if ( (!validate("certificate_id",$certificate_id,0,11,"int")) ) {
		LOG_MSG('ERROR',"go_app_send_email(): VALIDATE ARGS FAILED!");
		if ( $report_mode != '' ) {
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			exit;
		}
		return;
	}

	// Get from DB
	$ROW=db_certificate_select($certificate_id);
	// Error selecting
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Certificate. Please try again later.");
		LOG_ARR('INFO','ROW',$ROW);
		return;
	}

	// No rows found
	if ( $ROW[0]['NROWS'] == 0 ) {
		add_msg("ERROR","No Results found!"); 
		LOG_ARR("INFO","ROW",$ROW);
		return;
	}

	$tag_row=db_certtag_select($certificate_id);
	if ( $tag_row[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Certificates. Please try again later. <br/>");
		return;
	}

	if ( $report_mode != '' && get_arg($ROW[0],'NROWS') > 0 && $report_mode == 'EMAIL' ) { 

		$ROW['filename']='modules/certificate/certificate.html';
		$ROW['report_name']='Certificate';
		$ROW['tag']=$tag_row;

		$_GET['from']=get_arg($_POST,"from");
		$_GET['to']=get_arg($_POST,"to");
		$_GET['subject']=get_arg($_POST,"subject");
		$_GET['content_type']=get_arg($_POST,"content_type");
		$_GET['message']=get_arg($_POST,"message");
		$resp=generate($report_mode,$ROW);
		if ( $report_mode == 'EMAIL' ) {
			$json=array();
			$json_response['status']='ERROR';
			if ( $resp === false ) {
				$json_response['message']=$ERROR_MESSAGE;
				echo json_encode($json_response);
				exit;
			}
			$json_response['status']='OK';
			$json_response['to']=get_arg($_POST,"to");
			echo json_encode($json_response);
		}
		exit;
	}

	if ( $mode == "ADD" ) $tag_row=db_get_list('ARRAY','tag_id,tag_name,tag_value','tTag','is_standard_tag = 1 AND org_id='.$_SESSION['org_id']);

	LOG_MSG('INFO',"go_app_send_email(): JSON RESPONSE ".print_r($json_response,true));
	LOG_MSG('INFO',"go_app_send_email(): END");
}



?>

