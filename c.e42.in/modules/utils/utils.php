<?php

//Get the shop details
function init_org($org_id=-1) {

	LOG_MSG("INFO","init_org(): START org_id=[$org_id]");
	//Get shop id through POST when this function called from SHOP
	if( get_arg($_POST,'org_id') !== ''  ) $org_id=get_arg($_POST,'org_id');
	if(!validate("Shop ID",$org_id,1,11,"int")) {
		return false;
	}

	//SNT0098: To prevent cross session issue with shop
	if ( $org_id != -1 ) {
		$org_row=db_org_select($org_id);
		if( $org_row[0]['STATUS'] != 'OK' ) {
			add_msg("ERROR","Error initializing org. Contact Our Customer Care");
			show_msgs();
			return false;
		}

		//Initialize the shop details into session variable
		if( $org_row[0]['NROWS'] == 1 ) {
			$_SESSION['org_id']=$org_row[0]['org_id'];
			$_SESSION['org_name']=$org_row[0]['name'];
			$_SESSION['org_logo']=$org_row[0]['logo'];
		}
	}
	return true;
}

function init_shop_constants() {
	global $DOMAIN, $BRANCH, $ROWS_PER_PAGE;

	LOG_MSG("INFO","init_shop_constants START");

	$protocol=get_arg($_SERVER,'HTTPS') == 'on' ? 'https:' : 'http:'; // need this because Dompdf does not work with //


	if( isset($_SESSION['admin']['shop']['shop_id']) ) {
		$domain=$_SESSION['admin']['shop']['domain'];
        if ($DOMAIN == 'localhost' || $DOMAIN == '192.168.1.62' ) {
                define('SHOP_CDN',LOCAL_CDN_BASE."$domain/");
				define('SHOP_BASEURL',"$protocol//localhost/cn/shopnix/$BRANCH/shop");
        } else {
                define('SHOP_CDN',"$protocol//$domain/media/$domain/");
				define('SHOP_BASEURL',"$protocol//$domain");
        }
		define('SHOP_ID',$_SESSION['admin']['shop']['shop_id']);
		define('SHOP_CSV_DIR',MEDIA_DIR.$domain.'/shop_csv/');
		define('SHOP_THEME_DIR',MEDIA_DIR.$domain.'/images/banners/');
		define('SHOP_NAME',$_SESSION['admin']['shop']['name']);
		define('SHOP_DOMAIN',$_SESSION['admin']['shop']['domain']);
		define('SUPPORT_EMAIL',$_SESSION['admin']['shop']['support_email']);

		define('SHOP_UPLOADS_DIR',MEDIA_DIR.$domain.'/uploads/');
		define('SHOP_LOGO_DIR',MEDIA_DIR.$domain.'/images/logos/');
		define('SHOP_BG_IMAGE_DIR',MEDIA_DIR.$domain.'/images/bg/');
		define('SHOP_PRODUCT_IMG_DIR',MEDIA_DIR.$domain.'/images/products/');
		define('SHOP_PRODUCT_VARIANT_IMG_DIR',MEDIA_DIR.$domain.'/images/variants/');
		define('SHOP_FONTS_DIR',MEDIA_DIR.'/THEMES/fonts/');
		define('SHOP_CUSTOMER_IMG_DIR',MEDIA_DIR.$domain.'/images/customers/');
		if ($_SESSION['admin']['shop']['support_phone'] !== '' ) {
		define('SUPPORT_PHONE',$_SESSION['admin']['shop']['support_phone']);
			define('CONTACT_SUPPORT_MSG','Please contact our customer care on <a href="mailto:'.SUPPORT_EMAIL.'">'.SUPPORT_EMAIL.'</a> or call us on '.SUPPORT_PHONE.'.');
		} else {
			define('CONTACT_SUPPORT_MSG','Please contact our customer care on <a href="mailto:'.SUPPORT_EMAIL.'">'.SUPPORT_EMAIL.'</a>');
		}

		if ( !shopsetting_init() ) exit;

		// ROws per page
		$temp_rows_per_page=shopsetting_get('admin_rows_per_page');
		$ROWS_PER_PAGE=($temp_rows_per_page == '' ) ? 10 : $temp_rows_per_page;
		if (SHOP_DOMAIN == 'sapnaonline.com') define('PASSWORD_SALT','');	# No salt for sapna bcoz their old system never used one
		else define('PASSWORD_SALT',SHOP_ID);

		// No of days left untill the Shop plan subscription ends
		$days_left=((strtotime(shopsetting_get('subscription_end_date'))-strtotime(date('Y-m-d')))/86400);
		define('DAYS_LEFT',$days_left);
	}
}



function add_prodtag($product_id,$tag,$tag_value) {

	LOG_MSG('INFO',"add_prodtag(): START product_id=[$product_id] tag=[$tag] tag_value=[$tag_value]");

	// Transaction check
	global $TRANSACTION_STATUS;
	if (!$TRANSACTION_STATUS) {
		$is_internal_transaction=true;
		db_transaction_start(); // prevents nested transactions
	} else $is_internal_transaction=false;

	//STEP 1: DELETE OLD ProdTag
	$param_arr=_init_db_params();
	$param_arr=_db_prepare_param($param_arr,"s","tag",$tag,true);
	$param_arr=_db_prepare_param($param_arr,"s","value",$tag_value,true);
	$param_arr=_db_prepare_param($param_arr,"i","product_id",$product_id,true);
	$resp=execSQL("DELETE pt.*
			FROM tProdTag pt, tTag t
			WHERE t.tag_id = pt.tag_id
				AND t.tag = ?
				AND t.value = ? 
				AND pt.prod_id = ? 
				AND t.shop_id=".SHOP_ID,
				$param_arr['params'],
				true);
	if($resp['STATUS'] != 'OK') {
		add_msg('ERROR',"There was an error deleting the old $tag tag <strong>$tag_value</strong>");
		LOG_MSG('ERROR',"add_prodtag(): Error deleting prodtag details from tProdTag ");
		return false;
	}
	LOG_MSG('DEBUG',"add_prodtag(): Deleted all prodtags for product $product_id");



	// STEP 2:	SEE IF THE TAG ALREADY EXISTS. This has to be manually checked since 
	// 			we no longer have a unqiue key on the tag/value pair in tTag table
	$resp=db_tag_select('',$tag,$tag_value);
	if ($resp[0]['STATUS'] != 'OK' || $resp[0]['NROWS'] > 1 ) {
		add_msg('ERROR',"There was an error adding the $tag tag <strong>$tag_value</strong>");
		return false;
	} elseif ($resp[0]['NROWS'] === 1) {
		$tag_id=$resp[0]['tag_id'];
	// INSERT A NEW TAG INTO tTag
	} else {
		$resp=db_tag_insert($tag,$tag_value);
		if ($resp['STATUS'] != 'OK') {
			add_msg('ERROR',"There was an error adding the $tag tag <strong>$tag_value</strong>");
			return false;
		}
		$tag_id=get_arg($resp,'INSERT_ID');
	}
	LOG_MSG('DEBUG',"add_prodtag(): Got tag_id: $tag_id");


	// STEP 3: INSERT INTO tProdTag
	$resp=db_prodtag_insert($product_id,$tag_id);
	if ($resp['STATUS'] != 'OK') {
		add_msg('ERROR',"There was an error associating the $tag tag <strong>$tag_value</strong> with the product");
		return false;
	}
	LOG_MSG('INFO',"add_prodtag(): Added prodtag for product_id $product_id and tag_id $tag_id");

	if ( $is_internal_transaction ) db_transaction_end(); // prevents nested transactions
	LOG_MSG('INFO',"add_prodtag(): END");
	return $tag_id;
}


