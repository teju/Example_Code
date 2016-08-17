<?php 
include("../../tConf.php");
include("../../lib/utils.php");

function emailer() {
	LOG_MSG('INFO',"do_confirm_order(): START");
	$htmldata = file_get_contents("php://input");
	echo "***************do_confirm_order() start *************************";
	echo "----GET----\n".print_r($_GET,true);
	//echo "----RAW----\n [$htmldata]";
	echo "***************do_confirm_order() end   *************************";
	
	$to=get_arg($_GET,'to');
	$from=get_arg($_GET,'from');
	$subject=get_arg($_GET,'sub');
	$message=$htmldata;


	send_email($to,$subject,$message,$from);
	LOG_MSG('INFO',"do_confirm_order(): END");
}

emailer();

?>
