<?php

/**********************************************************************/
/*                       SELECT MULTIPLE records                      */
/**********************************************************************/
function go_tag_list() {

	if(!has_user_permission(__FUNCTION__)) return; 

	global $ROW, $TEMPLATE;
	LOG_MSG('INFO',"go_tag_list(): START GET=".print_r($_GET,true));

	$ROW=db_tag_select("","","",1);
	if ( $ROW[0]['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error loading the Tags. Please try again later. <br/>");
		return;
	}

	// No rows found
	if ( !get_arg($ROW[0],'IS_SEARCH') ) {
		//add_msg("NOTICE","No Standard Tag found! <br />Enter details and click on <strong>Add</strong> to create a one.<br />"); 
	}

	if ( isset( $TEMPLATE ) ) { $template=$TEMPLATE; } else { $template=TEMPLATE_DIR."list.html"; } 
	include($template); 

	LOG_MSG('INFO',"go_tag_list(): END");
}



/**********************************************************************/
/*                         ADD/UPDATE LISTING                         */
/**********************************************************************/
function do_tag_save_json($mode="ADD") {

	global $GO,$ROW,$_GET,$ERROR_MESSAGE;

	$json_response['status']='ERROR';
	$json_response['message']='';

	if(!has_user_permission(__FUNCTION__,$mode)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_tag_save_json(): Validate args failed!");
		exit;
	} 

	LOG_MSG('INFO',"do_tag_save_json(): START (mode=$mode) POST=".print_r($_POST,true));

	// Get all the args from $_POST
	$tag_name=get_arg($_POST,"tag_name");
	$tag_value=get_arg($_POST,"tag_value");
	$is_tag_sync=get_arg($_POST,"is_tag_sync");
	$type=get_arg($_POST,"type");
	$is_active=get_arg($_POST,"is_active");
	LOG_MSG('DEBUG',"do_tag_save_json(): Got args");

	// Validate parameters
	if (
		!validate("Tag Name",$tag_name,1,200,"varchar") ||
		!validate("Tag Value",$tag_value,1,200,"varchar") ||
		!validate("Is Tag Sync",$is_tag_sync,1,1,"tinyint") ||
		!validate("Type",$type,1,10,"varchar") ||
		!validate("Is Active",$is_active,1,1,"tinyint") ){
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			LOG_MSG('ERROR',"do_tag_save_json(): Validate args failed!");
			exit;
	} 
	LOG_MSG('DEBUG',"do_tag_save_json(): Validated args");

	db_transaction_start();
	// Standard Tag - Insert
	$resp=db_tag_insert($tag_name,
						$tag_value,
						1,		// Standard Tag
						$is_tag_sync,
						$type,
						$is_active);
	if ( $resp['STATUS'] != 'OK' ) {
		switch ($resp["SQL_ERROR_CODE"]) {
			case 1062: // unique key
				$json_response['message']='The tag is already added. Please try adding a different Tag';
				echo json_encode($json_response);
			break;
			default:
				$json_response['message']='There was an error adding the Data';
				echo json_encode($json_response);
			break;
		}
		LOG_MSG('INFO',"do_tag_save_json(): There was an error adding into the tTag tag_name=[$tag_name], tag_value=[$tag_value]");
		exit;
	}

	$tag_id=$resp["INSERT_ID"];

	// Certificate Tag - Insert
	$resp=db_tag_insert($tag_name,
						$tag_value,
						0,		// Standard Tag
						$is_tag_sync,
						$type,
						$is_active);
	if ( $resp['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"do_tag_save_json(): There was an error adding into the tTag tag_name=[$tag_name], tag_value=[$tag_value]");
		exit;
	}

	$tag_id=$resp["INSERT_ID"];

	// Get all the Certificate Ids to insesrt the new tag for all Certificates
	$certificate_row=db_get_list("ARRAY","certificate_id","tCertificate","org_id=".$_SESSION['org_id']);
	if ( $certificate_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"do_tag_save_json(): There was an error adding while fetching the certificate_ids");
		exit;
	}

	for ( $i=0;$i<$certificate_row[0]["NROWS"];$i++ ) {
		$certtag_resp=db_certtag_insert($certificate_row[$i]["certificate_id"],$tag_id);
		if ( $resp['STATUS'] != 'OK' ) {
			$json_response['message']='There was an error adding the Data';
			echo json_encode($json_response);
			LOG_MSG('INFO',"do_tag_save_json(): There was an error adding into the tTag tag_name=[$tag_name], tag_value=[$tag_value]");
			exit;
		}
	}
	db_transaction_end();

	$json_response['status']='OK';
	$json_response['message']='Tag added successfully';
	$json_response['tag_id']=$tag_id;
	$json_response['tag_name']=$tag_name;
	$json_response['tag_value']=$tag_value;
	$json_response['is_tag_sync']=$is_tag_sync;
	$json_response['type']=$type;
	$json_response['is_active']=$is_active;
	echo json_encode($json_response);
	LOG_MSG('INFO',"do_tag_save_json(): END");
	exit;
}


function do_tag_update_json($mode="UPDATE") {

	global $GO,$ROW,$_GET,$ERROR_MESSAGE;

	$json_response['status']='ERROR';
	$json_response['message']='';

	if(!has_user_permission(__FUNCTION__,$mode)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_tag_update_json(): Validate args failed!");
		exit;
	} 

	LOG_MSG('INFO',"do_tag_update_json(): START (mode=$mode) POST=".print_r($_POST,true));

	// Get all the args from $_POST
	$tag_id=get_arg($_POST,"tag_id");
	$is_active=get_arg($_POST,"is_active");
	$is_tag_sync=get_arg($_POST,"is_tag_sync");
	$type=get_arg($_POST,"type");
	LOG_MSG('DEBUG',"do_tag_update_json(): Got args");

	// Validate parameters
	if (
		!validate("Tag ID",$tag_id,1,11,"int") ||
		!validate("Is Active",$is_active,0,1,"tinyint") || 
		!validate("Is Tag Sync",$is_tag_sync,0,1,"tinyint") || 
		!validate("Is Tag Sync",$type,0,10,"varchar") ){
			$json_response['message']=$ERROR_MESSAGE;
			echo json_encode($json_response);
			LOG_MSG('ERROR',"do_tag_update_json(): Validate args failed!");
			exit;
	} 
	LOG_MSG('DEBUG',"do_tag_update_json(): Validated args");

	// Get Tag name
	$tag_row=db_tag_select($tag_id);
	if ( $tag_row[0]['STATUS'] != 'OK' || $tag_row[0]['NROWS'] != 1 ) {
		$json_response['message']='There was an error updating the tag';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the tag details tag_id=[$tag_id]");
		exit;
	}

	// Insert
	$resp=db_tag_update( $tag_row[0]['tag_name'],
						 $is_active,
						 $is_tag_sync,
						 $type );
	if ( $resp['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"do_tag_update_json(): There was an error adding into the tTag tag_name=[".$tag_row[0]['tag_name']."]");
		exit;
	}

	$tag_id=$resp["INSERT_ID"];

	$json_response['status']='OK';
	$json_response['message']='Tag successfully updated';
	$json_response['is_active']=$is_active;
	$json_response['is_tag_sync']=$is_tag_sync;

	echo json_encode($json_response);
	LOG_MSG('INFO',"do_tag_update_json(): END");
	exit;
}

/**********************************************************************/
/*                           DELETE LISTING                           */
/**********************************************************************/
function do_tag_delete_json() {


	global $ROW,$ERROR_MESSAGE;

	$json_response=array();
	$json_response['status']='ERROR';

	// CHECK USER ACCESSIBILITY
	if(!has_user_permission(__FUNCTION__)) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	LOG_MSG('INFO',"do_tag_delete_json(): START POST=".print_r($_POST,true));

	$tag_id=get_arg($_POST,"tag_id");

	// Validate certificate_id
	if ( !validate("Tag Id",$tag_id,1,11,"int") ) {
		$json_response['message']=$ERROR_MESSAGE;
		echo json_encode($json_response);
		exit;
	}

	// Get Tag name
	$tag_row=db_tag_select($tag_id);
	if ( $tag_row[0]['STATUS'] != 'OK' ) {
		$json_response['message']='There was an error adding the Data';
		echo json_encode($json_response);
		LOG_MSG('INFO',"There was an error fetching the tag details tag_name=[$tag_name], tag_value=[$tag_value]");
		exit;
	}

	// Delete all the tags based on tag name when deleting standard tag 
	##################################################
	#                 DB DELETE                      #
	##################################################
	$ROW=db_tag_delete("","",$tag_row[0]["tag_name"]);
	if ( $ROW['STATUS'] != "OK" ) {
		$json_response['message']='There was an error removing the tag';
		echo json_encode($json_response);
		LOG_MSG('ERROR',"do_tag_delete_json(): Delete row failed");
		exit;
	}

	$json_response['status']='OK';
	$json_response['tag_id']=$tag_id;

	//Send json response to response handler 
	echo json_encode($json_response);

	LOG_MSG('INFO',"do_tag_delete_json(): Tag has been removed.");

	LOG_MSG('INFO',"do_tag_delete_json(): END");

	exit;
}

?>