function delete_prodtag($product_id,$tag_id,$is_delete_tag=true) {

	global $TRANSACTION_STATUS;

	LOG_MSG('INFO',"delete_prodtag(): START product_id=[$product_id] tag_id=[$tag_id] is_delete_tag=[$is_delete_tag]");

	if (!$TRANSACTION_STATUS) {
		$is_delete_prodtag_transaction=true;
		db_transaction_start(); // prevents nested transactions
	} else $is_delete_prodtag_transaction=false;

	// 1. Delete from ProdTag table
	$delete_prodtag_resp=db_prodtag_delete($product_id,$tag_id);
	if ( $delete_prodtag_resp['STATUS'] != "OK" ) {	// for multi delete, the query return 0 rows
		add_msg('ERROR',"There was an error removing the tag");
		LOG_MSG('ERROR',"delete_prodtag(): Delete row failed");
		return false;
	}

	// Check if there are other products are associated with the tag 
	$resp=db_prodtag_select_count($tag_id);
	if ( $resp[0]['STATUS'] != "OK" ) {
		add_msg('ERROR',"There was an error removing the tag");
		LOG_MSG('ERROR',"delete_prodtag(): Error While retrieving the tag details");
		return false;
	}
	LOG_MSG('INFO',"delete_prodtag(): Tag [$tag_id] has [".$resp[0]['count']."] products associated with it.");

	// 2. Delete from Tag table if no other products are associated with it
	if ( $resp[0]['count'] == 0 && $is_delete_tag ) {
	$delete_resp=db_tag_delete($tag_id);
		if ( $delete_resp['STATUS'] != "OK" ) {
		add_msg('ERROR',"There was an error removing the tag");
		LOG_MSG('ERROR',"delete_prodtag(): Delete row failed");
		return false;
	}
	}

	LOG_MSG("INFO","+++++++is_delete_prodtag_transaction=[$is_delete_prodtag_transaction]");
	if ( $is_delete_prodtag_transaction ) db_transaction_end(); // prevents nested transactions
	LOG_MSG('INFO',"delete_prodtag(): END");
	return true;
}

function is_org_set() {
	// Check Org ID is present
	return (isset($_SESSION['org_id'])); 
}


