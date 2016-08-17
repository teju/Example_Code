<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_wallet_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_wallet_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$user_id=get_arg($_SESSION,"user_id");
	$created_dt=get_arg($_GET,"created_dt");
	$description=get_arg($_GET,"description");
	$transaction_type=get_arg($_GET,"transaction_type");
	$amount=get_arg($_GET,"amount");
	$balance_amount=get_arg($_GET,"balance_amount");
	LOG_MSG('DEBUG',"do_wallet_list(): Got args");


	// Validate parameters as normal strings 
	if (
		!validate("User Id",$user_id,0,11,"varchar") ||
		!validate("Created Dt",$created_dt,0,30,"varchar") ||
		!validate("Description",$description,0,500,"varchar") ||
		!validate("Transaction Type",$transaction_type,0,"'TOPUP','PURCHASE'","enum") ||
		!validate("Amount",$amount,0,10,"varchar") ||
		!validate("Balance Amount",$balance_amount,0,10,"varchar") 	){
		LOG_MSG('ERROR',"do_wallet_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"do_wallet_list(): Validated args");

	// Rebuild search string for future pages
	$search_str="
		user_id=$user_id&
		created_dt=$created_dt&
		description=$description&
		transaction_type=$transaction_type&
		amount=$amount&
		balance_amount=$balance_amount	";


	$ROW=db_wallet_select(
		"",
			$user_id,
			$created_dt,
			$description,
			$transaction_type,
			$amount,
			$balance_amount	);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Wallets. Please try again later. <br/>");
		return;
	}


	// PAGE & URL Details
	$page_arr=get_page_params($ROW[0]["NROWS"]);
	$url="index.php?mod=admin&ent=wallet&go=list&$search_str";
	$search_url='';
	$order_url='';
	$ordert_url='';
	$page_url='&page='.get_arg($page_arr,'page');


	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') && get_arg($page_arr,'page_row_count') <= 0 ) {
		add_msg("NOTICE","No Wallets found! <br />Click on <strong>Add Wallet</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_wallet_list(): END");
}




/**********************************************************************/
/*                      SELECT SINGLE record                         */
/**********************************************************************/
// mode: which mode should the form be displayed? 
//		 EDIT/ADD/DELETE/SEARCH
//		 default mode = EDIT
function go_wallet_view($mode="EDIT") {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_wallet_view(): START ROW=".print_r($ROW,true));

	// Don't load for add mode or when reloading the form
	if ( $mode != "ADD" && (!isset($ROW[0]) || get_arg($ROW[0],'STATUS') !== 'RELOAD') ) {

		// Get the Wallet ID
		$wallet_id=get_arg($_GET,"wallet_id");

		// Validate the ID
		if (
			!validate("Wallet Id",$wallet_id,1,11,"int")
		) { 
			LOG_MSG('ERROR',"go_wallet_view(): Invalid Wallet ID [$wallet_id]!");
			return;
		}

		// Get from DB
		$ROW=db_wallet_select($wallet_id);
		if ( $ROW[0]['STATUS'] != "OK" ) {
			add_msg("ERROR","There was an error loading the Wallet. Please try again later. <br/>");
			return;
		}
		// No rows found
		if ( $ROW[0]['NROWS'] == 0 ) {
			add_msg("ERROR","No Wallets found! <br />Click on <strong>Add Wallet</strong> to create a one.<br /><br />"); 
			return;
		}
	}

	$disabled="";
	// Setup display parameters
	switch($mode) {
		case "ADD":
				if ( !isset($ROW[0]) ) $ROW[0]=array();
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

	LOG_MSG('INFO',"go_wallet_view(): END");
}







/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_wallet_save($mode="ADD") {

	if(!has_user_permission(__FUNCTION__,$mode)) return;
	global $GO,$ROW;

	LOG_MSG('INFO',"do_wallet_save(): START (mode=$mode) POST=".print_r($_POST,true));
	if ($mode == 'UPDATE') { $GO='modify'; } else { $GO='new'; }

	// Get all the args from $_POST
	$wallet_id=get_arg($_POST,"wallet_id");
	$user_id=$_SESSION["user_id"];
	$created_dt=get_arg($_POST,"created_dt");
	$description=get_arg($_POST,"description");
	$transaction_type=get_arg($_POST,"transaction_type");
	$amount=get_arg($_POST,"amount");
	$balance_amount=get_arg($_POST,"balance_amount");
	LOG_MSG('DEBUG',"do_wallet_save(): Got args");


	// Validate parameters
	if (
		!validate("User Id",$user_id,1,11,"int") ||
		!validate("Created Dt",$created_dt,1,30,"varchar") ||
		!validate("Description",$description,0,500,"varchar") ||
		!validate("Transaction Type",$transaction_type,1,"'TOPUP','PURCHASE'","enum") ||
		!validate("Amount",$amount,0,10,"decimal") ||
		!validate("Balance Amount",$balance_amount,0,10,"decimal") 	){
		LOG_MSG('ERROR',"do_wallet_save(): Validate args failed!");
		 return;
	} 
	LOG_MSG('DEBUG',"do_wallet_save(): Validated args");

	##################################################
	#                 DB INSERT                      #
	##################################################
	switch($mode) {
		case "ADD":
			$ROW=db_wallet_insert(
								$user_id,
								$created_dt,
								$description,
								$transaction_type,
								$amount,
								$balance_amount						);
			if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
					case 1062: // unique key
						add_msg("ERROR","The Wallet is already in use. Please enter a different Wallet<br/>");
						break;
					default:
						add_msg("ERROR","There was an error adding the Wallet.");
						break;
				}
				LOG_MSG('ERROR',"do_wallet_save(): Add args failed!");
				return;
			}
			add_msg("SUCCESS","New Wallet <strong>$wallet_id</strong> added successfully");
			break;
		case "UPDATE":
			// Validate wallet_id
			if (
				!validate("Wallet Id",$wallet_id,1,11,"int")
			) { 
				LOG_MSG('ERROR',"do_wallet_save(): Failed to validate PK");
				return;
			}

			$ROW=db_wallet_update(
								$wallet_id,
								$user_id,
								$created_dt,
								$description,
								$transaction_type,
								$amount,
								$balance_amount						);
			if ( $ROW['STATUS'] != "OK" ) {
				add_msg("ERROR","There was an error updating the Wallet <strong>$wallet_id</strong> .");
				return;
			}
			add_msg("SUCCESS","Wallet <strong>$wallet_id</strong> updated successfully");
			break;
	}

	// on success show the list
	$GO='list';
	LOG_MSG('INFO',"do_wallet_save(): END");
}

