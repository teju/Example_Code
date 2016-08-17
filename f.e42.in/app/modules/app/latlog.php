<?php

// do_student_login()
function go_send_data() {

	global $ERROR_MESSAGE;

	LOG_MSG('INFO',"go_send_data(): START");
	LOG_MSG('INFO',"go_send_data(): POST_ARR ".print_r($_POST,true));

	// Initialize json
	$json['message']='';
	$json['status']='ERROR';
	LOG_MSG('INFO',"go_send_data(): START GET=".print_r($_GET,true));

	$json['status']='OK';
	$json['message']="Check Out";
	echo json_encode($json);

	LOG_ARR('INFO','JSON response',$json);
	LOG_MSG('INFO',"go_send_data(): END ");
	exit;

}

?>