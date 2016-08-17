<?php
 
function GATEWAY_prepare_request($ORDER) {

	LOG_MSG('INFO',"PAYU_prepare_request(): START".print_r($ORDER,true));
	/*
		PAYU
		----
		Merchant: C0Dr8m
		Salt: 3sf0jURk
		CardName: any name
		CardNumber: 5123456789012346
		CVV: 123
		Expiry: May-2017

		PAYUPAISA
		---------
		Merchant: JBZaLc
		Salt: GQs7yium
		CardName: any name
		CardNumber: 5123456789012346
		CVV: 123
		Expiry: May-2017
	*/
	$is_live_mode = 0;//shopsetting_isset('payment_gateway_live_mode');

	/******************************************************************/
	/* 4. PREPARE DATA FOR PAYU                                       */
	/******************************************************************/
	$MERCHANT_ID = "C0Dr8m";
	$MERCHANT_SALT = "3sf0jURk";

	$protocol=get_arg($_SERVER,'HTTPS') == 'on' ? 'https:' : 'http:';
	$DATA['surl']=BASEURL."/make_payment/confirm";
	$DATA['furl']=BASEURL."/make_payment/confirm";
	$DATA['udf1']=get_ip();

	$DATA['key']=$MERCHANT_ID;
	$DATA['txnid']=$ORDER['order_id_str'];	// we need to send the entire string bcoz it needs to be unique!
	$DATA['udf2']=$ORDER['order_id_str'];	// Payupaisa does not return the orderid in the txnid field! So we send it here as well.
	$DATA['amount']=$is_live_mode ? $ORDER['amount'] : intval($ORDER['amount']); 	// needs to be an integer in testing mode
	$DATA['productinfo']='Product not specified';
	$DATA['firstname']=preg_replace('/[^a-z]/','',strtolower($ORDER['name']));
	$DATA['email']=$ORDER['email_id'];

	$DATA['address1']='Address not specified';
	$DATA['address2']='Address2 not specified';
	$DATA['phone']=$ORDER["mobile"];
	$DATA['pg']=$ORDER['pmt_type'];	//NB = Net Banking & CC=Credit Card & DB=Debit card

	// CALCULATE HASH
	$calc_hash_seq = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
	$calc_hash_vars_seq = explode('|', $calc_hash_seq);
	$calc_hash_string = '';
	foreach ($calc_hash_vars_seq as $calc_hash_var) {
		$calc_hash_string .= isset($DATA[$calc_hash_var]) ? $DATA[$calc_hash_var] : '';
		$calc_hash_string .= '|';
	}
	$calc_hash_string .= $MERCHANT_SALT;
	$calc_hash = strtolower(hash('sha512', $calc_hash_string));
	$DATA['hash']=$calc_hash;
	//if (shopsetting_get('payment_gateway') == 'PAYUPAISA')  $DATA['service_provider']='payu_paisa';


	LOG_MSG('INFO',"PAYU_prepare_request(): ************* HASH DETAILS *****************
		hash format     =[$calc_hash_seq]
		hash string     =[$calc_hash_string]
		hash            =[$calc_hash]
		*********************************************");

	$GATEWAY['action']=$is_live_mode ? "https://secure.payu.in/_payment" : "https://test.payu.in/_payment";
	$GATEWAY['DATA']=$DATA;
	LOG_MSG('INFO',"PAYU_prepare_request(): END ");
	return $GATEWAY;
}


function GATEWAY_process_response() {

	/*############ARRAY:Recived POST############
	Array
	(
		[mihpayid] => 6134
		[mode] => 
		[status] => failure
		[unmappedstatus] => userCancelled
		[key] => C0Dr8m
		[txnid] => MEG052943
		[amount] => 847.21
		[discount] => 0.00
		[productinfo] => Megamart
		[firstname] => adsad
		[lastname] => 
		[address1] => 
		[address2] => 
		[city] => 
		[state] => 
		[country] => 
		[zipcode] => 
		[email] => nishant2097@gmail.com
		[phone] => 2222222222
		[udf1] => 
		[udf2] => 
		[udf3] => 
		[udf4] => 
		[udf5] => 
		[udf6] => 
		[udf7] => 
		[udf8] => 
		[udf9] => 
		[udf10] => 
		[hash] => bc981bde42e61ca41f2588abeaa8197531eab8d2755ba8418274819810892cc3e414a530c78e417ba166d3ac2524b1481c299fb60ed42b286ecd99f3880a6a42
		[field1] => 
		[field2] => 
		[field3] => 
		[field4] => 
		[field5] => 
		[field6] => 
		[field7] => 
		[field8] => 
		[field9] => cancelled by user
		[PG_TYPE] => 
		[bank_ref_num] => 
		[bankcode] => 
	) */
	$PAYMENT_GATEWAY="payu";	//shopsetting_get('payment_gateway');
	$MERCHANT_ID = "C0Dr8m";	// Merchant key here as provided by Payu
	$MERCHANT_SALT = "3sf0jURk";		// Merchant Salt as provided by Payu
	$RESPONSE=array();
	$RESPONSE['status']='ERROR';
	foreach($_POST as $key => $value) { $GATEWAY_RESPONSE[$key] = htmlentities($value, ENT_QUOTES); }

	// STEP 1: CHECK IF THERE IS A RESPONSE
	if (!isset($GATEWAY_RESPONSE) ) { 
		add_msg('ERROR',"The payment gateway seems to be experiencing some problems. Please try placing your order again or contact us on ".SUPPORT_EMAIL);
		return $RESPONSE;
	}

	// STEP 2: VERIFY HASH
	$calc_hash_seq = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
	$calc_hash_vars_seq = array_reverse(explode('|', $calc_hash_seq));		// Get into array and reverse it
	$calc_hash_string = $MERCHANT_SALT . '|' . get_arg($GATEWAY_RESPONSE,'status');	//Make string: => [salt | status | reverse order of variables]
	foreach ($calc_hash_vars_seq as $calc_hash_var) {
		$calc_hash_string .= '|';
		$calc_hash_string .= isset($GATEWAY_RESPONSE[$calc_hash_var]) ? $GATEWAY_RESPONSE[$calc_hash_var] : '';
	}
	$calc_hash =strtolower(hash('sha512', $calc_hash_string));
	LOG_MSG('INFO',"************* HASH DETAILS *****************
			hash format     =[MERCHANT_SALT|status|$calc_hash_seq]
			hash string     =[$calc_hash_string]
			hash            =[$calc_hash]
			GATEWAY RESPONSE[hash]  =[".get_arg($GATEWAY_RESPONSE,'hash')."]
			*********************************************");
	//$calc_hash = $GATEWAY_RESPONSE['hash'];	// TESTING
	// Put in the hash calculation into the comments
	$GATEWAY_RESPONSE['CALC_hash_format']="MERCHANT_SALT|status|$calc_hash_seq";
	$GATEWAY_RESPONSE['CALC_hash_string']=$calc_hash_string;
	$GATEWAY_RESPONSE['CALC_hash']=$calc_hash;
	$RESPONSE['comments']="<pre>Payment Gateway Response <br/>".print_r($GATEWAY_RESPONSE,true)."</pre><br>";
	if($calc_hash !== get_arg($GATEWAY_RESPONSE,'hash')) {
		LOG_MSG('ERROR',"HASH CHECK FAILED: calc_hash[$calc_hash] gateway_hash[".get_arg($GATEWAY_RESPONSE,'hash')."]");
		add_msg('ERROR',"There was an error processing your payment due to an invalid response.
						<br/>Please recheck your details and try again.<br/>");
		return $RESPONSE;
	}


	// STEP 3: VERIFY ORDER ID AND PAYMENT TYPE
	// Payupaisa has the orderid in udf2 and payu has it in txnid
	if ( $PAYMENT_GATEWAY == 'PAYUPAISA') $order_id_str=get_arg($GATEWAY_RESPONSE,'udf2');
	else $order_id_str=get_arg($GATEWAY_RESPONSE,'txnid');

	$order_id_len=strlen($order_id_str)-9;
	$order_id=substr($order_id_str,9,$order_id_len);
	LOG_MSG("INFO","%%%%%%%%%%%%%%%GATEWAY=$PAYMENT_GATEWAY order_id_str=[$order_id_str] order_id_len=[$order_id_len] order_id=[$order_id]%%%%%%%%%%%%%%%");
	$payment_type=get_arg($GATEWAY_RESPONSE,'mode') !== '' ? get_arg($GATEWAY_RESPONSE,'mode') : 'CC';
	if (!validate("Order ID",$order_id,1,11,'int') || 
		!validate("Payment type",$payment_type,2,2,'SIMPLE_STRING')) {
			clear_msgs();
			add_msg('ERROR',"There was an error processing your payment due to an invalid response.
							<br/>Please recheck your details and try again.<br/>");
		return $RESPONSE;
	}

	// Set order details
	$RESPONSE['order_id']=$order_id;
	$RESPONSE['pmt_type']=$payment_type;
	$RESPONSE['comments']="<pre>Payment Gateway Response <br/>".print_r($GATEWAY_RESPONSE,true)."</pre>";
	$RESPONSE['pmt_status']='FAILED';	// START with failed, then we'll change to success later
	$RESPONSE['order_status']='INVALID'; // this needs to be new so that all orders end up as new and then user can sort it out based on the payment status


	// STEP 4: CHECK SUCCESS OR FAILURE
	if (get_arg($GATEWAY_RESPONSE,'status') != 'success') { 
		add_msg('ERROR',"YOUR PAYMENT TRANSACTION FAILED!<br/>
							Payment gateway message was: <span style='color:blue'>[Ref:".get_arg($GATEWAY_RESPONSE,'bank_ref_num')." - ".get_arg($GATEWAY_RESPONSE,'field9')."]</span><br/>
							Please recheck your details and try again.");
		return $RESPONSE;
	}


	// STEP 5: ALL OK
	$RESPONSE['pmt_status']='PAID';
	$RESPONSE['order_status']='VALID';
	$RESPONSE['comments']="<pre>Payment Gateway Response <br/>".print_r($GATEWAY_RESPONSE,true)."</pre>";
	$RESPONSE['status']='OK';
	return $RESPONSE;

}


?>