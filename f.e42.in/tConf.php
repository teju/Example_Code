<?php

/**********************************************************************/
/*                          APP DETAILS                               */
/**********************************************************************/
define('SITE_NAME','Fueltracker');
define('THEME','blitzer');


/**********************************************************************/
/*                          SERVER SPECIFIC                           */
/**********************************************************************/
$DOMAIN=preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
if ( $DOMAIN == 'localhost' || $DOMAIN == '192.168.1.35' ) {
	if ( $DOMAIN == '192.168.1.35' ) $DOMAIN='localhost';
	define('BASEURL',"http://$DOMAIN/REPO/services/f.e42.in/");
	//$DOMAIN='f.e42.in';
	define('IMG_DIR',"media/$DOMAIN/images/");
	define('IMG_PATH',BASEURL."media/$DOMAIN/images/");
	define('UPLOAD_PATH',BASEURL."media/$DOMAIN/uploads/");
	define('UPLOAD_DIR',"media/$DOMAIN/uploads/");
	define('CUST_CDN',"http://localhost/cn/shopnix/media");
	#define('LOG_FILE', "/var/log/cloudnix/".SITE_NAME.".".date("Y-m-d").".log");
	define('LOG_FILE', "d:/".SITE_NAME.".".date("Y-m-d").".log");
} else {
	define('BASEURL',"http://$DOMAIN/");
	define('IMG_DIR',"media/$DOMAIN/images/");
	define('IMG_PATH',BASEURL."media/$DOMAIN/images/");
	define('UPLOAD_PATH',BASEURL."media/$DOMAIN/uploads/");
	define('UPLOAD_DIR',"media/$DOMAIN/uploads/");
	define('CUST_CDN',"http://$DOMAIN/media.RELEASE_DATE");
	if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == "122.167.133.205" ) 
		define('LOG_FILE', "/var/log/cloudnix/".$DOMAIN.".DEVELOPMENT.".date("Y-m-d").".log");
	else 
		define('LOG_FILE', "/var/log/cloudnix/".$DOMAIN.".".date("Y-m-d").".log");
}

/**********************************************************************/
/*                          db STUFF                                  */
/**********************************************************************/
define('DB_SERVER',"localhost");
define('DB_NAME',"fueltracker");
define('DB_USER',"fueltracker");
define('DB_PASSWORD',"fueltracker1233");

/**********************************************************************/
/*                       EMAIL STUFF                                  */
/**********************************************************************/
# LOCAL - Send from local SMTP server
# REMOTE - Send from Cloudnix SMTP server (usually used for AWS)
define('EMAIL_FROM',"admin@$DOMAIN");
define('EMAIL_ADMIN',"admin@$DOMAIN");
define('EMAIL_CC',$DOMAIN."_cc@cloudnix.com");
define('EMAIL_BCC',$DOMAIN."_bcc@cloudnix.com");
define('EMAILER_HOST','MANDRILL');
define('SMTP_USERNAME','webmaster@cloudnix.com');
define('SMTP_PASSWORD','e3a6d939-caa3-4cdb-a60b-600132a3ef32');


/**********************************************************************/
/*                       DATA DIRECTORIES                                */
/**********************************************************************/
define('TEMPLATE_DIR',"");
define('NAV_TEMPLATE_DIR',"static/html/");
define('HASH_KEY',"$DOMAIN");


/**********************************************************************/
/*                       LOCALE SETTINGS                              */
/**********************************************************************/
date_default_timezone_set('Asia/Calcutta');
setlocale(LC_MONETARY, 'en_IN');


/**********************************************************************/
/*                          IMAGE FILE CONFIG                         */
/**********************************************************************/
define('MIN_IMAGE_SIZE',5);
define('MAX_IMAGE_SIZE',3048576);
define('MAX_PRODUCT_FILE_SIZE',10485760);

$IMAGE_CAPTURE_RANGE = array ( 
							"always" => "Always",
							"frequently" => "Frequently(2)",
							"sometimes" => "Sometimes(4)",
							"rarely" => "Rarely(8)",
							"never" => "Never"
						);

?>

