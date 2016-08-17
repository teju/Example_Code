<?php

/**********************************************************************/
/*                          APP DETAILS                               */
/**********************************************************************/
define('SITE_NAME','Certified42');
define('SITE_LOGO','Certified42');

//define('CLI_MODE',true);
/**********************************************************************/
/*                          SERVER SPECIFIC                           */
/**********************************************************************/
$DOMAIN=preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
define('SERVER',$DOMAIN);
if ( $DOMAIN == 'localhost' || $DOMAIN == '192.168.1.38' ) {
	$BRANCH="nfc";
	define('MEDIA_DIR','../media/');
	define('BASEURL',"http://$DOMAIN/REPO/services/c.e42.in/");
	define('UPLOADS_DIR',"uploads/");
	define('IMG_DIR',"uploads/images/");
	define('IMG_PATH',BASEURL."uploads/images/");
	define('LOCAL_CDN_BASE',"http://$DOMAIN/NFC/nfc/");
	define('LOG_FILE', "E:/".SITE_NAME.".".date("Y-m-d").".log");
} else {
	define('MEDIA_DIR','../media/');
	define('BASEURL',"http://$DOMAIN/");
	define('UPLOADS_DIR',"uploads/");
	define('IMG_DIR',"uploads/images/");
        define('IMG_PATH',BASEURL."uploads/images/");
	if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == "122.172.129.92" ) {
		define('LOG_FILE', "/var/log/cloudnix/".SITE_NAME.".DEVELOPMENT.".date("Y-m-d").".log");
	} else {
		define('LOG_FILE', "/var/log/cloudnix/".SITE_NAME.".".date("Y-m-d").".log");
	}
}

/**********************************************************************/
/*                          DB STUFF                                  */
/**********************************************************************/
define('DB_SERVER',"localhost");
define('DB_NAME',"certified42");
define('DB_USER',"certified42");
define('DB_PASSWORD',"c421233");

/**********************************************************************/
/*                       EMAIL STUFF                                  */
/**********************************************************************/
# LOCAL - Send from local SMTP server
# REMOTE - Send from Cloudnix SMTP server (usually used for AWS)
define('EMAILER_HOST','MANDRILL');	
define('EMAIL_FROM','Shopnix Team <support@shopnix.in>');
define('EMAIL_ADMIN','Shopnix Admin <admin@shopnix.in>');
define('EMAIL_CC','');
define('EMAIL_BCC','');
define('SMTP_USERNAME','webmaster@cloudnix.com');
define('SMTP_PASSWORD','e3a6d939-caa3-4cdb-a60b-600132a3ef32');

/**********************************************************************/
/*                       DATA DIRECTORIES                                */
/**********************************************************************/
define('TEMPLATE_DIR',"");
define('NAV_TEMPLATE_DIR',"static/html/");
define('HASH_KEY','NFC');

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


/**********************************************************************/
/*                       SEARCH                                       */
/**********************************************************************/
define('SEARCH_MODE','DB');


/**********************************************************************/
/*                  UNIVERSITY CONSTANTS                              */
/**********************************************************************/
define('UNIVERSITY_NAME','Visveswaraya Technological University');
define('UNIVERSITY_LOGO',BASEURL.'uploads/images/logo.jpg');

define('SUPPORT_EMAIL_STR','support@shopnix.in');
define('SUPPORT_EMAIL','support@shopnix.in');
define('CONTACT_SUPPORT_MSG','Please contact our customer care on <a href="mailto:'.SUPPORT_EMAIL.'">'.SUPPORT_EMAIL.'</a>');
define('PASSWORD_SALT','NFC');

?>