// Rules to call this function
// 1. The coloumn name in the db should be same as the coloumn name in the front end table
// 2. Number of fields retreiving from the db should be same as the number of coloumns in the front end
function generate($report_mode,$row) {

	global $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;

	LOG_MSG('INFO',"generate(): START report_mode=[$report_mode], rows=[".print_r($row,true)."]");
	switch ($report_mode) {
		case 'HTML':
				include($row['filename']);
			break;
		case 'CSV':
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=".$_SESSION['admin']['shop']['domain'].'_'.$row['report_name'].'_'.date("Y-m-d-h-i-s").'.csv');
				header("Pragma: no-cache");
				header("Expires: 0");
				$nrows=$row[0]['NROWS'];
				unset($row[0]['NROWS']);
				unset($row[0]['STATUS']);
				if ( isset($row[0]['IS_SEARCH']) ) unset($row[0]['IS_SEARCH']);
				for ( $i=0;$i<$nrows;$i++ ) { 
					if ( isset($row[$i]['payment_type']) ) $row[$i]['payment_type']=$PAYMENT_TYPES[$row[$i]['payment_type']]; 
					if ( $row['report_name'] == 'Order_Report' ) {
						unset($row[$i]['order_by']);
					}

					if ( $row['report_name'] == 'Stock_Report' ) {
                                                $row[$i]['product']=encode_csv_field($row[$i]['product']);
                                        }
					// This loop will print headers
					if (  $i == 0 ) {
						foreach ( $row[0] AS $key => $value ) {
							if ( !$_SESSION['admin']['shop']['is_multistore'] && $key == 'supplier_name' ) continue;
							if ($row['report_name'] == 'Bluedart_Report' ) echo "$key,";
							else echo make_clean_str($key).',';
						}
						echo "\r\n";
					}
					// This will print the values
					if ( !$_SESSION['admin']['shop']['is_multistore'] ) unset($row[$i]['supplier_name']);
					//$row[$i]=encode_csv_field($row[$i]);
					echo implode(',',$row[$i])."\r\n";
				}
				break;
		case 'PDF':
				require_once("lib/dompdf/dompdf_config.inc.php");
				ob_start();
				include($row['filename']);
				$html=ob_get_contents();		// Set mail content
				ob_get_clean();
				$dompdf = new DOMPDF();
				$dompdf->load_html($html);
				if ( strlen($html) > 80000 ) {
					echo "Report is too large to generate PDF. Please download CSV instead";
					LOG_MSG('INFO',"Maximum memory reached. String Length=[".strlen($html)."bytes]");
					exit;			
				}
				// For blue dart report,the table width is too large to fit into an A4-portrait
				if ( $row['report_name'] == 'Bluedart_Report' ) { 
					$dompdf->set_paper('a3', 'landscape');
				}
				$dompdf->render();
				$dompdf->stream($row['report_name'].'.pdf');
				break;
		case 'EMAIL':
				// Get args from POST
				$from=get_arg($_GET,"from");
				$to=get_arg($_GET,"to");
				$subject=get_arg($_GET,"subject");
				$content_type=get_arg($_GET,'content_type');
				$message=get_arg($_GET,'message');

				// Validate parameters
				if (
					!validate("To address",$to,5,100,"EMAIL") ||
					!validate("From address",$from,5,100,"EMAIL") ||
					!validate("Subject",$subject,1,100,"varchar") ||
					!validate("Message",$message,0,200,"varchar") ){
					LOG_MSG('ERROR',"generate(): VALIDATE ARGS FAILED! ".print_r($_POST,true));
					return false;
				}
				// Validate parameters
				if ( $content_type != 'pdf_attachment' && $content_type != 'plain_text' ) {
					add_msg('ERROR','Invalid Mail mode');
					LOG_MSG('ERROR',"generate(): VALIDATE ARGS FAILED! Invalid Mail mode".print_r($_POST,true));
					return false;
				}
				LOG_MSG('INFO',"generate(): Validated args");

				// Required for attachment
				$attachments_arr=array();
				if ( $content_type == 'pdf_attachment' ) {
					require_once("lib/dompdf/dompdf_config.inc.php");
					ob_start();
					include($row['filename']);
					$html=ob_get_contents();		// Set mail content
					ob_get_clean();
					$dompdf = new DOMPDF();
					$dompdf->load_html($html);
					LOG_MSG('INFO',"Maximum memory reached. String Length=[".strlen($html)."bytes]");
					// Due to the memory allocation error, send as html mail as a work around instead of attaching PDF
					if ( strlen($html) > 80000 ) {
						LOG_MSG('INFO',"Sending HTML mail instead of attachment as Maximum memoty reached. String Length=[".strlen($html)."bytes]");
						$message.="<br/> $html";		// Set mail content
						$plain_message=''; 				// the contents are already in $message
					} else {
					// For blue dart report,the table width is too large to fit into an A4-portrait
					if ( $row['report_name'] == 'Bluedart_Report' ) { 
						$dompdf->set_paper('a3', 'landscape');
					}
					$dompdf->render();
					$attachments_arr[0]['filename']=$row['report_name'].'.pdf';
					$attachments_arr[0]['data']=$dompdf->output();
					$plain_message=$html;			// this will contain the attachment contents
					}
				} else {

				//Store in buffer and load to mail message	
				ob_start();
				include($row['filename']);
					$message.='<br/>'.ob_get_contents();		// Set mail content
				ob_get_clean();
				$plain_message=''; 				// the contents are already in $message
				}
				send_email($to,$from,'','',$subject,$message,$attachments_arr,$plain_message); // Send Mail
				break;
	}
	LOG_MSG('INFO',"generate(): END");
	return true;
}


// Validate order_status
function validate_status($current_status,$new_status) {

	LOG_MSG('INFO',"validate_order_status(): START current_status=[$current_status], new_status=[$new_status]");

	switch ($current_status) {
		case 'NEW': // 1. NEW => PROCURE, CANCELLED
				if ( $new_status != 'NEW' && $new_status != 'PROCURE' && $new_status != 'CANCELLED' ) {
					add_msg('ERROR',"Status NEW cannot be changed to $new_status");
					return false;
				}
				break;
		case 'PROCURE': // 2. PROCURE => DELAYED, BILLING, CANCELLED
				if ( $new_status != 'PROCURE' && $new_status != 'DELAYED' && $new_status != 'BILLING' && $new_status != 'CANCELLED' ) {
					add_msg('ERROR',"Status PROCURE cannot be changed to $new_status");
					return false;
				}
				break;
		case 'DELAYED': // 3. DELAYED => BILLING, CANCELLED
				if ( $new_status != 'DELAYED' && $new_status != 'BILLING' && $new_status != 'CANCELLED' ) {
					add_msg('ERROR',"Status DELAYED cannot be changed to $new_status");
					return false;
				}
				break;
		case 'BILLING': // 4. BILLING => PACKING, DELAYED, CANCELLED
				if ( $new_status != 'BILLING' && $new_status != 'PACKING' && $new_status != 'DELAYED' && $new_status != 'CANCELLED' ) {
					add_msg('ERROR',"Status BILLING cannot be changed to $new_status");
					return false;
				}
				break;
		case 'PACKING': // 5. PACKING => SHIPPED
				if ( $new_status != 'PACKING' && $new_status != 'SHIPPED' ) {
					add_msg('ERROR',"Status PACKING cannot be changed to $new_status");
					return false;
				}
				break;
		case 'CANCELLED': // 6. CANCELLED => CANCELLED
				if ( $new_status != 'CANCELLED' && $new_status != 'PROCURE' ) {
					add_msg('ERROR',"Status CANCELLED cannot be changed to $new_status");
					return false;
				}
				break;
		case 'SHIPPED': // 7. SHIPPED => DELIVERED
				if ( $new_status != 'SHIPPED' && $new_status != 'DELIVERED' ) {
					add_msg('ERROR',"Status SHIPPED cannot be changed to $new_status");
					return false;
				}
				break;
		case 'DELIVERED': // 7. DELIVERED => DELIVERED
				if ( $new_status != 'DELIVERED' ) {
					add_msg('ERROR',"Status DELIVERED cannot be changed to $new_status");
					return false;
				}
				break;
	}
	return true;

	LOG_MSG('INFO',"validate_order_status(): END");
}

