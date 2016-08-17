<?php 

function do_wallet_save() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"do_wallet_save(): START");

	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO', "do_wallet_save(): START".print_r($_POST,true));

	$tag_id=get_arg($_POST,'tag_id');
	$transaction_type=get_arg($_POST,'transaction_type');
	$amount=get_arg($_POST,'amount');
	$imei=get_arg($_POST,'imei');
	
	if( !validate("Transaction Type",$transaction_type,2,"'CR','DR'",'enum') ||
		!validate("Tag Id",$tag_id,1,45,'varchar') ||
		!validate("Amount",$amount,1,12,'decimal') ||
		!validate("Imei",$imei,1,45,'varchar') ) {
		LOG_MSG('DEBUG',"do_wallet_save() : Validate Args Failed");
		$json['message']="Validate args failed";
		echo json_encode($json);
		exit;
	}

	LOG_MSG('DEBUG',"do_wallet_save(): Validated args");

	db_transaction_start();

	// Select nfctag with id number
	$nfc_tag_select=db_nfctag_select($tag_id);
	if( $nfc_tag_select[0]['STATUS'] !== 'OK' || $nfc_tag_select[0]['NROWS'] == 0 ) {
		LOG_MSG('DEBUG', "do_wallet_save() : Error fetching details or no rows found for id number ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	//Select student with fetched id number from nfc_tag_id
	$student_select=db_student_select($nfc_tag_select[0]['id_number']);
	if( $student_select[0]['STATUS'] !== 'OK' || $student_select[0]['NROWS'] == 0) {
		LOG_MSG('DEBUG',"do_wallet_save() : Error fetching details or no row found for student with id number [".$nfc_tag_id[0]['id_number']."]");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	$location_row=db_location_select($imei);
	if ( $location_row[0]['STATUS'] !== 'OK' || $location_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	//select group id based on location id
	$location_group_row=db_location_group_select($location_row[0]['location_id']);
	if ( $location_group_row[0]['STATUS'] !== 'OK' || $location_group_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	$wallet_row=db_wallet_select($student_select[0]['student_id'],$location_group_row[0]['group_id'],1);
	if ( $wallet_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Invalid Card";
		echo json_encode($json);
		exit;
	}

	$credit_limit=0.00;
	$balance_amount = 0.00;
	if ( $wallet_row[0]['NROWS'] > 0 ) {
		$group_row=db_group_select($wallet_row[0]['group_id']);
		if ( $group_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
			$json['message']="Invalid Card";
			echo json_encode($json);
			exit;
		}
		$credit_limit=$group_row[0]['credit_limit'];

		if( $transaction_type == "CR") {
			$balance_amount = $amount + $wallet_row[0]['balance_amount'];
			$description ="Amount credited";
			$json['message']="Rs. $amount has been added to the Card. Current Balance is Rs.$balance_amount ";
		} else if ( ($amount-$wallet_row[0]['balance_amount']) <= $credit_limit ) {
			$balance_amount = $wallet_row[0]['balance_amount'] - $amount;
			$description ="Amount debited";
			$json['message']="Rs. $amount has been deducted from the Card. Current Balance is Rs.$balance_amount ";	
		} else {
			LOG_MSG('ERROR',"do_wallet_save(): Balance amount is less than credit limit");
			$json['message']="Insufficient Balance !!!";
			echo json_encode($json);
			exit;
		}
	} else if ( $transaction_type == "CR" )  {
		$balance_amount = $amount;
		$description ="Amount credited";
		$json['message']="Rs.$amount has been added to the Card. Current Balance is Rs.$balance_amount ";
	} else if ( ($amount-$wallet_row[0]['balance_amount']) <= $credit_limit ) {
		$balance_amount = $wallet_row[0]['balance_amount'] - $amount;
		$description ="Amount debited";
		$json['message']="Rs.$amount has been deducted from the Card. Current Balance is Rs.$balance_amount ";
	} else {
		LOG_MSG('ERROR',"do_wallet_save(): Balance amount is less than credit amount ");
		$json['message']="Insufficient Balance !!!";
		echo json_encode($json);
		exit;
	}

	$resp=db_wallet_insert(	$student_select[0]['student_id'],
							$location_row[0]['location_id'],
							$location_group_row[0]['group_id'],
							$imei,$description,
							$transaction_type,
							$amount,
							$balance_amount);
	if( $resp['STATUS'] != 'OK' ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error while inserting the new row");
		$json['message']="Error while processing the request";
		echo json_encode($json);
		exit;
	}

	$json['status']='OK';
	$json['balance']=$balance_amount;
	LOG_ARR('INFO', "Json response",$json);
	db_transaction_end();
	LOG_MSG('INFO',"do_wallet_save(): Json Response".print_r($json,true));

	echo json_encode($json);
	exit;
}

function go_nfctag_select() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_nfctag_select(): START ".print_r($_POST,true));

	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';

	$nfc_tag_id=get_arg($_POST,'nfc_tag_id');
	$name=get_arg($_POST,"name");
	$id_number=get_arg($_POST,"id_number");
	$phone_no=get_arg($_POST,"phone_no");
	$recharge_amount=get_arg($_POST,"recharge_amount");
	$imei=get_arg($_POST,"imei");

	//LOG_MSG('INFO', "go_nfctag_select(): $_POST".print_r($_POST,true));

	if( !validate("NFC Tag Id",$nfc_tag_id,1,45,'varchar') ||
		!validate("Name",$name,0,200,'varchar') ||
		!validate("Id Number",$id_number,0,45,'varchar') ||
		!validate("Phone No",$phone_no,0,18,'bigint') ) {
		LOG_MSG('DEBUG',"go_nfctag_select() : Validate Args Failed");
		$json['message']=$ERROR_MESSAGE;
		echo json_encode($json);
		exit;
	}

	LOG_MSG('DEBUG',"go_nfctag_select(): Validated args");

	db_transaction_start();

	// Select nfctag with id number
	$nfc_tag_select=db_nfctag_select($nfc_tag_id);
	if( $nfc_tag_select[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('DEBUG', "go_nfctag_select() : Error fetching details or no rows found for id number ");
		$json['message']="NFCTAg Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	//Select student with fetched id number from nfc_tag_id
	if( $nfc_tag_select[0]['NROWS'] > 0 ) {
		$student_select=db_student_select($nfc_tag_select[0]['id_number']);
		if( $student_select[0]['STATUS'] !== 'OK' || $student_select[0]['NROWS'] == 0 ) {
			LOG_MSG('DEBUG',"go_nfctag_select() : Error fetching details or no row found for student");
			$json['message']="Invalid/Expired";
			echo json_encode($json);
			exit;
		}

		$location_row=db_location_select($imei);
		if ( $location_row[0]['STATUS'] !== 'OK' || $location_row[0]['NROWS'] == 0 ) {
			LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
			$json['message']="Invalid/Expired";
			echo json_encode($json);
			exit;
		}

		$location_group_row=db_location_group_select($location_row[0]['location_id']);
		if ( $location_group_row[0]['STATUS'] !== 'OK' || $location_group_row[0]['NROWS'] == 0 ) {
			LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
			$json['message']="Invalid/Expired";
			echo json_encode($json);
			exit;
		}

		$wallet_row=db_wallet_select($student_select[0]['student_id'],$location_group_row[0]['group_id'],1);
		if ( $wallet_row[0]['STATUS'] !== 'OK' ) {
			LOG_MSG('ERROR',"go_nfctag_select(): Error fetching wallet details ");
			$json['message']="Invalid/Expired";
			echo json_encode($json);
			exit;
		}

		$balance=0.00;
		if( $wallet_row[0]['NROWS'] != 0 ) {
			$balance=$wallet_row[0]['balance_amount'];
		}

		$json['name']=$student_select[0]['name'];
		$json['id_number']=$student_select[0]['id_number'];
		$json['phone']=$student_select[0]['phone'];
		$json['balance']=$balance;
	}

	$json['status']='OK';
	$json['NROWS']=$nfc_tag_select[0]['NROWS'];
	LOG_ARR('INFO', " go_nfctag_select() : Json response",$json);
	db_transaction_end();

	echo json_encode($json);
	exit;
}

function do_student_register() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"do_student_register(): START");

	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';

	$nfc_tag_id=get_arg($_POST,'nfc_tag_id');
	$name=get_arg($_POST,"name");
	$id_number=get_arg($_POST,"id_number");
	$phone_no=get_arg($_POST,"phone_no");
	$recharge_amount=get_arg($_POST,"recharge_amount");
	$imei=get_arg($_POST,"imei");

	LOG_MSG('INFO', "do_student_register(): POST Array".print_r($_POST,true));

	if( !validate("NFC Tag Id",$nfc_tag_id,1,45,'varchar') ||
		!validate("Name",$name,1,200,'varchar') ||
		!validate("Id Number",$id_number,1,45,'varchar') ||
		!validate("Phone no",$phone_no,1,18,'bigint')||
		!validate("Recharge Amount",$recharge_amount,1,12,'decimal') ) {
		LOG_MSG('DEBUG',"do_student_register() : Validate Args Failed");
		$json['message']="Validate Args Failed";
		echo json_encode($json);
		exit;
	}

	db_transaction_start();

	// Insert the details into nfctag table
	$nfctag_resp=db_nfctag_insert($nfc_tag_id,$id_number);
	if ( $nfctag_resp['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_student_register(): Error while inserting the new row");
		$json['message']="Error while processing the request. Please contact customer care";
		echo json_encode($json);
		exit;
	}

	// Insert the details into student table
	$resp=db_student_insert($name,$id_number,$phone_no);
	if ( $resp['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_student_register(): Error while inserting the new row");
		$json['message']="Error while processing the request. Please contact customer care";
		echo json_encode($json);
		exit;
	}

	$location_row=db_location_select($imei);
	if ( $location_row[0]['STATUS'] !== 'OK' || $location_row[0]['NROWS'] != 0  ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}

	//select group id based on location id
	$location_group_row=db_location_group_select($location_row[0]['location_id']);
	if ( $location_group_row[0]['STATUS'] !== 'OK' || $location_group_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Invalid/Expired";
		echo json_encode($json);
		exit;
	}
	//Insert the details into wallet table
	$transaction_type = "CR";
	$description = "Amount credited";
	$balance_amount = $recharge_amount;
	$resp=db_wallet_insert(	$resp['INSERT_ID'],
							$location_row[0]['location_id'],
							$location_group_row[0]['group_id'],
							$imei,
							$description,
							$transaction_type,
							$recharge_amount,
							$balance_amount);
	if ( $resp['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"do_student_register(): Error while inserting the new row");
		$json['message']="Error while processing the request. Please contact customer care";
		echo json_encode($json);
		exit;
	}

	db_transaction_end();

	LOG_MSG('DEBUG',"do_student_register(): Validated args");

	$json['status']='OK';
	$json['message']="User $name successfully registered. Current Balance is $balance_amount";
	$json['balance']=$balance_amount;
	$json['name']=$name;
	$json['id_number']=$id_number;
	$json['phone']=$phone_no;

	LOG_ARR('INFO', " do_student_register() : Json response",$json);

	echo json_encode($json);
	exit;
		}

function go_show_all_wallet() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_show_all_wallet(): START");

	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';

	$imei=get_arg($_POST,"imei");
	$nfc_tag_id=get_arg($_POST,"nfc_tag_id");

	LOG_MSG('INFO', "go_show_all_wallet(): POST Array".print_r($_POST,true));

	if( !validate("NFC Tag Id",$nfc_tag_id,1,45,'varchar') ||
		!validate("Imei",$imei,1,45,'varchar') ) {
		LOG_MSG('DEBUG',"go_show_all_wallet() : Validate Args Failed");
		$json['message']="Validate Args Failed";
		echo json_encode($json);
		exit;
	}

	db_transaction_start();

	$nfc_tag_select=db_nfctag_select($nfc_tag_id);
	if( $nfc_tag_select[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('DEBUG', "go_show_all_wallet() : Error fetching details or no rows found for id number ");
		$json['message']="NFCTAg Inavalid/Expired";
		echo json_encode($json);
		exit;
		}

	//Select student with fetched id number from nfc_tag_id
	$student_select=db_student_select($nfc_tag_select[0]['id_number']);
	if( $student_select[0]['STATUS'] !== 'OK' || $student_select[0]['NROWS'] == 0) {
		LOG_MSG('DEBUG',"go_show_all_wallet() : Error fetching details or no row found for student with id number [".$nfc_tag_id[0]['id_number']."]");
		$json['message']="Error while processing the request";
		echo json_encode($json);
		exit;
	}

	$location_row=db_location_select($imei);
	if ( $location_row[0]['STATUS'] !== 'OK' || $location_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	//select group id based on location id
	$location_group_row=db_location_group_select($location_row[0]['location_id']);
	if ( $location_group_row[0]['STATUS'] !== 'OK' || $location_group_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	$wallet_row=db_wallet_select($student_select[0]['student_id'],$location_group_row[0]['group_id']);
	if ( $wallet_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_show_all_wallet(): Error fetching wallet details ");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	// Initialize
	$json['all_created_dt'][0]=date("Y-m-d H:i:s");
	$json['all_balance_arr'][0]=0.00;

	for ( $i=0;$i<$wallet_row[0]['NROWS'];$i++ ) {
		$json['all_created_dt'][$i]=$wallet_row[$i]['created_dt'];
		$json['all_balance_arr'][$i]=$wallet_row[$i]['amount']." ".$wallet_row[$i]['transaction_type'];
	}

	db_transaction_end();

	$json['status']='OK';
	$json['wallet_rows']=$wallet_row[0]['NROWS'];

	LOG_ARR('INFO', "go_show_all_wallet(): END Json response",$json);

	echo json_encode($json);
	exit;
}

function go_check_balance() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_check_balance(): START");

	//Initalize Json
	$json['message']='';
	$json['status']='ERROR';

	$imei=get_arg($_POST,"imei");
	$nfc_tag_id=get_arg($_POST,"nfc_tag_id");

	LOG_MSG('INFO', "go_check_balance(): POST Array".print_r($_POST,true));

	if( !validate("NFC Tag Id",$nfc_tag_id,1,45,'varchar') ||
		!validate("Imei",$imei,1,45,'varchar') ) {
		LOG_MSG('DEBUG',"go_check_balance() : Validate Args Failed");
		$json['message']="Validate Args Failed";
		echo json_encode($json);
		exit;
	}

	db_transaction_start();
	$nfc_tag_select=db_nfctag_select($nfc_tag_id);
	if( $nfc_tag_select[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('DEBUG', "go_check_balance() : Error fetching details or no rows found for id number ");
		$json['message']="NFCTAg Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	//Select student with fetched id number from nfc_tag_id
	$student_select=db_student_select($nfc_tag_select[0]['id_number']);
	if( $student_select[0]['STATUS'] !== 'OK' || $student_select[0]['NROWS'] == 0) {
		LOG_MSG('DEBUG',"go_check_balance() : Error fetching details or no row found for student with id number [".$nfc_tag_id[0]['id_number']."]");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}
	
	$location_row=db_location_select($imei);
	if ( $location_row[0]['STATUS'] !== 'OK' || $location_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"go_check_balance(): Error fetching wallet details ");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	//select group id based on location id
	$location_group_row=db_location_group_select($location_row[0]['location_id']);
	if ( $location_group_row[0]['STATUS'] !== 'OK' || $location_group_row[0]['NROWS'] == 0 ) {
		LOG_MSG('ERROR',"do_wallet_save(): Error fetching wallet details ");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}

	$wallet_row=db_wallet_select($student_select[0]['student_id'],$location_group_row[0]['group_id'],5);
	if ( $wallet_row[0]['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"go_check_balance(): Error fetching wallet details ");
		$json['message']="Inavalid/Expired";
		echo json_encode($json);
		exit;
	}
	$balance=0.00;
	if( $wallet_row[0]['NROWS'] != 0 ) {
		$balance=$wallet_row[0]['balance_amount'];
	}

	// Initialize
	$json['created_dt'][0]=date("Y-m-d H:i:s");
	$json['balance_arr'][0]=0.00;

	for ( $i=0;$i<$wallet_row[0]['NROWS'];$i++ ) {
		$json['created_dt'][$i]=$wallet_row[$i]['created_dt'];
		$json['balance_arr'][$i]=$wallet_row[$i]['amount']." ".$wallet_row[$i]['transaction_type'];
	}

	db_transaction_end();

	LOG_MSG('DEBUG',"go_check_balance(): Validated args");

	$json['status']='OK';
	$json['balance']=$balance;

	LOG_ARR('INFO', " go_check_balance() : Json response",$json);

	echo json_encode($json);
	exit;
}

?>