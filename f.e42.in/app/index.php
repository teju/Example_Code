<?php 
	session_start(); 
	$_script_st_tm=round(microtime(true), 2);

	/******************************************************************/
	/* STANDARD INCLUDES                                              */
	/******************************************************************/
	include("tConf.php");
	include("lib/db.php");
	include("lib/utils.php");
	include("modules/utils/utils.php");

	// Site offline
	// if (ONLINE == "false") { include("lib/down.html"); exit; }

	/******************************************************************/
	/* GLOBALS                                                        */
	/******************************************************************/
	/******************************************************************/
	/* GLOBALS                                                        */
	/******************************************************************/
	// Navigation
	global $MOD;
	global $ENTITY;
	global $AJAX_MODE;

	// Error handling
	global $ERROR_MESSAGE;
	global $SUCCESS_MESSAGE;

	// DB data
	global $ROW;

	// Initialise globals
	$ROW=array();
	$ROW[0]=array();
	$ERROR_MESSAGE="";
	$SUCCESS_MESSAGE="";

	// Initialise globals
	$ROW=array();
	$GO=get_arg($_GET,"go");
	$DO=get_arg($_POST,"do");
	LOG_MSG("INFO","$DO");
	$MOD=get_arg($_GET,"mod");
	if ( $GO === '' ) { $GO='search'; }

	db_connect();

	// DO
	switch($DO) {
		case 'user_register':
			do_user_register();
		return;
		case 'user_login':
			do_user_login();
		return;
	}

	// GO
	switch($GO) {
		case 'activate':
			do_user_activate();
		return;
		case 'logout':
			do_user_logout();
		break;
	}

	// initialization of travel_id constant
	init_travel($DOMAIN); 

	// Set output mode (a->AJAX, j->JSON)
	$output_mode=get_arg($_GET,'mode');
	if ($output_mode !== '') define('AJAX_MODE','j');

	LOG_MSG('INFO',"\n======= START: GO=[$GO] DO=[$DO] GET=[".implode('|',$_GET)."] POST=[".implode('|',$_POST)."] =========");
	if ( defined('AJAX_MODE') ) LOG_MSG('INFO',"^^^^^^^^^^^^^^^^^^^^^^^ AJAX START ^^^^^^^^^^^^^^^^^^^^^^^");

	include("modules/".$MOD."/controller.php"); 

if ( defined('AJAX_MODE') ) {LOG_MSG('INFO',"^^^^^^^^^^^^^^^^^^^^^^^ AJAX END ^^^^^^^^^^^^^^^^^^^^^^^");exit;}

$_script_tm=round(microtime(true)-$_script_st_tm, 2);
db_close();
LOG_MSG('INFO',"===== END: GO=[$GO] DO=[$DO] GET=[".implode('|',$_GET)."] POST=[".implode('|',$_POST)."] TIME=[${_script_tm}s] =====\n"); 
?>
