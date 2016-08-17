<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_order_make_payment() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;

	$_SESSION["payment"]=array();
	LOG_MSG('INFO',"go_order_make_payment(): START GET=".print_r($_GET,true));

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."make_payment.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_order_make_payment(): END");

}


function do_order_make_payment() {

	global $ROW;

	LOG_MSG('INFO',"do_order_make_payment(): ^^^^^^^^^^^^^^^^^^^^^^^START^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ ");

	$name=get_arg($_POST,'name');
	$email_id=get_arg($_POST,'email_id');
	$mobile=get_arg($_POST,'mobile');
	$amount=get_arg($_POST,'amount');
	$pmt_type=get_arg($_POST,'pmt_type');

	// Update the session as well
	$_SESSION['payment']['name']=$name;
	$_SESSION['payment']['email_id']=$email_id;
	$_SESSION['payment']['mobile']=$mobile;
	$_SESSION['payment']['amount']=$amount;

	// Check fields
	if (
		!validate("Your Name",$name,3,50,'varchar') ||
		!validate("Email ID",$email_id,5,50,'EMAIL') ||
		!validate("Mobile",$mobile,10,18,'bigint') ||
		!validate("Amount",$amount,0,10,'decimal') ||
		!validate("Payment Type",$pmt_type,0,2,'varchar') ) {
			go_order_make_payment();
			return;
	}
	LOG_MSG('DEBUG',"do_order_make_payment(): Validated fields");

	/*******************************************************************/
	/* START TRANSACTION                                               */
	/*******************************************************************/
	db_transaction_start();

	// STEP 1: INSERT ORDER INTO THE DB 
	$resp=db_order_insert(
							$name,
							$email_id,
							$mobile,
							$amount,
							"PENDING",
							$pmt_type,
							"INVALID");
	if ( $resp['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_order_make_payment(): Error inserting order: ".print_r($resp,true));
		add_msg('ERROR',"do_order_make_payment(): There was an error processing your order. Please contact customer care.");
		go_order_make_payment();
		return;
	}

	$order_id=$resp['INSERT_ID'];

	LOG_MSG('INFO',"do_order_make_payment(): Inserted order with ID: [$order_id]");

	/*******************************************************************/
	/* END TRANSACTION                                                 */
	/*******************************************************************/
	db_transaction_end();

	$order_id_str="E42".date('ymd').$order_id;

	// Prepare the gateway request
	$ORDER=array();
	$ORDER['order_id_str']=$order_id_str;
	$ORDER['name']=$name;
	$ORDER['email_id']=$email_id;
	$ORDER['mobile']=$mobile;
	$ORDER['amount']=$amount;
	$ORDER['pmt_type']=$pmt_type;

	include('gateways/'.strtolower('payu.php'));

	$GATEWAY=GATEWAY_prepare_request($ORDER);
	if ( $GATEWAY === false ) {
		LOG_MSG('ERROR','do_order_make_payment(): The payment gateway function GATEWAY_prepare_request returned an error');
		add_msg('ERROR',"do_order_make_payment(): There was an error processing your order. Please contact customer care.");
		go_order_make_payment();
		return;
	}

	/******************************************************************/
	/* 5. REDIRECT TO PAYMENT GATEWAY                                 */
	/******************************************************************/
	LOG_ARR('INFO',"do_order_make_payment(): ================= SENDING ARRAY TO GATEWAY =================== ",$GATEWAY);
	include('pmt-gateway-redirect.html');

	LOG_MSG('INFO',"do_order_make_payment(): END ");
}


function go_order_payment_confirm() {

	LOG_MSG('INFO',"go_order_payment_confirm(): START ");

	// Check response array
	LOG_ARR('INFO','Recived GET',$_GET);
	LOG_ARR('INFO','Recived POST',$_POST);

	include('gateways/payu.php');

	// We need order_id, payment_type and comments from the system.
	$RESPONSE=GATEWAY_process_response();
	LOG_ARR('INFO','RESPONSE',$RESPONSE);

	// BEFORE UPDATING THE ORDER, CHANGE 'PROCESING' STATUS TO 'PROCURE' IF aom_enabled
	// if (shopsetting_isset('AOM_enabled') && $RESPONSE['order_status'] == 'PROCESSING' ) $RESPONSE['order_status']='NEW';

	// STEP 1: UPDATE PAYMENT STATUS IN tOrder TABLE (for both success and failure)
	if (isset($RESPONSE['order_id']) && $RESPONSE['pmt_type'] && $RESPONSE['comments']) {
		$resp=db_order_update(	$RESPONSE['order_id'],
								$RESPONSE['pmt_status'],
								$RESPONSE['pmt_type'],
								$RESPONSE['order_status'],
								$RESPONSE['comments'],
								date("Y-m-d"));
		if ( $resp['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"go_order_payment_confirm(): Error updating the PAID order [".$RESPONSE['order_id']."] with payment type [".$RESPONSE['payment_type']."].");
			add_msg('ERROR',"There was an error updating your order payment status.
							<br/>Please contact us on ".SUPPORT_EMAIL);
			go_order_make_payment();
			return;
		}
	}

	// STEP 2: FAILED?
	if ( $RESPONSE['status'] != 'OK' ) { 
		go_order_make_payment();
		return;
	}

	// STEP 3: SUCCESS - SHOW ORDER CONFIRMATION
	$_GET['order_id']=$RESPONSE['order_id'];

	include('confirm.html');

	LOG_MSG('INFO',"go_order_payment_confirm(): END ");
}


/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_order_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_order_list(): START GET=".print_r($_GET,true));

	if ( !is_superuser() ) {
			add_msg('ERROR','Sorry! You do not have sufficient privileges');
			return;
	}

	// Do we have a search string?
	// Get all the args from $_GET
	$name=get_arg($_GET,"name");
	$logo=get_arg($_GET,"logo");
	LOG_MSG('DEBUG',"do_order_list(): Got args");

	// Validate parameters as normal strings 
	if (
		!validate("Name",$name,0,200,"varchar") ||
		!validate("logo",$logo,0,100,"varchar") ){
		LOG_MSG('ERROR',"do_order_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_order_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="
		name=$name&
		logo=$logo";


	$ROW=db_order_select(
		"",
			$name,
			$logo);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the orders. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=order&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No orders found! <br />Click on <strong>Add order</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_order_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_order_view($mode="EDIT") {

	global $ROW, $TEMPLATE, $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_order_view(): START");

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get args
		$order_id=get_arg($_GET,"order_id");

		// Validate args
		if ( !validate("Org ID",$order_id,1,11,"int") ) {
			LOG_MSG('ERROR',"go_order_view(): VALIDATE ARGS FAILED!");
			return;
		}

		$_SESSION['order_id']=$order_id;

		// Get from DB
		$ROW=db_order_select($order_id);
		// Error selecting
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the order. Please try again later.");
			LOG_ARR('INFO','ROW',$ROW);
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Results found!"); 
			LOG_ARR("INFO","ROW",$ROW);
			return;
		}

	}

	$disabled="";
	// Setup display parameters
	switch($mode) {
		case "ADD":
				if ( !isset($ROW[0]) ) {
					$ROW[0]=array();
					$ROW[0]['logo']='no_image.jpg';
				}
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

	// Initialize orderanization
	if ( $mode == 'EDIT' && !init_order($order_id) ) {
		add_msg("ERROR","Invalid orderanization. Please contact customer care");
		show_msgs();
		return;
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."record.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_order_view(): END");
}


/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_order_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW,$_GET;

	LOG_MSG('INFO',"do_order_save(): START (mode=$mode) POST=".print_r($_POST,true));
	LOG_MSG('INFO',"do_order_save(): START FILES=".print_r($_FILES,true));

	if ( $mode == 'ADD' && !is_superuser() ) {
		add_msg('ERROR','Sorry! You do not have sufficient privileges');
		return;
	}

	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$order_id=get_arg($_POST,"order_id");
	$name=get_arg($_POST,"name");
	$logo=get_arg($_POST,"logo");
	LOG_MSG('DEBUG',"do_order_save(): Got args");

	if ( $_FILES['logo']['name'] != '' ) {
		$temp_image=$_FILES['logo']['name'];
		/**********************************************************************/
		/*  Clean up the image name                                           */
		/*  This name will be used for upload/updating as well                */
		/**********************************************************************/
		if( !$logo=generate_imagename( $temp_image ) ) { 
			add_msg("ERROR","Invalid file name/extension");
			return;
		}

	}



	// Validate parameters
	if (
		!validate("Name",$name,1,200,"varchar")	){
		LOG_MSG('ERROR',"do_order_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_order_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_order_insert(
								$name,
								$logo);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The order <strong>$name</strong> is already in use. Please enter a different order<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the order <strong>$name</strong>.");
						break;
				}
				LOG_MSG('ERROR',"do_order_save(): Add args failed!");
				return;
			}
			$order_id=$ROW['INSERT_ID'];
			$_GET['order_id']=$order_id;

			// Create the default directories for the orderanization
			recurse_copy_dir(IMG_DIR."default",IMG_DIR."order/$order_id");

			add_msg("SUCCESS","New order added successfully");
			break;
		case "UPDATE":
			// Validate order_id
			if (
				!validate("order Id",$order_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_order_save(): Failed to validate PK");
				return;
			}

			$ROW=db_order_update(
								$order_id,
								$name,
								$logo);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the order <strong>$order_id</strong> .");
				return;
			}
			add_msg("SUCCESS","order updated successfully");
			break;
	}

	// Upload the image after inserting as we need the order_id
	if ( $_FILES['logo']['name'] != '' && !upload_image("logo",IMG_DIR."order/$order_id/$logo") ) {
		add_msg("ERROR","Error while uploading the image");
		return;
	}

	if ( !is_superuser() ) {
		$GO='modify';
		$_GET['order_id']=$order_id;
		return;
	} else {
		// on success show the list
		$GO='list';
	}

	LOG_MSG('INFO',"do_order_save(): END");
}






/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_order_delete_json() {


	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_order_delete_json(): START POST=".print_r($_POST,true));

	$order_id=get_arg($_POST,"id");

	// Validate order_id
	if ( !validate("order Id",$order_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	db_transaction_start();

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_order_delete($order_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the order';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_order_delete_json(): Delete row failed");
		exit;
	}

	// Since the user table is not a child for the order, we have to manually delete the users 
	$ROW=db_user_delete('',$order_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the user';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_order_delete_json(): Delete row failed");
		exit;
	}

	// Remove directories for the orderanization
	recurse_remove_dir(IMG_DIR."order/$order_id");

	db_transaction_end();

	$json_response['status']='OK';
	$json_response['id']=$order_id;
	//Send json response to response handler 
	echo json_encode($json_response);

	LOG_MSG('INFO',"do_order_delete_json(): order <strong>$order_id</strong> has been removed.");
	LOG_MSG('INFO',"do_order_delete_json(): END");

	exit;
}


?>