// checks the current plan
function check_product_limit($current_no_of_products=0) {

	LOG_MSG('INFO',"check_product_limit(): START current_no_of_products=[$current_no_of_products]");

	if (!$current_no_of_products) {
		// Get the total number of products currently in the database if not passed
		$row=db_count_products();
		if ( $row[0]['STATUS'] != 'OK' ) {
			add_msg("ERROR","<b>There was an error getting the current product count from your store. Please try again later.</b> ");
			LOG_MSG('ERROR',"Error getting total number of products from the database");
			return false;
		}
		$current_no_of_products=$row[0]['product_count'];
		LOG_MSG('INFO',"Total no of products products_total :[$current_no_of_products]");
	}

	$max_no_of_products=shopsetting_get('max_no_of_products');

	// Check if the plan limit has exceeded
	if ( $max_no_of_products <= $current_no_of_products ) {
		switch ( $max_no_of_products ) {
			case 25000:
				go_upgrade_plan('<div class="upgradeplan-message-small" style="padding-bottom:10px;color:grey">Your store already has '.$current_no_of_products.' products</div><div class="upgradeplan-message-big" ><b>More than 25,000 products</b> is only available on our <b>Enterprise Plans</b>!</div><br><div class="upgradeplan-message-small" >To upgrade please contact us at <a href="mailto:support@shopnix.in">support@shopnix.in</a>!</div>');
				break;
			case 10000:
				go_upgrade_plan('<div class="upgradeplan-message-small" style="padding-bottom:10px;color:grey">Your store already has '.$current_no_of_products.' products</div><div class="upgradeplan-message-big" ><b>More than 10,000 products</b> is only available on the <b>Premium Plan</b>!</div><br><div class="upgradeplan-message-small" >Upgrade now to increase the number of products in your store!</div>');
				break;
			case 2000:
				go_upgrade_plan('<div class="upgradeplan-message-small" style="padding-bottom:10px;color:grey">Your store already has '.$current_no_of_products.' products</div><div class="upgradeplan-message-big" ><b>More than 2,000 products</b> is only available on the <b>Pro Plan</b> and above!</div><br><div class="upgradeplan-message-small" >Upgrade now to increase the number of products in your store!</div>');
				break;
			case 500:
				go_upgrade_plan('<div class="upgradeplan-message-small" style="padding-bottom:10px;color:grey">Your store already has '.$current_no_of_products.' products</div><div class="upgradeplan-message-big" ><b>More than 500 products</b> is only available on the <b>Starter Plan</b> and above!</div><br><div class="upgradeplan-message-small" >Upgrade now to increase the number of products in your store!</div>');
				break;
			case 100:
				go_upgrade_plan('<div class="upgradeplan-message-small" style="padding-bottom:10px;color:grey">Your store already has '.$current_no_of_products.' products</div><div class="upgradeplan-message-big" ><b>More than 100 products</b> is only available on the <b>Quicky Plan</b> and above!</div><br><div class="upgradeplan-message-small" >Upgrade now to increase the number of products in your store!</div>');
				break;
			default:
				go_upgrade_plan('<div class="upgradeplan-message-small" style="padding-bottom:10px;color:grey">Your store already has '.$current_no_of_products.' products</div><div class="upgradeplan-message-big" ><b>More than '.$max_no_of_products.' products</b> is only available on a <b>paid plan</b>!</div><br><div class="upgradeplan-message-small" >Upgrade now to increase the number of products in your store!</div>');
				break;
		}
		LOG_MSG("INFO","check_product_limit():Total number of products exceeded the plan limit");
		exit;
	}

	LOG_MSG('INFO',"check_product_limit(): END");
	return true;
}

// Displays upgrade plan while trying to access features out of the current plan
function go_upgrade_plan($message="") {


	LOG_MSG('INFO',"go_upgrade_plan(): START");

	// If the message is empty, then show a default message
	if ( $message=="" && get_arg($_GET,'message') ) $message='<div class="upgradeplan-message-big" >'.get_arg($_GET,'message').'</div>';
	if ( $message=="" ) $message='<div class="upgradeplan-message-big" >Please select one of the plans below to renew!</div>';

	$email_id=urlencode($_SESSION['admin']['email_id']); 	//Admin Email Id
        $name=urlencode(ucwords(trim(preg_replace('/[ ]+/',' ',preg_replace('/[^a-zA-Z\+]/',' ',strtolower($_SESSION['admin']['fname']))),' ')));


	// Get the plans available from the shop payments.shopnix.in
	$plans_row=db_get_plans();
	LOG_ARR("INFO","plans_row",$plans_row);
	if( $plans_row[0]['STATUS'] != 'OK' ) {
		add_msg("ERROR","Error getting the plan details");
		LOG_MSG('ERROR',"go_upgrade_plan() Error getting the plan details");
		return false;
	}

	// Payment Url
	$baseurl="http://payments.shopnix.in/index.php?mod=store&mode=a&go=payment&new_customer=1&is_checkout_as_guest=1&pincode=111111&state=NA&city=NA&area=NA&mobile=1111111111&payment_type=CC&qty=1&email_id=$email_id&name=".SHOP_DOMAIN;

	include("static/html/upgrade_plan.html");
	include("static/html/footer.html");

	LOG_MSG('INFO',"go_upgrade_plan(): END");
}



function do_redirect_openid() {

	//echo print_arr($_GET);
	//echo 'Redirected to shopnix<br>';
	//echo $_SERVER['REQUEST_URI'];
	
//	echo $_GET['state'].'&'.$_GET['code'];
$location=$_GET['state'].'&'.$_GET['code'];
echo '<script>document.location.href="'.$location.'"</script>'; 
	//header('Location: '.$_GET['state'].'&'.$_GET['code']);

	exit;

}


