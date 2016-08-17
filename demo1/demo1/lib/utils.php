<?php
if (!defined('IGNORE_SWIFT')) require_once 'lib/swiftmailer/swift_required.php';  // don't include it when running from scripts

/**********************************************************************/
/*                          SEND SMS                                  */
/**********************************************************************/
function send_sms($to, $message) {

	LOG_MSG('INFO',"send_sms(): START to=[$to]");

	// Retrieve SMS Gateway Details
	$smsgateway_resp=db_lib_smsgateway_select();
	if ( $smsgateway_resp[0]['STATUS'] != 'OK' ) {
		LOG_MSG('ERROR',"send_sms(): Error loading sms gateway");
	}

	// Send SMS only if Gateway is found
	if ( $smsgateway_resp[0]['NROWS'] == 1 ) {
		$plain_message=$message;
		$smsgateway_id=$smsgateway_resp[0]['smsgateway_id'];

		$status='FAILED';
		$username=$smsgateway_resp[0]['username'];
		$password=$smsgateway_resp[0]['password'];
		$api_key=$smsgateway_resp[0]['api_key'];
		$default_sender_id=$smsgateway_resp[0]['default_sender_id'];
		$to=urlencode($to);
		$message=urlencode($message);
		$gateway_url=$smsgateway_resp[0]['gateway_url'];

		// Generate the URL base on the provider
		if ( $smsgateway_resp[0]['name'] == 'SolutionsInfini' ) {
			// http://alerts.sinfini.com/api/web2sms.php?workingkey=##API_KEY##&sender=##DEFAULT_SENDER_ID##&to=##MOBILE_TO##&message=##MESSAGE##
			$search=array('##API_KEY##', '##DEFAULT_SENDER_ID##', '##MOBILE_TO##', '##MESSAGE##');
			$replace=array($api_key, $default_sender_id, $to, $message);
			$url=str_replace($search,$replace,$gateway_url);
		} elseif ( $smsgateway_resp[0]['name'] == 'SMSGupshup' ) {
			// http://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage&send_to=##MOBILE_TO##&msg=##MESSAGE##&msg_type=TEXT&userid=##USERNAME##&auth_scheme=plain&password=##PASSWORD##&v=1.1&format=text	
			$search=array('##USERNAME##', '##PASSWORD##', '##MOBILE_TO##', '##MESSAGE##');
			$replace=array($username, $password, $to, $message);
			$url=str_replace($search,$replace,$gateway_url);
		}

		// Send SMS
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response=curl_exec($ch);
		curl_close($ch);     
		if ( strpos($response,'GID') ) $status='SUCCESS';
		LOG_MSG('INFO',"send_sms(): $response");

		// Add SMS Sent Details
		$smssent_resp=db_smssent_insert(
							$smsgateway_id,
							$default_sender_id,
							$to,
							$plain_message,
							$url,
							$response,
							$status);
		if ( $smssent_resp['STATUS'] != 'OK' ) {
			LOG_MSG('ERROR',"send_sms(): Error while inserting in SMSSent talble from=[$from] to=[$to]");
		}
	}

	LOG_MSG('INFO',"send_sms(): END");
	return true;
}