function do_wallet_save_json() {

	global $GO,$ROW;

	LOG_MSG('INFO',"do_wallet_save(): START  POST=".print_r($_POST,true));
	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';

	// Get all the args from $_POST
	$imei=get_arg($_GET,"imei");
	LOG_MSG('DEBUG',"do_wallet_save(): Got args");

	// Validate parameters
	if (
		!validate("IMEI",$imei,1,45,"varchar") ){
		$json['message']="Validate args failed";
		echo json_encode($json);
		exit;
	} 
	LOG_MSG('DEBUG',"do_wallet_save(): Validated args");

	db_transaction_start();

	$user_row=db_userimei_select( "",$imei );
	if ( $user_row[0]['STATUS'] != "OK"  || $user_row[0]['NROWS'] == 0 ) {
		LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows ");
		$json['message']="Error fetching details or no rows found";
		echo json_encode($json);
		exit;
	}

	$wallet_row=db_wallet_select(
			"",
			"",
			$imei,
			1);
			print_arr($wallet_row);
	if ( $wallet_row[0]['STATUS'] != "OK" ) {
		LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows ");
		$json['message']="Error fetching details or no rows found";
		echo json_encode($json);
		exit;
	}

	$comment="Rs 10 has been deducted from your account";
	$created_dt = date("Y-m-d H:i:s");
	$description = "Rs 10 has been deducted from your account";
	$transaction_type = "PURCHASE";
	$amount = 10;
	$balance_amount=0.0;
	if( $wallet_row[0]['NROWS'] > 0 && $wallet_row[0]['balance_amount'] > 10) { 
		$balance_amount =$wallet_row[0]['balance_amount'] -$amount ;
		LOG_MSG('INFO',"do_wallet_save(): ".$amount ." ".$wallet_row[0]['balance_amount']. " balance_amount = ".$balance_amount);
	} else {
		//$balance_amount = -$amount;
		LOG_MSG('INFO',"do_wallet_save(): balance ".$amount ." balance_amount = ".$balance_amount);
	}

	if($user_row[0]['email_id'] != "") {
		send_email($user_row[0]['email_id'],'admin@element42.in','','','Student certificate','PFA');
	}
	$ROW=db_wallet_insert(
						$user_row[0]['user_id'],
						$imei,
						$created_dt,
						$description,
						$transaction_type,
						$amount,
						$balance_amount,
						$comment);
	if ( $ROW['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error adding the Wallet.");
		LOG_MSG('ERROR',"do_wallet_save(): Add args failed!");
		return;
	}

	$json['status']='OK';
	LOG_ARR('INFO', "Json response",$json);
	db_transaction_end();
	LOG_MSG('INFO',"do_wallet_save(): Json Response".print_r($json,true));
	echo json_encode($json);
	exit;
}




/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_wallet_delete_json() {

	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_wallet_delete_json(): START POST=".print_r($_POST,true));

	$wallet_id=get_arg($_POST,"id");

	// Validate wallet_id
	if ( !validate("Wallet Id",$wallet_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_wallet_delete($wallet_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the Wallet';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_wallet_delete_json(): Delete row failed");
		exit;
	}

	$json_response['status']='OK';
	$json_response['id']=$wallet_id;

	//Send json response to response handler 
	echo json_encode($json_response);

	add_msg("SUCCESS","Wallet <strong>$wallet_id</strong> has been removed.");
	LOG_MSG('INFO',"do_wallet_delete_json(): END");
	exit;

}






?>