// Get State and City name
function get_state_city_area_json() {

	//if(!has_user_permission(__FUNCTION__)) return; //CHECK USER ACCESSIBILITY

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"get_state_city_area_json(): START");

	$json_response=array();
	$json_response['status']='ERROR';
	$json_response['nrows']=0;

	// Validate the pincode
	$pincode=get_arg($_GET,'pincode');
	if ( !validate("Pincode",$pincode,1,10,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		LOG_MSG('ERROR',"get_state_city_area_json(): Error validating. Error message = $ERROR_MESSAGE");
		exit;
	}

	// Get locations from the DB
	$row=db_get_state_city_area($pincode);
	if ( $row[0]['STATUS'] != "OK" ) {			// Error
		LOG_MSG('ERROR',"get_state_city_area_json(): There was an error loading areas");
		echo json_encode($json_response);
		LOG_ARR("INFO","get_state_city_area_json(): row",$row);
		exit;
	}
	if ( $row[0]['NROWS'] == 0 ) {				// No locations found
		LOG_MSG('ERROR',"get_state_city_area_json(): No matching locations found");
		$json_response['status']='OK';
		echo json_encode($json_response);
		exit;
	}

	$area_list=array();
	for( $i=0;$i<$row[0]['NROWS'];$i++ ) {
		$area_list[$i]=$row[$i]['area_name'];
	}
	$json_response['status']='OK';
	$json_response['nrows']=$row[0]['NROWS'];
	$json_response['state_name']=$row[0]['state_name'];
	$json_response['city_name']=$row[0]['city_name'];
	$json_response['area_list']=json_encode($area_list);

	//Send json response to response handler 
	echo json_encode($json_response);
	LOG_MSG('INFO',"get_state_city_area_json(): END");
	exit;
}


