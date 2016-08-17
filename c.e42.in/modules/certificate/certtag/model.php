<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_certtag_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_certtag_list(): START GET=".print_r($_GET,true));


	// Do we have a search string?
	// Get all the args from $_GET
	$certificate_id=get_arg($_GET,"certificate_id");
	LOG_MSG('DEBUG',"go_certtag_list(): Got args");

	// Validate parameters as normal strings 
	if ( !validate("Certificate ID",$certificate_id,0,45,"varchar") ){
		LOG_MSG('ERROR',"do_certificate_list(): Validate args failed!");
		return;
	}
	LOG_MSG('DEBUG',"go_certtag_list(): Validated args");


	LOG_MSG('INFO',"go_certificate_list(): END");
}


/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_certtag_save_json($mode="ADD") {

	global $GO,$ROW,$_GET,$ERROR_MESSAGE;

	$json_response['status']='ERROR';
	$json_response['message']='';

	if(!has_user_permission(__FUNCTION__,$mode)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_certtag_save_json(): Validate args failed!");
		exit;
	} 

	LOG_MSG('INFO',"do_certtag_save_json(): START (mode=$mode) POST=".print_r($_POST,true));

	// Get all the args from $_POST
	$certificate_id=get_arg($_POST,"certificate_id");
	$tag_name=get_arg($_POST,"tag_name");
	$tag_value=get_arg($_POST,"tag_value");
	LOG_MSG('DEBUG',"do_certtag_save_json(): Got args");

	// Validate parameters
	if (
		!validate("Certificate ID",$certificate_id,1,11,"int") ||
		!validate("Tag Name",$tag_name,1,200,"varchar") ||
		!validate("Tag Value",$tag_value,1,200,"varchar")	){
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			LOG_MSG('ERROR',"do_certtag_save_json(): Validate args failed!");
			exit;
	} 
	LOG_MSG('DEBUG',"do_certtag_save_json(): Validated args");

	db_transaction_start();

	// Che whether the tag already present in the tTag table
	$tag_row=db_tag_select('',$tag_name,$tag_value);
	if ( $tag_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the tag details tag_name=[$tag_name], tag_value=[$tag_value]");
		exit;
	}

	// Get Tag ID
	$tag_row=db_tag_select('',$tag_name,$tag_value,1);
	if ( $tag_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the tag details tag_name=[$tag_name], tag_value=[$tag_value]");
		exit;
	}

	// If not insert it
	if ( $tag_row[0]['NROWS'] == 0 ) {
		$json_response['message']="Invalid Tag name <b>$tag_name</b>";
		echo json_encode($json_response);
		LOG_MSG('INFO',"Tag name does not contain in the standard tag list tag_name=[$tag_name]");
		exit;
	}

	// Get Tag ID
	$tag_row=db_tag_select('',$tag_name,$tag_value);
	if ( $tag_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the tag details tag_name=[$tag_name], tag_value=[$tag_value]");
		exit;
	}

	// If not insert it
	if ( $tag_row[0]['NROWS'] == 0 ) {
		$resp=db_tag_insert($tag_name,$tag_value,0);
		if ( $resp['STATUS'] != 'OK' ) {
			$json_response['message']='There was an error adding the Data';
			echo json_encode($json_response);
			LOG_MSG('INFO',"There was an error adding into the tTag tag_name=[$tag_name], tag_value=[$tag_value]");
			exit;
		}
		$tag_id=$resp['INSERT_ID'];
	} else {
		$tag_id=$tag_row[0]['tag_id'];
	}

	##################################################
	#                 DB INSERT                      #
	##################################################
	$ROW=db_certtag_insert(
						$certificate_id,
						$tag_id);
	if ( $ROW['STATUS'] != "OK" ) {
		switch ($ROW["SQL_ERROR_CODE"]) {
			case 1062: // unique key
				add_msg("ERROR","The $tag_name - $tag_value is already in use. Please enter a different Tag<br/>");
				break;
			default:
				add_msg("ERROR","There was an error adding the Tag.");
				break;
		}
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_certificate_save(): Add args failed!");
		exit;
	}
	db_transaction_end();

	$json_response['status']='OK';
	$json_response['message']='Tag added successfully';
	$json_response['certificate_id']=$certificate_id;
	$json_response['tag_id']=$tag_id;
	$json_response['tag_name']=$tag_name;
	$json_response['tag_value']=$tag_value;
	echo json_encode($json_response);
	LOG_MSG('INFO',"do_certificate_save(): END");
	exit;
}



/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_certtag_delete_json() {


	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_certtag_delete_json(): START POST=".print_r($_POST,true));

	$certificate_id=get_arg($_POST,"certificate_id");
	$tag_id=get_arg($_POST,"tag_id");

	// Validate certificate_id
	if ( !validate("Certificate Id",$certificate_id,1,11,"int") || 
		 !validate("Tag Id",$tag_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	db_transaction_start();
	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_certtag_delete($certificate_id,$tag_id);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the tag';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_certtag_delete_json(): Delete row failed");
		exit;
	}

	// Check whether the tag present in the tCertTag table
	$certtag_row=db_certtag_select('',$tag_id);
	if ( $certtag_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error deleting the Tag';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the certtag details tag_id=[$tag_id]");
		exit;
	}

	// Delete the tag from tTag if not present
	if ( $certtag_row[0]['NROWS'] == 0 ) {
		$resp=db_tag_delete($tag_id,0);
		if ( $resp['STATUS'] != 'OK' ) {
			$json_response['message']='There was an error deleting the Tag';
			echo json_encode($json_response);
			LOG_MSG('INFO',"There was an error deleting the tag tag_id=[$tag_id]");
			exit;
		}
	}
	db_transaction_end();

	$json_response['status']='OK';
	$json_response['tag_id']=$tag_id;
	$json_response['certificate_id']=$certificate_id;
	//Send json response to response handler 
	echo json_encode($json_response);

	LOG_MSG('INFO',"do_product_delete_json(): Certificate Tag <strong>$tag_id</strong> has been removed.");
	LOG_MSG('INFO',"do_certtag_delete_json(): END");

	exit;
}

?>
