<?php

function do_check_rooms() {
	
	global $ROW, $TEMPLATE;
	
	LOG_MSG('INFO',"do_check_rooms(): START GET=".print_r($_GET,true));

	// Get all the args from $_GET
	$room_name=get_arg($_GET,"room_name");
	$room_id=get_arg($_GET,"room_id");
	
	$ROW=db_room_select(
			$room_id,
			$room_name);
			
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the wallet. Please try again later. <br/>");
		return;
	}
	
	LOG_MSG('INFO',"do_check_rooms() : END");
}

?>