function intercom_send_data_single($shop_id='') {

	LOG_MSG('INFO',"intercom_send_data_single(): START shop_id=[$shop_id]");
	if (!defined('IGNORE_SWIFT')) include("lib/intercom/intercom.php");

	$INTERCOM_USER=array();
	$INTERCOM_SHOP=array();

	/********************************* INTERCOM_USER *********************************/
	$user_shop_row=db_user_shop_intercom_select($shop_id);
	if ($user_shop_row[0]['STATUS'] != 'OK' || $user_shop_row[0]['NROWS'] < 1 ) {
		LOG_MSG("ERROR","intercom_send_data_single(): No shops found - shop_id [$shop_id]");
		return false;
	}

	for ($i=0;$i<$user_shop_row[0]['NROWS'];$i++) {
		$shop_id=$user_shop_row[$i]['shop_id'];
		$email_id=$user_shop_row[$i]['email_id'];
		//echo date("d-m-Y H:i:s").": [$i] Sending Shop_id:[$shop_id] Domain:[".$user_shop_row[$i]['domain']."]  User:[".$user_shop_row[$i]['user_id']."] email:[$email_id] \n";

		// Fetch subscription details for the shop
		/*$shopsetting_resp=db_shopsetting_intercom_select($shop_id);
		if ($shopsetting_resp[0]['STATUS'] != 'OK' || $shopsetting_resp[0]['NROWS'] < 1 ) {
			LOG_MSG("ERROR","intercom_send_data_single(): No shopsetting found for shop_id [$shop_id]");
			return false;
		}
		// Copy the subscription info to the main array
		for ($j=0;$j<$shopsetting_resp[0]['NROWS'];$j++) {
			$setting_name=$shopsetting_resp[$j]['name'];
			$setting_value=$shopsetting_resp[$j]['value'];
			$user_shop_row[$i][$setting_name]=$setting_value;
		}*/


		$INTERCOM_USER['user_id']=$user_shop_row[$i]['user_id'];
		$INTERCOM_USER['email']=$user_shop_row[$i]['email_id'];
		$INTERCOM_USER['created_at']=strtotime($user_shop_row[$i]['created_dt']);
		$INTERCOM_USER['name']=$user_shop_row[$i]['fname'];
		//iif ( $user_shop_row[$i]['mobile'] != '' ) $INTERCOM_USER['mobile']=$user_shop_row[$i]['mobile'];

		/*

		// User View Count
		$INTERCOM_USER['view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND activity LIKE "%View %"');
		if ( $INTERCOM_USER['view_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data_single(): Error while fetching the VIEW COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['view_count']=0;
		}
		if ( $INTERCOM_USER['view_count'] == '' ) $INTERCOM_USER['view_count']=0;

		// User Edit Count
		$INTERCOM_USER['edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND (activity LIKE "% Add%" OR activity LIKE "% Modify%" OR activity LIKE "% Delete%")');
		if ( $INTERCOM_USER['edit_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data_single(): Error while fetching the EDIT COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['edit_count']=0;
		}
		if ( $INTERCOM_USER['edit_count'] == '' ) $INTERCOM_USER['edit_count']=0;

		// Admin View Count
		$INTERCOM_USER['admin_view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND activity LIKE "Admin > %View %"');
		if ( $INTERCOM_USER['admin_view_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data_single(): Error while fetching the ADMIN VIEW COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['admin_view_count']=0;
		}
		if ( $INTERCOM_USER['admin_view_count'] == '' ) $INTERCOM_USER['admin_view_count']=0;

		// Admin Edit Count
		$INTERCOM_USER['admin_edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND (activity LIKE "Admin > % Add%" OR activity LIKE "Admin > % Modify%" OR activity LIKE "Admin > % Delete%")');
		if ( $INTERCOM_USER['admin_edit_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data_single(): Error while fetching the ADMIN EDIT COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['admin_edit_count']=0;
		}
		if ( $INTERCOM_USER['admin_edit_count'] == '' ) $INTERCOM_USER['admin_edit_count']=0;

		// Store View Count
		$INTERCOM_USER['store_view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND activity LIKE "Store > %View %"');
		if ( $INTERCOM_USER['store_view_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data_single(): Error while fetching the STORE VIEW COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['store_view_count']=0;
		}
		if ( $INTERCOM_USER['store_view_count'] == '' ) $INTERCOM_USER['store_view_count']=0;

		// Store Edit Count
		$INTERCOM_USER['store_edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND (activity LIKE "Store > % Add%" OR activity LIKE "Store > % Modify%" OR activity LIKE "Store > % Delete%")');
		if ( $INTERCOM_USER['store_edit_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data_single(): Error while fetching the STORE EDIT COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['store_edit_count']=0;
		}
		if ( $INTERCOM_USER['store_edit_count'] == '' ) $INTERCOM_USER['store_edit_count']=0;

		*/

		/********************************* INTERCOM_SHOP *********************************/
		$INTERCOM_SHOP['id']=$user_shop_row[$i]['shop_id'];
		$INTERCOM_SHOP['name']=$user_shop_row[$i]['domain'];
		$INTERCOM_SHOP['created_at']=strtotime($user_shop_row[$i]['created_dt']);

		
		/*
		$subscription_end_dt=$user_shop_row[$i]['subscription_end_date'];
		$subs_reminder_dt=date("Y-m-d",strtotime("$subscription_end_dt -30 day"));
		//if (defined("CLI_MODE")) echo "subscription_end_date=[".$user_shop_row[$i]['subscription_end_date']."] subs_reminder_dt=[$subs_reminder_dt]\n";
		$INTERCOM_SHOP['subscription_end_dt']=strtotime("$subscription_end_dt 23:59:59");
		$INTERCOM_SHOP['subs_reminder_dt']=strtotime(date("Y-m-d",strtotime($user_shop_row[$i]['subscription_end_date']." -30 day"))." 23:59:59"); */


		// DEBUG INFO 
		/*$INTERCOM_SHOP['created_at_org']=$user_shop_row[$i]['created_dt'];
		$INTERCOM_SHOP['subscription_end_dt_org']=$user_shop_row[$i]['subscription_end_date']." 23:59:59";
		$INTERCOM_SHOP['max_no_of_products_org']=$user_shop_row[$i]['max_no_of_products'];
		$INTERCOM_SHOP['subscription_is_trial_org']=$user_shop_row[$i]['subscription_is_trial'];
		$INTERCOM_SHOP['subscription_is_renewed_org']=$user_shop_row[$i]['subscription_is_renewed'];*/

		/*

		// Status
		// subscription will end within 15 days of creating
		//  the shop, then he is still in trial
		if ( $user_shop_row[$i]['subscription_is_trial'] ) $INTERCOM_SHOP['status']='TRIAL';
		elseif ( $user_shop_row[$i]['subscription_is_renewed'] ) $INTERCOM_SHOP['status']='RENEWED';
		else $INTERCOM_SHOP['status']='PAID';

		// Plan
		if ( $user_shop_row[$i]['subscription_is_trial'] ) {
			$INTERCOM_SHOP['plan']='FREE';
		} else {
			switch ( $user_shop_row[$i]['max_no_of_products'] ) {
				case 500: 
					$INTERCOM_SHOP['plan']='QUICKY'; 
					break;
				case 2000: 
					$INTERCOM_SHOP['plan']='STARTER';
					break;
				case 10000: 
					$INTERCOM_SHOP['plan']='PRO';
					break;
				case 25000: 
					$INTERCOM_SHOP['plan']='PREMIUM';
					break;
				default: $INTERCOM_SHOP['plan']='CUSTOM';
					break;
			}
		}

		// Subs Status
		$INTERCOM_SHOP['subs_status']=strtotime($user_shop_row[$i]['subscription_end_date']) > strtotime(date('Y-m-d')) ? 'ACTIVE' : 'EXPIRED';

		// Expires In
		$INTERCOM_SHOP['expires_in']=(strtotime($user_shop_row[$i]['subscription_end_date'])-strtotime(date('Y-m-d')))/(60*60*24);
		*/

		// Assign the customer to an agent 
		$AGENTS=array("Subhrajit","Sachin");
		$last_agent=file_get_contents("/tmp/last_agent.snix");
		LOG_MSG("INFO","Last agent=[$last_agent]");
		if ( $last_agent === false ||
			 $last_agent >= count($AGENTS)-1) {
				$last_agent="0";
		} else $last_agent++;
		file_put_contents("/tmp/last_agent.snix", $last_agent);
		LOG_MSG("INFO","This agent=[$last_agent]");
		$INTERCOM_SHOP['agent']=$AGENTS[$last_agent];
		//$INTERCOM_SHOP['agent']='Sachin';



		$INTERCOM_USER['company']=$INTERCOM_SHOP;
		//$INTERCOM_USER['companies']['id']=$shop_id;
		//$INTERCOM_USER['companies']['name']=$user_shop_row[$i]['domain'];
		//$INTERCOM_USER['companies']['max_no_of_products']=120;
		//$INTERCOM_USER['companies']['plan']='PAID';

		//print_arr($INTERCOM_USER);
		LOG_MSG("INFO","################## Sending Shop_id:[$shop_id] Domain:[".$user_shop_row[$i]['domain']."]  User:[".$user_shop_row[$i]['fname']."] </pre>");
		if (defined("CLI_MODE")) echo date("d-m-Y H:i:s").": Sending Shop_id:[$shop_id] Domain:[".$user_shop_row[$i]['domain']."]  User:[".$user_shop_row[$i]['email_id']."] agent=[$last_agent] \n";
		$intercom = new Intercom('e5d8196714cf0bf945bd764be170dd41c22dc998', '70d4cdfa27209f16a53a211bce5de077f8e84d94');
		$intercom_resp=$intercom->updateUser($user_shop_row[$i]['user_id'],
							$user_shop_row[$i]['email_id'],
							$user_shop_row[$i]['fname'],
							$INTERCOM_USER
						);
		
		
		
		//LOG_ARR("INFO","INTERCOM DATA FOR USER $email_id",$INTERCOM_USER);
		//LOG_ARR("INFO","INTERCOM DATA FOR SHOP $shop_id",$INTERCOM_SHOP);
		//LOG_ARR("INFO","INTERCOM RESPONSE",$intercom_resp);
		if ( $i!=0 && $i % 25 ) sleep(10);
	} // END user_row loop

	LOG_MSG('INFO',"intercom_send_data_single(): END");

	return true;
}


