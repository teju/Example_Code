<?php 

	define('IGNORE_SWIFT','TRUE');
	$_SERVER['SERVER_NAME']='shopnix.in';

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../lib/users/db.php");
	include("../../lib/users/model.php");
	include("../../modules/utils/db.php");
	include("../../modules/utils/utils.php");

	define('CLI_MODE',false);
	db_connect();
	echo "Starting...\n";
	include("../../lib/intercom/intercom.php");
	intercom_send_data_single();
	echo "--------Ended-------\n";
	db_close();