/**********************************************************************/
/*                          SEND EMAIL                                */
/**********************************************************************/
function send_email($to,$from,$cc='',$bcc='',$subject,$message,$attachments_arr='',$plain_message='',$field_type="pdf") {

	LOG_MSG('INFO',"send_email(): START EMAILER_HOST=[".EMAILER_HOST."] to=[$to] from=[$from] cc=[$cc] bcc=[$bcc] subject=[$subject]");

	// Defaults
	$EOL = PHP_EOL;
	$separator = md5(time());
	if (!$bcc) $bcc=EMAIL_BCC;

	 // Copy of the message without the attachment details
	 // required to insert into the db 
	if ( $plain_message == '' ) $plain_message=$message;
	$plain_subject=$subject;

	// The subject should have the shop domain 
	if ( defined('SHOP_NAME') ) $subject  = "[".SHOP_NAME."] $subject";
	else $subject  = "[".SITE_NAME."] $subject";

	// common headers
	$headers = "From: $from $EOL";
	$headers .= "CC: $cc $EOL";   
	$headers .= "Bcc: $bcc $EOL";
	
	if ($attachments_arr) {
		// main header
		$headers .= "MIME-Version: 1.0".$EOL; 
		$headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

		// body
		//$body = "--".$separator.$EOL;
		$body = "Content-Transfer-Encoding: 7bit".$EOL.$EOL;
		$body .= "--".$separator.$EOL;
		$body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$EOL;
		$body .= "Content-Transfer-Encoding: 8bit".$EOL.$EOL;
		$body .= $message.$EOL;

		// attachment
		$attachment = chunk_split(base64_encode($attachments_arr[0]['data']));
		$body .= "--".$separator.$EOL;
		$body .= "Content-Type: application/octet-stream; name=\"".$attachments_arr[0]['filename']."\"".$EOL; 
		$body .= "Content-Transfer-Encoding: base64".$EOL;
		$body .= "Content-Disposition: attachment".$EOL.$EOL;
		$body .= $attachment.$EOL;
		$body .= "--".$separator."--";
		$message=$body;
	} else {
		$headers.="Content-Type: text/html $EOL";
		$headers.="MIME-Version: 1.0 $EOL";
		//$headers.="charset=utf-8 $EOL";
		$headers.="Content-Transfer-Encoding: 8bit $EOL";
		$headers.="X-Mailer: Shopnix - eCommerce Solution $EOL";
	}

	// Setup parameters
	$status='SUCCESS';
	if (EMAILER_HOST == 'LOCAL' ) {
		$emailer_host=$_SERVER['SERVER_NAME'];
		$resp=mail($to, $subject, $message, $headers); // SEND EMAIL
		if ( $resp ) $status='SUCCESS';
		else $status='FAILED';
	} elseif (EMAILER_HOST == 'REMOTE' ) {
		$headers .="From:$from \nCC: $cc \nBcc: $bcc";
		$from	= urlencode($from);
		$to	= urlencode($to);
		$cc	= urlencode($cc);
		$bcc	= urlencode($bcc);
		$subject= urlencode($subject);
		$url	="http://mitnix.in/snix/modules/utils/emailer.php?to=$to&from=$from&cc=$cc&bcc=$bcc&sub=$subject";
		$resp=curl_post($url,$message);	// SEND EMAIL
		$emailer_host='cloudnix.com';
	} elseif (EMAILER_HOST == 'MANDRILL' ) {
		// Setup data
		$sw_from = convert_email($from);
		$sw_to = convert_email($to);
		$text = strip_tags($plain_message);
		$html = $plain_message;
		$emailer_host='Mandrill';

		// Setup connection info
		$transport = Swift_SmtpTransport::newInstance('smtp.mandrillapp.com', 587);
		$transport->setUsername(SMTP_USERNAME);
		$transport->setPassword(SMTP_PASSWORD);
		$swift = Swift_Mailer::newInstance($transport);

		// Setup Data object
		$message = new Swift_Message($subject);
		$message->setFrom($sw_from);
		$message->setBody($html, 'text/html');
		$message->setTo($sw_to);
		$message->addPart($text, 'text/plain');
 		if ($bcc) {
			$sw_bcc=convert_email($bcc);
			//echo "<pre>Setting BCC[$bcc] to [".print_r($sw_bcc,true)."]</pre>";
			$message->setBcc($sw_bcc);
		}
		if ($cc) {
			$sw_cc=convert_email($cc);
			//echo "<pre>Setting CC[$cc] to [".print_r($sw_cc,true)."]</pre>";
			$message->setCc($sw_cc);
		}



		if ($attachments_arr) {
			// Create the attachment with your data
			$attachment = Swift_Attachment::newInstance($attachments_arr[0]['data'], $attachments_arr[0]['filename'], "application/$field_type");

			// Attach it to the message
			$message->attach($attachment);
		}

		// Send mail
		if ($recipients = $swift->send($message, $failures)) {
			$resp=true;
			$status='SUCCESS';
		} else {
			LOG_MSG('ERROR',"send_email(MANDRILL): Error sending email=[".print_r($failures,true)."]");
			$resp=false;
			$status='FAILED';
		}
	} else {
		LOG_MSG("INFO","EMAILER_HOST is OFF. Not sending email");
		$status='NOT SENT';
		$resp=true;
	}

	if ( defined('SHOP_ID') && $from != 'security@shopnix.in' ) {
		// Don't store security emails
		$email_resp=db_emails_insert(
						$from,
						$to,
						$cc,
						$bcc,
						$plain_subject,
						$plain_message,
						$status,
						$headers,
						$emailer_host);
	if ( $email_resp['STATUS'] != 'OK' ) {
		LOG_MSG('ERROR',"send_email(): Error while inserting in EMails talble from=[$from] to=[$to]");
	}
	}


	LOG_MSG("INFO","
	******************************EMAIL START [$status]******************************
	TO: [$to]
	$headers
	SUBJECT:[$subject]
	$plain_message
	******************************EMAIL END******************************");

	return $resp;
}

/* Function to convert email address from 
 * Shopnix Support <support@shopnix.in> => support@shopnix.in => Shopnix Support
 */
function convert_email($src_email_addr) {

	$final_emails_array=array();
	$src_email_addr=trim($src_email_addr,',');	// Clean up email IDS


	$email_addr_arr=preg_split("/,/",$src_email_addr);
	//echo "<pre>convert_email(): ============================================ </pre>";
	//echo "<pre>convert_email(): SRC EMAIL [$src_email_addr]</pre>";
	//echo "<pre>convert_email(): SPLIT SRC EMAIL".print_r($email_addr_arr,true)."</pre>";

	foreach ($email_addr_arr as $email_addr) {
		//echo "<pre>     convert_email(): EACH EMAIL=[$email_addr]</pre>";
		// Split it by '<' to seperate the email id from the name
		$email_addr=preg_split("/</",$email_addr);
		switch (count($email_addr)) {
			case 1:	// Only email ID is present
					$email_id=trim(str_replace(">","",$email_addr[0]));
					$name=$email_id;
					break;
			case 2:	// Both name & email ID is present
					$name=trim($email_addr[0]);
					$email_id=trim(str_replace(">","",$email_addr[1]));
					break;
		}
		$final_emails_array[$email_id]=$name;
		//echo "<pre>     convert_email(): FINAL EMAILS ARRAY=[".print_r($final_emails_array,true)."]</pre>";
	}

	//$a=array("asdsad","adsdsa"=>"asdsad","bbbb");
	//echo "<pre>convert_email(): EXPECTED FORMAT=[".print_r($a,true)."]</pre>";

	//echo "<pre>convert_email(): RETURNING FINAL EMAILS ARRAY=[".print_r($final_emails_array,true)."]</pre>";
	return $final_emails_array;
}


function curl_post($url,$post_data) {

	LOG_MSG('INFO',"curl_post(): URL=[$url]");
	//LOG_MSG('DEBUG',"curl_post(): POST_DATA=[$post_data]");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER, 1);

	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/html'));
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);


	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//LOG_ARR('INFO','curl array',$ch);
	$response = curl_exec($ch);
	if (!$response) {
		LOG_MSG('ERROR', "curl_post():  error message=[".curl_error($ch)."]\n");
		return false;
	}
	LOG_MSG('INFO', "curl_post(): END response=[$response]");
	curl_close($ch);
	return $response;
}


// Checks if a remote file exists. 
// Parameter: URL - Url of the file to check
// Returns :
// 404 - no file found
// 200 - file found
function curl_check($url) {

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);

	$response = curl_exec($ch);
	LOG_MSG('INFO',"curl_get(): [$url] response=[$response]");

	return curl_getinfo($ch, CURLINFO_HTTP_CODE);
}


/**********************************************************************/
/*                          GET ARGUMENT                              */
/**********************************************************************/
// Function to get an argument from the form (either from GET or POST)
// A wrapper helps ensure we properly check for isset() and in future
// ensure that the user parameter passed in is actually safe
function get_arg($ARR,$var) {
	if (isset($ARR[$var])) { 
		return $ARR[$var]; 
	} else {
		return "";
	}
}


/**********************************************************************/
/*                          TODAY'S DATE                              */
/**********************************************************************/
// Function to get todays date. 
function today($time=false) {
	if ($time) return date('Y-m-d-h-i-sa');
	else return date('Y-m-d');
}


/**********************************************************************/
/*                        CURL A GET REQUEST                          */
/**********************************************************************/
// Function to make an HTTP request using the curl lib
// Errors are logged into the apache error.log
// while the user sees a standard error message (shown where the
// function is called)
function curl_get($url,$POST=false) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	if (!$response) {
		LOG_MSG('ERROR',"curl_get(): MSG=[".curl_error($ch)."]\nURL=[".$url."] ");
		return false;
	}
	curl_close($ch); 
	return true;
}