function intercom_send_data($shop_id='') {

	LOG_MSG('INFO',"intercom_send_data(): START shop_id=[$shop_id]");
	if (!defined('IGNORE_SWIFT')) include("lib/intercom/intercom.php");

	$INTERCOM_USER=array();
	$INTERCOM_SHOP=array();

	/********************************* INTERCOM_USER *********************************/
	$user_shop_row=db_user_shop_intercom_select($shop_id);
	if ($user_shop_row[0]['STATUS'] != 'OK' || $user_shop_row[0]['NROWS'] < 1 ) {
		LOG_MSG("ERROR","intercom_send_data(): No shops found - shop_id [$shop_id]");
		return false;
	}

	for ($i=0;$i<$user_shop_row[0]['NROWS'];$i++) {
		$shop_id=$user_shop_row[$i]['shop_id'];
		$email_id=$user_shop_row[$i]['email_id'];
		//echo date("d-m-Y H:i:s").": [$i] Sending Shop_id:[$shop_id] Domain:[".$user_shop_row[$i]['domain']."]  User:[".$user_shop_row[$i]['user_id']."] email:[$email_id] \n";

		// Fetch subscription details for the shop
		$shopsetting_resp=db_shopsetting_intercom_select($shop_id);
		if ($shopsetting_resp[0]['STATUS'] != 'OK' || $shopsetting_resp[0]['NROWS'] < 1 ) {
			LOG_MSG("ERROR","intercom_send_data(): No shopsetting found for shop_id [$shop_id]");
			return false;
		}
		// Copy the subscription info to the main array
		for ($j=0;$j<$shopsetting_resp[0]['NROWS'];$j++) {
			$setting_name=$shopsetting_resp[$j]['name'];
			$setting_value=$shopsetting_resp[$j]['value'];
			$user_shop_row[$i][$setting_name]=$setting_value;
		}


		$INTERCOM_USER['user_id']=$user_shop_row[$i]['user_id'];
		$INTERCOM_USER['email']=$user_shop_row[$i]['email_id'];
		$INTERCOM_USER['created_at']=strtotime($user_shop_row[$i]['created_dt']);
		$INTERCOM_USER['name']=$user_shop_row[$i]['fname'];
		if ( $user_shop_row[$i]['mobile'] != '' ) $INTERCOM_USER['mobile']=$user_shop_row[$i]['mobile'];

		// User View Count
		$INTERCOM_USER['view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND activity LIKE "%View %"');
		if ( $INTERCOM_USER['view_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data(): Error while fetching the VIEW COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['view_count']=0;
		}
		if ( $INTERCOM_USER['view_count'] == '' ) $INTERCOM_USER['view_count']=0;

		// User Edit Count
		$INTERCOM_USER['edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND (activity LIKE "% Add%" OR activity LIKE "% Modify%" OR activity LIKE "% Delete%")');
		if ( $INTERCOM_USER['edit_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data(): Error while fetching the EDIT COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['edit_count']=0;
		}
		if ( $INTERCOM_USER['edit_count'] == '' ) $INTERCOM_USER['edit_count']=0;

		// Admin View Count
		$INTERCOM_USER['admin_view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND activity LIKE "Admin > %View %"');
		if ( $INTERCOM_USER['admin_view_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data(): Error while fetching the ADMIN VIEW COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['admin_view_count']=0;
		}
		if ( $INTERCOM_USER['admin_view_count'] == '' ) $INTERCOM_USER['admin_view_count']=0;

		// Admin Edit Count
		$INTERCOM_USER['admin_edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND (activity LIKE "Admin > % Add%" OR activity LIKE "Admin > % Modify%" OR activity LIKE "Admin > % Delete%")');
		if ( $INTERCOM_USER['admin_edit_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data(): Error while fetching the ADMIN EDIT COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['admin_edit_count']=0;
		}
		if ( $INTERCOM_USER['admin_edit_count'] == '' ) $INTERCOM_USER['admin_edit_count']=0;

		// Store View Count
		$INTERCOM_USER['store_view_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND activity LIKE "Store > %View %"');
		if ( $INTERCOM_USER['store_view_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data(): Error while fetching the STORE VIEW COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['store_view_count']=0;
		}
		if ( $INTERCOM_USER['store_view_count'] == '' ) $INTERCOM_USER['store_view_count']=0;

		// Store Edit Count
		$INTERCOM_USER['store_edit_count']=db_get_list('LIST','SUM(counter)','tShopActivity','email_id="'.$user_shop_row[$i]['email_id'].'" AND (activity LIKE "Store > % Add%" OR activity LIKE "Store > % Modify%" OR activity LIKE "Store > % Delete%")');
		if ( $INTERCOM_USER['store_edit_count'] === false ) {
			LOG_MSG('ERROR','intercom_send_data(): Error while fetching the STORE EDIT COUNT from Shop Activity for the user email_id=['.$INTERCOM_USER['email'].']');
			$INTERCOM_USER['store_edit_count']=0;
		}
		if ( $INTERCOM_USER['store_edit_count'] == '' ) $INTERCOM_USER['store_edit_count']=0;



		/********************************* INTERCOM_SHOP *********************************/
		$INTERCOM_SHOP['id']=$user_shop_row[$i]['shop_id'];
		$INTERCOM_SHOP['name']=$user_shop_row[$i]['domain'];
		$INTERCOM_SHOP['created_at']=strtotime($user_shop_row[$i]['created_dt']);


		$subscription_end_dt=$user_shop_row[$i]['subscription_end_date'];
		$subs_reminder_dt=date("Y-m-d",strtotime("$subscription_end_dt -30 day"));
		//if (defined("CLI_MODE")) echo "subscription_end_date=[".$user_shop_row[$i]['subscription_end_date']."] subs_reminder_dt=[$subs_reminder_dt]\n";
		$INTERCOM_SHOP['subscription_end_dt']=strtotime("$subscription_end_dt 23:59:59");
		$INTERCOM_SHOP['subs_reminder_dt']=strtotime(date("Y-m-d",strtotime($user_shop_row[$i]['subscription_end_date']." -30 day"))." 23:59:59");


		// DEBUG INFO 
		/*$INTERCOM_SHOP['created_at_org']=$user_shop_row[$i]['created_dt'];
		$INTERCOM_SHOP['subscription_end_dt_org']=$user_shop_row[$i]['subscription_end_date']." 23:59:59";
		$INTERCOM_SHOP['max_no_of_products_org']=$user_shop_row[$i]['max_no_of_products'];
		$INTERCOM_SHOP['subscription_is_trial_org']=$user_shop_row[$i]['subscription_is_trial'];
		$INTERCOM_SHOP['subscription_is_renewed_org']=$user_shop_row[$i]['subscription_is_renewed'];*/

		// Status
		# subscription will end within 15 days of creating
		# the shop, then he is still in trial
		if ( $user_shop_row[$i]['subscription_is_trial'] ) $INTERCOM_SHOP['status']='TRIAL';
		elseif ( $user_shop_row[$i]['subscription_is_renewed'] ) $INTERCOM_SHOP['status']='RENEWED';
		else $INTERCOM_SHOP['status']='PAID';

		// Plan
		if ( $user_shop_row[$i]['subscription_is_trial'] ) {
			$INTERCOM_SHOP['plan']='FREE';
		} else {
			switch ( $user_shop_row[$i]['max_no_of_products'] ) {
				case 500: 
					$INTERCOM_SHOP['plan']='QUICKY'; 
					break;
				case 2000: 
					$INTERCOM_SHOP['plan']='STARTER';
					break;
				case 10000: 
					$INTERCOM_SHOP['plan']='PRO';
					break;
				case 25000: 
					$INTERCOM_SHOP['plan']='PREMIUM';
					break;
				default: $INTERCOM_SHOP['plan']='CUSTOM';
					break;
			}
		}

		// Subs Status
		$INTERCOM_SHOP['subs_status']=strtotime($user_shop_row[$i]['subscription_end_date']) > strtotime(date('Y-m-d')) ? 'ACTIVE' : 'EXPIRED';

		// Expires In
		$INTERCOM_SHOP['expires_in']=(strtotime($user_shop_row[$i]['subscription_end_date'])-strtotime(date('Y-m-d')))/(60*60*24);


		$INTERCOM_USER['company']=$INTERCOM_SHOP;
		//$INTERCOM_USER['companies']['id']=$shop_id;
		//$INTERCOM_USER['companies']['name']=$user_shop_row[$i]['domain'];
		//$INTERCOM_USER['companies']['max_no_of_products']=120;
		//$INTERCOM_USER['companies']['plan']='PAID';

		//print_arr($INTERCOM_USER);
		LOG_MSG("INFO","################## Sending Shop_id:[$shop_id] Domain:[".$user_shop_row[$i]['domain']."]  User:[".$user_shop_row[$i]['fname']."] </pre>");
		if (defined("CLI_MODE")) echo date("d-m-Y H:i:s").": Sending Shop_id:[$shop_id] Domain:[".$user_shop_row[$i]['domain']."]  User:[".$user_shop_row[$i]['email_id']."]\n";
		$intercom = new Intercom('e5d8196714cf0bf945bd764be170dd41c22dc998', '70d4cdfa27209f16a53a211bce5de077f8e84d94');
		$intercom_resp=$intercom->updateUser($user_shop_row[$i]['user_id'],
							$user_shop_row[$i]['email_id'],
							$user_shop_row[$i]['fname'],
							$INTERCOM_USER
						);
		
		
		
		//LOG_ARR("INFO","INTERCOM DATA FOR USER $email_id",$INTERCOM_USER);
		//LOG_ARR("INFO","INTERCOM DATA FOR SHOP $shop_id",$INTERCOM_SHOP);
		//LOG_ARR("INFO","INTERCOM RESPONSE",$intercom_resp);
		if ( $i!=0 && $i % 25 ) sleep(10);
	} // END user_row loop

	LOG_MSG('INFO',"intercom_send_data(): END");

	return true;
}

function intercom_curlpost_test() {

	LOG_MSG("INFO","intercom_curlpost_test(): ##################################################################START");

	LOG_ARR("INFO","POST",$_POST);
	LOG_ARR("INFO","GET",$_GET);
	parse_str(file_get_contents("php://input"),$put);
	LOG_ARR("INFO","PUT",$put);


	LOG_MSG("INFO","intercom_curlpost_test(): ###################################################################END");
	$json_response=array();
	$json_response['status']='OK';
	$json_response['post']=$_POST;
	$json_response['get']=$_GET;
	$json_response['put']=$put;
	echo json_encode($json_response);
	exit;}


function validate_imagefile( $image_file_path ) {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"validate_imagefile(): START image_file_path=[$image_file_path]");

	$response["STATUS"]="ERROR";
	$response["MESSAGE"]="";

	/**********************************************************************/
	/*  Validate the file contents                                        */
	/**********************************************************************/
	// Step 1: Check filesize
	$image_file_size=filesize($image_file_path);

	if ( $image_file_size > MAX_IMAGE_SIZE ) {														// Image size exceeds the maximum limit
		$response["MESSAGE"]="The Image Size exceeds the maximum upload size(".MAX_IMAGE_SIZE.")";
		return $response;
	} else if ( $image_file_size < MIN_IMAGE_SIZE ) {												// Image size exceeds the minimum limit
		$response["MESSAGE"]="The Image Size is lower than the minimum upload size(".MIN_IMAGE_SIZE.")";
		return $response;
	}

	// Step 2: Check if it is an image
	if ( !$image_info_array=getimagesize($image_file_path) ) {										// Get the image properties
		$response["MESSAGE"]="The file is not an image file";
	} else {
		if( !in_array( $image_info_array[2], array( IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG ) ,true ) ) {
			$response["MESSAGE"]="The image should be of the format jpg, jpeg ,png or gif";
			return $response;
		}
	}

	$response["STATUS"]="OK";
	$response["MESSAGE"]="The image is valid";
	LOG_MSG('INFO',"validate_imagefile(): END");
	return $response;
}


function generate_imagename( $image_file_path ) {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"generate_imagename(): START image_file_path=[$image_file_path]");

	/**********************************************************************/
	/*  Validate and clean up the filename                                */
	/**********************************************************************/
	// Step 3: Get the base filename
	$ext = pathinfo($image_file_path, PATHINFO_EXTENSION);
	$uploaded_image_name = basename($image_file_path, ".".$ext);									// Extract image name

	// step 4. Check image extension type
	$image_extension=make_clean_url( pathinfo( $image_file_path, PATHINFO_EXTENSION ) );			// Extract image extension
	if ( $image_extension != 'jpg' &&
		 $image_extension != 'jpeg' &&
		 $image_extension != 'png' &&
		 $image_extension != 'gif' ) {
			return false;
	}

	// Step 5: Clean the filename 
	$new_image_name=substr( make_clean_url( $uploaded_image_name ), 0, 130 ).".".date('YmdHis').".".$image_extension; 	// New image name

	LOG_MSG('INFO',"generate_imagename(): END");
	return $new_image_name;
}



?>