/**********************************************************************/
/*                       GET REAL IP OF USER                          */
/**********************************************************************/
function get_ip()
{
	$ip='0.0.0.0';
	if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	{
	  $ip=$_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	{
	  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif (isset($_SERVER['REMOTE_ADDR']))
	{
	  $ip=$_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**********************************************************************/
/*                              RELOAD FORM                            */
/**********************************************************************/
// Gets all POST elements and then puts them into the row array. This
// row array is then used in all forms to load in the default value of
// the fields
function reload_form() {
	global $ROW, $ERROR_MESSAGE;

	// Only reload if it hasn't been reloaded already
	if ( $ERROR_MESSAGE && ( !isset($ROW[0]['STATUS']) || $ROW[0]['STATUS'] != 'RELOAD')  ) {
		$ROW[0]=$_POST;
		$ROW[0]['STATUS']='RELOAD';
		LOG_MSG('INFO',"reload_form(): ROW=[".print_r($ROW,true)."]");
	}
}

function is_form_reloaded() {
	global $ROW;

	if ( isset($ROW) && isset($ROW[0]) && get_arg($ROW[0],'STATUS') === 'RELOAD' ) return true;
	return false;
}



/**********************************************************************/
/*                         VALIDATE PARAM                             */
/**********************************************************************/
// Input validation proc
function validate($name,$value,$minlen,$maxlen,$datatype="",$min_val="",$max_val="",$regexp="") {	//SAT0112:To prevent entering values which is not less than min_val and not greater than max val

	$resp=true;

	//echo "Validating: name=".$name." val=".$value." min=".$minlen." maxlen=".$maxlen." type=".$datatype." regexp=".$regexp."<br>";

	// If the value is empty and the field is not mandatory, then return
	if ( (!isset($minlen) || $minlen == 0) && $value == "" ) {
		return true;
	}

	// Empty Check
	// Changed to === to ensure 0 does not fail 
	if ( isset($minlen) && $minlen > 0 && $value === "" ) {
		add_msg("ERROR",$name." cannot be empty. "); 
		return false;
	}

	//echo "count($value)=[".preg_match("/^[0-9]+$/","12344a4")."]<br>";
	// MIN LEN check
	if ( isset($minlen) && strlen($value) < $minlen ) {
		add_msg("ERROR",$name." should be atleast ".$minlen." characters long. "); 
		return false;
	}

	// MAX LEN check
	if ( $datatype == 'enum' ) { 
		$enum_str=$maxlen;
		unset($maxlen);
	}

	if ( isset($maxlen) && strlen($value) > $maxlen ) {
		add_msg("ERROR",$name." cannot be longer than ".$maxlen." characters. "); 
		return false;
	}

	// CUSTOM REGEXP check
	if ( isset($regexp) && !preg_match("/$regexp/",$value) ) {
		add_msg("ERROR",$name." is not valid. "); 
		return false;
	}

	// MIN value check
	if( ($min_val !== '' && $value < $min_val) ) {
		add_msg("ERROR",$name." cannot be less than ".$min_val.". "); 
		return false;
	}

	// MAX value check
	if( ($max_val !== '' && $value > $max_val) ) {
		add_msg("ERROR",$name." cannot be greater than ".$max_val.". "); 
		return false;
	}
	// STANDARD DATATYPES check
	if ( isset($datatype) ) {
		switch ($datatype) {
			case "int":
				if ( filter_var($value, FILTER_VALIDATE_INT) === false  ) {
					add_msg("ERROR",$name." should contain only digits. "); 
					return false;
				} 
				break;
			case "decimal":
				if ( filter_var($value, FILTER_VALIDATE_FLOAT) === false ) {
					add_msg("ERROR",$name." should contain only digits. "); 
					return false;
				} 
				break;
			case "PASSWORD":
			case "char": // anything
			case "varchar": // anything
			case "text": // anything
				return true;
				break;
			case "bigint":
			case "tinyint":
				if (!preg_match("/^[0-9]+$/",$value)) {
					add_msg("ERROR",$name." should contain only digits. "); 
					return false;
				} 
				break;
			case "date":
				$arr=preg_split("/-/",$value); // splitting the array
				$yy=get_arg($arr,0); // first element of the array is month
				$mm=get_arg($arr,1); // second element is date
				$dd=get_arg($arr,2); // third element is year
				if( $dd == "" || $mm == "" || $yy == "" || !checkdate($mm,$dd,$yy) ){
					add_msg("ERROR",$name." is not a valid date, should be of the format YYYY-MM-DD "); 
					return false;
				}
				break;
			/*case "PASSWORD":
				if (!preg_match("/^[a-zA-Z\-_0-9]+$/",$value)) {
					add_msg("ERROR",$name." can contain only alphabets,numbers,'-' and '_'. <br/>"); 
					return false;
				} 
				break;		
			*/
			case "SIMPLE_STRING": // can only have alphabets, spaces, dots, -'s or +
				if (!preg_match("/^[a-zA-Z0-9\.\s\-\+]+$/",$value)) {
					add_msg("ERROR",$name." should contain only alphabets, numbers, spaces '.', '-' or '+'. "); 
					return false;
				} 
				break;
			case "EMAIL":
				if ( filter_var($value, FILTER_VALIDATE_EMAIL) == false ) {
					add_msg("ERROR",$name." is not valid, should be of the format abc@xyz.com. "); 
					return false;
				}
				break;
			case "MOBILE":
				if (!preg_match("/^[0-9]+$/",$value)) {
					add_msg("ERROR",$name." is not valid, should be of the format 919123456789 "); 
					return false;
				} 
				break;
			case 'FILENAME':
				if ($value != basename($value) || !preg_match("/^[a-zA-Z0-9_\.-]+$/",$value) || !preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $value)) {
					add_msg('ERROR', "Invalid $name. ");
					return false;
				}
				break;
			case 'enum':
				$enum_arr=explode(',',$enum_str);
				if ( in_array($value, $enum_arr) ) {
					add_msg('ERROR', "Invalid $name.");
					return false;
				}
				break;
			default:
				add_msg("ERROR",$name." is not valid. Please re enter."); 
				return false;
		}
	}

	return true;
}




/**********************************************************************/
/*                    Generate random password                        */
/**********************************************************************/
// Mask Rules
// # - digit
// C - Caps Character (A-Z)
// c - Small Character (a-z)
// X - Mixed Case Character (a-zA-Z)
// ! - Custom Extended Characters
function gen_pass($mask) {
  $extended_chars = "!@#$%^&*()";
  $length = strlen($mask);
  $pwd = '';
  for ($c=0;$c<$length;$c++) {
    $ch = $mask[$c];
    switch ($ch) {
      case '#':
        $p_char = rand(0,9);
        break;
      case 'C':
        $p_char = chr(rand(65,90));
        break;
      case 'c':
        $p_char = chr(rand(97,122));
        break;
      case 'X':
        do {
          $p_char = rand(65,122);
        } while ($p_char > 90 && $p_char < 97);
        $p_char = chr($p_char);
        break;
      case '!':
        $p_char = $extended_chars[rand(0,strlen($extended_chars)-1)];
        break;
    }
    $pwd .= $p_char;
  }
  return $pwd; 
}


// Encrypt the password
function encrypt_pass($password){
	return md5(PASSWORD_SALT.$password);
}



/**********************************************************************/
/*                         Version info                               */
/**********************************************************************/
function ver() {

	// Makes a version number using the current dir name
	// dir name should end with YYYYMMDD eg: adfsafdsf20101214
	$dir=getcwd();
	$dir_len=strlen($dir);
	$ver=substr($dir,-6);

	
	$ver_p1=substr($ver,1,1);
	$ver_p2=substr($ver,2,2);
	$ver_p3=substr($ver,4,2);

	$ver=$ver_p1.".".$ver_p2.".".$ver_p3;

	return $ver	;

} 

function print_arr($arr) {
	echo "<pre>ARR=[".print_r($arr,true)."]</pre>";
}


/**********************************************************************/
/*                         USER MESSAGES                              */
/**********************************************************************/
function add_msg($type="SUCCESS",$msg="") {
	global $ERROR_MESSAGE, $SUCCESS_MESSAGE, $NOTICE_MESSAGE;
	global $DEBUG_MESSAGE;	

	LOG_MSG('INFO',"<<USER MESSAGE>>> $type: $msg");

	switch($type) {
		case "DEBUG": 
			if ( $DEBUG_MESSAGE ) $DEBUG_MESSAGE.='<br/>'; 
			$DEBUG_MESSAGE.=$msg;
			break;
		case "ERROR": 
			if ( $ERROR_MESSAGE ) $ERROR_MESSAGE.='<br/>'; 
			$ERROR_MESSAGE.=$msg;
			break;
		case "NOTICE": 
			if ( $NOTICE_MESSAGE ) $NOTICE_MESSAGE.='<br/>'; 
			$NOTICE_MESSAGE.=$msg;
			break;
		case "SUCCESS": 
		default:
			if ( $SUCCESS_MESSAGE ) $SUCCESS_MESSAGE.='<br/>'; 
			$SUCCESS_MESSAGE.=$msg;
			break;
	}
}


function show_msgs() {
	global $ERROR_MESSAGE, $SUCCESS_MESSAGE, $NOTICE_MESSAGE;
	include(NAV_TEMPLATE_DIR."messages.html");
	clear_msgs();
}

function clear_msgs() {
	global $ERROR_MESSAGE, $SUCCESS_MESSAGE, $NOTICE_MESSAGE;
	$ERROR_MESSAGE="";
	$SUCCESS_MESSAGE="";
	$NOTICE_MESSAGE="";
}



/******************************************************************************/
/* Function: get_page_params($count)                                          */
/*           generates the different page params                              */
/******************************************************************************/
function get_page_params($count) {

	global $ROWS_PER_PAGE;
	
	if ($ROWS_PER_PAGE == '') $ROWS_PER_PAGE=10;

	$page_arr=array();

	$firstpage = 1;
	$lastpage = intval($count / $ROWS_PER_PAGE);
	$page=(int)get_arg($_GET,"page");


	if ( $page == "" || $page < $firstpage ) { $page = 1; }	// no page no
	if ( $page > $lastpage ) {$page = $lastpage+1;}			// page greater than last page
	//echo "<pre>first=$firstpage last=$lastpage current=$page</pre>";

	if ($count % $ROWS_PER_PAGE != 0) {
		$pagecount = intval($count / $ROWS_PER_PAGE) + 1;
	} else {
		$pagecount = intval($count / $ROWS_PER_PAGE);
	}
	$startrec = $ROWS_PER_PAGE * ($page - 1);
	$reccount = min($ROWS_PER_PAGE * $page, $count);

	$currpage = ($startrec/$ROWS_PER_PAGE) + 1;


	if($lastpage==0) {
		$lastpage=null;
	} else {
		$lastpage=$lastpage;
	}

	if($startrec == 0) {
		$prevpage=null;
		$firstpage=null;
		if($count == 0) {$startrec=-1;}
	} else {
		$prevpage=$currpage-1;
	}
	
	if($reccount < $count) {
		$nextpage=$currpage+1;
	} else {
		$nextpage=null;
		$lastpage=null;
	}

	$appstr="&page="; 

	// Link to PREVIOUS page (and FIRST)
	if($prevpage == null) {
		$prev_href="#";
		$first_href="#";
		$prev_disabled="disabled";
	} else {
		$prev_disabled="";
		$prev_href=$appstr.$prevpage; 
		$first_href=$appstr.$firstpage; 
	}

	// Link to NEXT page
	if($nextpage == null) {
		$next_href = "#";
		$last_href = "#";
		$next_disabled="disabled";
	} else {
		$next_disabled="";
		$next_href=$appstr.$nextpage; 
		$last_href=$appstr.$lastpage; 
	}

	if ( $lastpage == null ) $lastpage=$currpage;

	$page_arr['page_start_row']=$startrec;
	$page_arr['page_row_count']=$reccount;

	$page_arr['page']=$page;
	$page_arr['no_of_pages']=$pagecount;

	$page_arr['curr_page']=$currpage;
	$page_arr['last_page']=$lastpage;

	$page_arr['prev_disabled']=$prev_disabled;
	$page_arr['next_disabled']=$next_disabled;

	$page_arr['first_href']=$first_href;
	$page_arr['prev_href']=$prev_href;
	$page_arr['next_href']=$next_href;
	$page_arr['last_href']=$last_href;

	//LOG_MSG('INFO',"Page Array=".print_r($page_arr,true));
	return $page_arr;
}

function url($replace_key=NULL,$replace_val=NULL) {
	global $URL_BASE;

	$url=$URL_BASE;
	$replaced=0;

	foreach ($_GET as $key => $value) {
		
		if ( $key == 'mod' || $key == 'go' ) continue; 
		
		//LOG_MSG('INFO',"GOT Key: [$key]; Value: [$value] \n");
		// REPLACE
		if ( $replace_key && $replace_val && $replace_key == $key ) {
			$value=$replace_val;
			$replaced=true;
		}
		// REMOVE
		else if ( $replace_key && !$replace_val && $replace_key == $key ) {
			$replaced=true;
			continue;
		}
		
		//LOG_MSG('INFO',"SET Key: [$key]; Value: [$value] \n");
		$url.="&".$key."=".$value;
	}
	// If not already replaced then replace here
	if ( $replace_key && !$replaced ) {
		$url.="&".$replace_key."=".$replace_val;
	}
	
	return $url;
	
}



//URL: any url without the base eg: bill_id=23&val=abc. NULL will return base url
//URL_BASE: the base url incase its different from current base url. Null will use global base url
//URL_TYPE: can be 'AJAX' if you want to override the AJAX_MODE variable
function make_url($url=NULL,$url_base=NULL,$trigger=false) {
	global $URL_BASE;
	
	if ( $url_base == NULL ) { $url_base = $URL_BASE; }

	if ( AJAX_MODE ) {
		if ( $url == NULL ) {
			$url="load(".$url_base.")";
		} else {
			$url="load(".$url_base.",'&".$url."')";
		}
		if ( $trigger) { $url='href="#" onclick="'.$url.'"'; };
	} else {
		if ( $url == NULL ) {
			$url=$url_base;
			//$url="document.location.href='".$url_base."'; return false;";
		} else {
			$url=$url_base."&".$url;
			//$url="document.location.href='".$url_base."&".$url."'; return false;";
		}
		if ( $trigger) { $url='href="'.$url.'"'; };
	}
	//LOG_MSG('INFO',"make_url(): =========$url==============");
	return $url;
}

function make_base_url($mod=NULL,$ent=NULL) {

	if ( AJAX_MODE ) {
		if ( $mod == NULL || $ent == NULL ) {
			$base_url="return false;"; 	// No base url, so do nothing
		} else {
			$base_url="'".$mod."','".$ent."'";
		}
	} else {
		if ( $mod == NULL || $ent == NULL ) {
			$base_url="index.php?a=reset";
		} else {
			$base_url="index.php?mod=".$mod."&ent=".$ent;
		}
	}
	return $base_url;
}


function CATCH_ERROR($code, $message, $errFile, $errLine) {

	//Set message/log info
	$subject = "[".SITE_NAME." ERROR:".date("F j g:ia] ").": ".$message;
	$body = "
		\t\tFILE: $errFile:$errLine\n
		\t\tStack Trace:\n
		\t\t".print_r(_debug_string_backtrace(),true)."\n
		";
	//The same subject line and body of the email will get written to the error log.
	LOG_MSG('FATAL',"$subject\n $body");


	// Redirect to home
	//add_msg('ERROR',STANDARD_ERROR_MSG);
	header ("Location: ". GENERIC_ERR_PAGE );
	exit;
}


function LOG_ARR($level='INFO',$arr_name, $arr) {
	LOG_MSG($level,"===================ARRAY:$arr_name=====================\n".print_r($arr,true));
}

function LOG_MSG($level,$msg)
{
	global $MOD;
	if (defined('CLI_MODE')) return true;

	// LOGGED IN
	if ( isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === 1 ) {	//SNT0098: To prevent cross session issue with shop
		$utype='N';
		$id="<".$utype.":".$_SESSION['user_id'].":".$_SESSION['email_id'].">";
	// NOT LOGGED IN
	} else {
		$id="<".get_ip().":GUEST>";
	}

	// If we are processing a module, then log it in as well
	if ($MOD) {
		$msg="[$MOD] $msg";
	}

	$message=date("h:i:sa:")."  ".$id." ".$level.": ".$msg."\n";

	$log_message=true;
	switch ($level) {
		case 'ERROR':
				$st=_debug_string_backtrace();
				$message.="=================================STACK TRACE======================================\n".$message."=====================================================================================\n";
				break;
		case 'FATAL':
				$message.="=================================FATAL ERROR======================================\n".$message."=====================================================================================\n";
				break;
		case 'DEBUG':
				$log_message=false;
				break;
	}

	if ( $log_message ) {
		$fd = fopen(LOG_FILE, "a");
		fwrite($fd, $message);
		fclose($fd);
	}
}


function _debug_string_backtrace() {
    ob_start();
    debug_print_backtrace();
    return ob_get_clean();
} 

function set_go($action) {
	global $GO;
	$GO=$action;
	return;
}


function validate_file($file_arr,$max_size=MAX_IMAGE_SIZE) {


	if ($file_arr['error'] == true) {
		LOG_ARR('INFO','validate_file(): FILE ARRAY',$file_arr);
		switch ($file_arr['error']) {
			case 1:
			case UPLOAD_ERR_INI_SIZE:
				add_msg('ERROR','Sorry, the maximum file size allowed is '.($max_size/1024).'Kb. Please try again.');
				LOG_MSG('ERROR','1:The uploaded file exceeds the upload_max_filesize directive in php.ini');
				break;
			case 2:
			case UPLOAD_ERR_FORM_SIZE:
				add_msg('ERROR','Sorry, the maximum file size allowed is '.($max_size/1024).'Kb. Please try again.');
				LOG_MSG('ERROR','2:The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ');
				break;
			case 3:
			case UPLOAD_ERR_PARTIAL:
				add_msg('ERROR','Sorry, the maximum file size allowed is '.($max_size/1024).'Kb. Please try again.');
				LOG_MSG('ERROR','2:The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ');
				break;
			case 4:
			case UPLOAD_ERR_NO_FILE:
				return false;
				add_msg('ERROR',"No file was uploaded");
				LOG_MSG('ERROR','4:No file was uploaded');
				break;
			case 6:
			case UPLOAD_ERR_NO_TMP_DIR:
				add_msg('ERROR',"There was a system error uploading your file. Please try later.");
				LOG_MSG('ERROR','6:Missing a temporary folder');
				break;
			case 7:
			case UPLOAD_ERR_CANT_WRITE:
				add_msg('ERROR',"There was a system error uploading your file. Please try later.");
				LOG_MSG('ERROR','7:Failed to write file to disk');
				break;
			case 8:
			case UPLOAD_ERR_EXTENSION:
				add_msg('ERROR',"Invalid file extension");
				LOG_MSG('ERROR','2:File upload stopped by extension');
				break;
			default:
				add_msg('ERROR',"Error uploading the file");
				LOG_MSG('ERROR',$file_arr['error'].':Unkown error');
				break;
		}
		return false;
	}
	// Check image size
	if ( $file_arr['size'] > $max_size ) {
		add_msg('ERROR','Sorry, the maximum file size allowed is '.($max_size/1024).'Kb. Please try again.');
		return false;
	}
	return true;
}

function is_file_specified($file_arr) {
	LOG_ARR('INFO','is_file_specified(): file arr',$file_arr);
	if ($file_arr['error'] == '4' || $file_arr['error'] == UPLOAD_ERR_NO_FILE) {
		LOG_MSG('INFO',"is_file_specified(): returning FALSE");
		return false;
	} else {
		LOG_MSG('INFO',"is_file_specified(): returning TRUE");
		return true;
	}
}


function upload_image(	$html_img_name,
						$dest_img_file,
						$req_width='',
						$req_height='',
						$autocrop=0) {
	LOG_MSG("INFO","upload_image():START html_img_name = [$html_img_name],
										dest_img_file = [$dest_img_file],
										req_width = [$req_width],
										req_height = [$req_height],
										autocrop = [$autocrop]");

	if ( isset($_FILES[$html_img_name]) && validate_file($_FILES[$html_img_name]) ) {
		if ($_FILES[$html_img_name]['type'] != 'image/jpeg' && 
			$_FILES[$html_img_name]['type'] != 'image/pjpeg' && 
			$_FILES[$html_img_name]['type'] != 'image/png'	) {
			add_msg('ERROR','Sorry, you can only upload a jpg, jpeg or png image. Please try again.');
			LOG_MSG('ERROR',"upload_image(): Got file type=[".$_FILES[$html_img_name]['type']."]");
			return false;
		}
		LOG_ARR('INFO','upload_image(): FILES',$_FILES);

		//Crop/Resize the image
		if ( $req_width !== '' && $req_height !=='' ) {
			if ( !image_resize(get_arg($_FILES[$html_img_name],'tmp_name'), $req_width, $req_height,$autocrop) ) {
				add_msg('ERROR','There was an error uploading the image. Please try later');
				LOG_MSG('ERROR',"upload_image(); Error resizing the file");
				return false;
			}
		}

		// Copy the file to the uploaded directory
		if ( !copy(get_arg($_FILES[$html_img_name],'tmp_name'),$dest_img_file) )  {
			add_msg('ERROR','There was an error uploading the image. Please try later');
			LOG_ARR('INFO','upload_image(): FILES',$_FILES);
			LOG_MSG('ERROR',"upload_image(); Error copying file to the directory: [$dest_img_file]");
			return false;
		}
		LOG_MSG('INFO',"upload_image(): New File: is [$dest_img_file]");
		return true;
	}
	LOG_MSG("INFO","upload_image():END");
	return false;
}

// Generate a hash using the Global Hash key
function gen_hash($key,$number=false){
	if ($number) {
		$hash=abs(crc32(HASH_KEY.$key)) % 999999;
	} else {
		LOG_MSG('INFO','Algorithms: Blowfish['.CRYPT_BLOWFISH.'] md5['.CRYPT_MD5.'] ext_des['.CRYPT_EXT_DES.'] std_des['.CRYPT_STD_DES.']');
		$hash=crypt(HASH_KEY.$key);
	}
	LOG_MSG('INFO',"gen_hash(): KEY=[$key], NUMBER=[$number], HASH=[$hash]");

	return $hash;
}

// Converts a string to a string suitable for url format
// eg: Flipkat Store, Indiranagar => flipkart-store-indiranagar
// eg: ASF@#$%^$&^UJYTIU/..]\KL{>}<NVBDF AE#$@# => asf-ujytiu-kl-nvbdf-ae
function make_clean_url($str,$delimiter="-") {
	// 1. convert to lowercase
	// 2. Replace all special characters with a $delimiter(hypen by default)
	// 3. Remove multiple hypens
	// 4. Trim the hypens at the end
	return trim(preg_replace("/[$delimiter]+/",$delimiter,preg_replace('/[^0-9a-z]/',$delimiter,strtolower($str))),'-');
}

// Same as above but does not convert into lower case and replaces by space instead of -
function make_clean_str($str) {
	return ucwords(trim(preg_replace('/[ ]+/',' ',preg_replace('/[^0-9a-zA-Z\+]/',' ',strtolower($str))),' '));
}




//IF money_format() function is undefined then follows this function

if (!function_exists('money_format')) { //Required for UNIX

	function money_format($format, $number)
	{
		$regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'.
		 '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
		if (setlocale(LC_MONETARY, 0) == 'C') {
			setlocale(LC_MONETARY, '');
		}
		$locale = localeconv();
		preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
		foreach ($matches as $fmatch) {
			$value = floatval($number);
			$flags = array(
				'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ?
				  $match[1] : ' ',
				'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
				'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
				  $match[0] : '+',
				'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
				'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
			);
			$width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
			$left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
			$right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits'];
			$conversion = $fmatch[5];

			$positive = true;
			if ($value < 0) {
				$positive = false;
				$value  *= -1;
			}
			$letter = $positive ? 'p' : 'n';

			$prefix = $suffix = $cprefix = $csuffix = $signal = '';

			$signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
			switch (true) {
				case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
					$prefix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
					$suffix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
					$cprefix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
					$csuffix = $signal;
					break;
				case $flags['usesignal'] == '(':
				case $locale["{$letter}_sign_posn"] == 0:
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (!$flags['nosimbol']) {
				$currency = $cprefix .
				($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
				$csuffix;
			} else {
				$currency = '';
			}
			$space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';

			$value = number_format($value, $right, $locale['mon_decimal_point'],
			$flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
			$value = @explode($locale['mon_decimal_point'], $value);

			$n = strlen($prefix) + strlen($currency) + strlen($value[0]);
			if ($left > 0 && $left > $n) {
				$value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
			}
			$value = implode($locale['mon_decimal_point'], $value);
			if ($locale["{$letter}_cs_precedes"]) {
				$value = $prefix . $currency . $space . $value . $suffix;
			} else {
				$value = $prefix . $value . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
				STR_PAD_RIGHT : STR_PAD_LEFT);
			}
			$format = str_replace($fmatch[0], $value, $format);
		}
		return $format;
	}
}

// recursively copy a directory
function recurse_copy_dir($src,$dst) { 
	$dir = opendir($src); 
	$resp=mkdir($dst);
	LOG_MSG("INFO","creating directory $resp -> [$dst] ");
	while($dir && false !== ( $file = readdir($dir)) ) { 
		if (( $file != '.' ) && ( $file != '..' )) { 
			if ( is_dir($src . '/' . $file) ) { 
				recurse_copy_dir($src . '/' . $file,$dst . '/' . $file); 
			} 
			else { 
				copy($src . '/' . $file,$dst . '/' . $file); 
			} 
		} 
	} 
	closedir($dir); 
} 

// recursively remove a directory
function recurse_remove_dir($dir) {
	foreach(glob($dir . '/*') as $file) {
		if(is_dir($file))
			recurse_remove_dir($file);
		else
			unlink($file);
	}
	rmdir($dir);
}

function send_security_info($subject,$debug_message) {
	// Send all info to admin
	ob_start();
	echo "<pre>";
	echo "$debug_message<br>";
	echo "user_ip: ".get_ip()."<br>";
	echo "user_hostname: ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."<br>";
	echo "server: ".gethostname()."<br><br>";
	echo "<u>_POST</u><br>".print_r($_POST,true)."<br><br>";
	echo "<u>_GET</u><br>".print_r($_GET,true)."<br><br>";
	echo "<u>_REQUEST</u><br>".print_r($_REQUEST,true)."<br><br>";
	//echo "<u>_HTTP_REQUEST</u><br>".print_r($_HTTP_REQUEST,true)."<br><br>";
	echo "<u>_SERVER</u><br>".print_r($_SERVER,true)."<br><br>";
	echo "<u>_SESSION</u><br>".print_r($_SESSION,true)."<br><br>";
	echo "<u>_FILES</u><br>".print_r($_FILES,true)."<br><br>";
	echo "<u>apache_request_headers</u><br>".print_r(apache_request_headers(),true)."<br><br>";
	echo "<u>getallheaders</u><br>".print_r(getallheaders(),true)."<br><br>";
	echo "</pre>";
	$debug_message=ob_get_contents();		// Set mail content
	ob_get_clean();

	send_email('avinash@cloudnix.com','security@shopnix.in','','',$subject,$debug_message);
}


// Image resize/crop
function image_resize(	$source_image_path,
						$resize_width,
						$resize_height,
						$autocrop=0) {

	LOG_MSG("INFO","image_resize() :START source_image_path=[$source_image_path],
											resize_width=[$resize_width],
											resize_height=[$resize_height],
											autocrop=[$autocrop]");

	$is_streched=false;
	// As the resize dimension changes as the flow goes, store it in a variable for the padding purpose
	$org_resize_width=$resize_width;
	$org_resize_height=$resize_height;

	/******************************************************************/
	/*     STEP1:Get the image properties                             */
	/******************************************************************/
	// Get source image properties
	list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);


	/******************************************************************/
	/*              STEP2:Create image resource                       */
	/******************************************************************/
	switch ($source_image_type) {
		case IMAGETYPE_GIF:
			$source_gd_image = imagecreatefromgif($source_image_path);
			break;
		case IMAGETYPE_JPEG:
			$source_gd_image = imagecreatefromjpeg($source_image_path);
			break;
		case IMAGETYPE_PNG:
			$source_gd_image = imagecreatefrompng($source_image_path);
			break;
	}
	if ($source_gd_image == false) {
		LOG_MSG("INFO","image_resize() : Failed to create image resource source_gd_image=[$source_gd_image]");
		return false;
	}

	// Check whether the image is original image
	$is_org_image=false;
	if ( $resize_width == '' && $resize_height == '' ) $is_org_image=true;

	// Check whether the image resize is required, else skip to padding
	$is_img_resize=true;
	if ( !$is_streched && $resize_width >= $source_image_width && $resize_height >= $source_image_height ) $is_img_resize=false;

	// If original image or image resize is not required, skip all the steps till padding
	if ( !$is_org_image && $is_img_resize ) {

	// If the image dimensions are equal to the required dimensions
	if (($source_image_width == $resize_width) && ($source_image_height == $resize_height)) { return true; }

	// FIXED IMAGE - PROTRONICS
	// When the image width or height is too small, or if there is more difference in width and height
	// fixing the image will stretch or compress the image which will not look like the original image
	// To avoid this, we need to reduce the image by same percentage on all the sides. The percentage 
	// can be calculated based on the max resize width or height. 
	// If the modified width and height is greater than the resize width and height resp., then re-size with the minimum resize width or height
	// OUR LAST AIM IS EITHER WIDTH AND HEIGHT SHOULD NOT EXCEED THE ORIGINAL RESIZE WIDTH AND HEIGHT
	if ( !$is_streched ) {
		// Return if the image is smaller than the resize image
		LOG_MSG("INFO","image_resize() : ############## ORIGINAL IMAGE SIZE AND RESIZE ################
															source_image_width=[$source_image_width],
															source_image_height=[$source_image_height],
															resize_width=[$resize_width],
															resize_height=[$resize_height]");
		// Find the new resize_height
		if ( ($resize_width >= $resize_height && $resize_width <= $source_image_width) || 
			 ($resize_width < $resize_height && $resize_height > $source_image_height) ) {
				$resize_percent=($resize_width*100)/$source_image_width;
				$mod_resize_height=($resize_percent*$source_image_height)/100;

			// When mod_resize_height is > than original resize_height, then find the width based on the original resize_height
			if ( $mod_resize_height > $resize_height ) {
				LOG_MSG("INFO","image_resize() : mod_resize_height=[$mod_resize_height] > resize_height=[$resize_height]");
				$resize_percent=($resize_height*100)/$source_image_height;
				$resize_width=($resize_percent*$source_image_width)/100;
			} else {
				$resize_height=$mod_resize_height;
			}
		}
		// Find the new resize_width
		elseif ( ($resize_width >= $resize_height && $resize_width > $source_image_width) || 
				 ($resize_width < $resize_height && $resize_height <= $source_image_height) ) {
					$resize_percent=($resize_height*100)/$source_image_height;
					$mod_resize_width=($resize_percent*$source_image_width)/100;

			// When mod_resize_width is > than original resize_width, then find the height based on the original resize_width
			if ( $mod_resize_width > $resize_width ) {
			LOG_MSG("INFO","image_resize() : mod_resize_width=[$mod_resize_width] > resize_width=[$resize_width]");
				$resize_percent=($resize_width*100)/$source_image_width;
				$resize_height=($resize_percent*$source_image_height)/100;
			} else {
				$resize_width=$mod_resize_width;
			}
		}
		LOG_MSG("INFO","image_resize() : ############## MODIFIED IMAGE SIZE AND RESIZE ################
															source_image_width=[$source_image_width],
															source_image_height=[$source_image_height],
															resize_percent=[$resize_percent],
															resize_width=[$resize_width],
															resize_height=[$resize_height]");
	}

	if ( $autocrop==1 ) {
		/******************************************************************/
		/*     STEP3:Find the crop ratio                                  */
		/******************************************************************/
		$crop_ratio=($resize_width*$source_image_height)/($resize_height*$source_image_width);


		/******************************************************************/
		/*     STEP4:Calculate the cropped image width and height         */
		/******************************************************************/
		if ( $crop_ratio < 1 ){
			$new_height = $source_image_height;
			$new_width = $source_image_width*$crop_ratio;
		}else if( $crop_ratio > 1 ){
			$new_width = $source_image_width;
			$new_height = $source_image_height/$crop_ratio;
		}else if( $crop_ratio == 1 ){
			$new_width = $source_image_width;
			$new_height = $source_image_height;
		}
		LOG_MSG("INFO","image_resize() : new_width=[$new_width],
											new_height=[$new_height]");

		/******************************************************************/
		/*     STEP4:Find the crop coordinates                            */
		/******************************************************************/
		$centreX = round($source_image_width / 2);
		$centreY = round($source_image_height / 2);
		$x = max(0, $centreX - round($new_width / 2) );
		$y = max(0, $centreY - round($new_height / 2));
		LOG_MSG("INFO","image_resize() : coordinates x=[$x],
														y=[$y]");
	} else {
		$x=0;
		$y=0;
		$new_width = $source_image_width;
		$new_height = $source_image_height;
	}

	/******************************************************************/
	/*     STEP5:Crop/Resize the image                                */
	/******************************************************************/
	// Create a black image of specified crop width and size into which the area to be cropped is interpolated
	if ( !$resized_gd_image = imagecreatetruecolor ($resize_width, $resize_height) ) {
		LOG_MSG("INFO","image_resize() : Function 'imagecreatetruecolor' failed to create image identifier representing black image of the size $resize_widthX$resize_height");
		return false;
	}

	// Copy the area to be cropped onto the black image
	if ( !imagecopyresampled( $resized_gd_image, 
							 $source_gd_image, 
							 0,
							 0,
							 $x, // Center the image horizontally
							 $y, // Center the image vertically
							 $resize_width,
							 $resize_height,
							 $new_width, 
							 $new_height ) ) {
		LOG_MSG("INFO","image_resize() : Function 'imagecopyresampled' failed to create the resized/cropped image");
		return false;
	}
	// Save the image file/Create a jpeg image
	if ( !imagejpeg( $resized_gd_image, $source_image_path, 100) ) {
		LOG_MSG("INFO","image_resize() : Function 'imagejpeg' failed to create the JPEG image file");
		return false;
	}

	}

	/******************************************************************/
	/*      STEP6: Image Padding (Only for non streched images)       */
	/******************************************************************/
	if ( !$is_streched ) {

		// Take maximum image width and height for zoom in case of original image
		if ( $is_org_image ) {
			if ( $source_image_width > $source_image_height ) {
				$output_w=$output_h=$source_image_width;
			} else {
				$output_w=$output_h=$source_image_height;
			}

			// calc new image dimensions
			$new_w = $source_image_width;
			$new_h = $source_image_height;
		} else {
			$output_w=$org_resize_width;
			$output_h=$org_resize_height;

			if ( $is_img_resize ) { 
				$source_gd_image=$resized_gd_image; 
				// calc new image dimensions
				$new_w = $resize_width;
				$new_h = $resize_height;
			} else {
				$new_w = $source_image_width;
				$new_h = $source_image_height;
			}
		}

		// determine offset coords so that new image is centered
		$offest_x = ($output_w - $new_w) / 2;
		$offest_y = ($output_h - $new_h) / 2;

		// create new image and fill with background colour
		$new_img = imagecreatetruecolor($output_w, $output_h);
		$bgcolor = imagecolorallocate($new_img, 255, 255, 255); // red
		imagefill($new_img, 0, 0, $bgcolor); // fill background colour

		// copy and resize original image into center of new image
		imagecopyresampled($new_img, $source_gd_image, $offest_x, $offest_y, 0, 0, $new_w, $new_h, $new_w, $new_h);
		LOG_MSG('INFO',"image_resize(): ############ PADDING IMAGE ############ 
												is_img_resize=[$is_img_resize],
												New Image=[$new_img],
												Source image = [$source_gd_image],
												Offset X = [$offest_x],
												Offset Y = [$offest_y],
												New Width = [$new_w],
												New Height= [$new_h],
												Output Width = [$output_w],
												Output Height = [$output_h]");
		//save it
		imagejpeg($new_img, $source_image_path, 80);
	}

	// Destroy the resource image file
	if ( !imagedestroy($source_gd_image) ) {
		LOG_MSG("INFO","image_resize() : Function 'imagedestroy' failed to destroy the resource image file");
		return false;
	}
	LOG_MSG("INFO","image_resize() :END");
	return true;
}



/**********************************************************************/
/*                          IMPORT CSV                                */
/**********************************************************************/
function encode_csv_field($string) {
	if(strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
		$string = preg_replace('/"/', '\'', $string);
		$string = '"' . preg_replace("/\r\n/", "", $string) . '"';
	}
	return $string;
}

function clean_string($str) {
	return ucwords(strtolower(preg_replace('/[ ]+/',' ',trim($str))));
}

function clean_csv_string($str) {
        return preg_replace('/[ ]+/',' ',trim($str));
}



?>
