<?php

// Rules to call this function
// 1. The coloumn name in the db should be same as the coloumn name in the front end table
// 2. Number of fields retreiving from the db should be same as the number of coloumns in the front end
function generate($report_mode,$row) {

	global $ORDER_STATUS, $PAYMENT_TYPES, $PAYMENT_STATUS, $ERROR_MESSAGE;

	LOG_MSG('INFO',"generate(): START report_mode=[$report_mode], rows=[".$row[0]['NROWS']."]");
	switch ($report_mode) {
		case 'HTML':
				include($row['filename']);
			break;
		case 'CSV':
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=".SITE_NAME.'_'.$row['report_name'].'_'.date("Y-m-d-h-i-s").'.csv');
				header("Pragma: no-cache");
				header("Expires: 0");
				$nrows=$row[0]['NROWS'];
				unset($row[0]['NROWS']);
				unset($row[0]['STATUS']);
				if ( isset($row[0]['IS_SEARCH']) ) unset($row[0]['IS_SEARCH']);
				if ( $row['report_name'] == 'attendance_report' ){
					echo "Name,";
					for ( $i=0;$i<$row['date_row'][0]['NROWS'];$i++ ) 
					{   if( $i > 0){
							echo $row['date_row'][$i]['date'].', ,';
						}
						else echo ",";
					}
					echo "\n";
					for ( $j=0;$j<$row['date_row'][0]['NROWS'];$j++ ) {  
						if ( $j > 0 ) echo "Morning,Evening,";
						else echo ",";
					}
					echo "\n";
					for ( $i=0;$i<$row['user_row'][0]['NROWS'];$i++ ) {
						echo $row['user_row'][$i]['name'];
							for ( $j=0;$j<$row['date_row'][0]['NROWS'];$j++ ) {
								if($j > 0){
									echo $row['attendance_arr'][$row['user_row'][$i]['student_id']][$row['date_row'][$j]['date']]['MORNING'].','; 
									echo $row['attendance_arr'][$row['user_row'][$i]['student_id']][$row['date_row'][$j]['date']]['EVENING'].',';
								}
							 else echo ",";
							}
							echo "\n";
							
					}
													
					
				} else {
					for ( $i=0;$i<$nrows;$i++ ) { 
						if ( isset($row[$i]['payment_type']) ) $row[$i]['payment_type']=$PAYMENT_TYPES[$row[$i]['payment_type']]; 

						if ( $row['report_name'] == 'search_report' ) {
							unset($row[$i]['order_by']);
							unset($row[$i]['fuel_image']);
							unset($row[$i]['odometer_image']);
						}
						if ( $row['report_name'] == 'student_report' ) {
							unset($row[$i]['check_in_image']);
							unset($row[$i]['check_out_image']);
						}

						if ( $row['report_name'] == 'Stock_Report' ) {
													$row[$i]['product']=encode_csv_field($row[$i]['product']);
											}
						// This loop will print headers
						if (  $i == 0 ) {
							foreach ( $row[0] AS $key => $value ) {
								if ($row['report_name'] == 'Bluedart_Report' ) echo "$key,";
								else echo make_clean_str($key).',';
							}
							echo "\r\n";
						}
						// This will print the values
						//$row[$i]=encode_csv_field($row[$i]);
						echo implode(',',$row[$i])."\r\n";
					}
				}
				break;
		case 'PDF':
				require_once("lib/dompdf/dompdf_config.inc.php");
				ob_start();
				include($row['filename']);
				$html=ob_get_contents();		// Set mail content
				ob_get_clean();
				$dompdf = new DOMPDF();
				$dompdf->load_html($html);
				if ( strlen($html) > 80000 ) {
					echo "Report is too large to generate PDF. Please download CSV instead";
					LOG_MSG('INFO',"Maximum memory reached. String Length=[".strlen($html)."bytes]");
					exit;			
				}

				// For blue dart report,the table width is too large to fit into an A4-portrait
				if ( $row['report_name'] == 'Bluedart_Report' ) { 
					$dompdf->set_paper('a3', 'landscape');
				}
				$dompdf->render();
				$dompdf->stream($row['report_name'].'.pdf');
				break;
		case 'EMAIL':
				// Get args from POST
				$from=get_arg($_GET,"from");
				$to=get_arg($_GET,"to");
				$subject=get_arg($_GET,"subject");
				$mail_mode=get_arg($_GET,'mail_mode');
				$message=get_arg($_GET,'message');

				// Validate parameters
				if (
					!validate("To address",$to,5,100,"EMAIL") ||
					!validate("From address",$from,5,100,"EMAIL") ||
					!validate("Subject",$subject,1,100,"varchar") ||
					!validate("Message",$message,0,200,"varchar") ){
					LOG_MSG('ERROR',"generate(): VALIDATE ARGS FAILED! ".print_r($_POST,true));
					return false;
				}
				// Validate parameters
				if ( $mail_mode != 'attachment' && $mail_mode != 'inline' ) {
					add_msg('ERROR','Invalid Mail mode');
					LOG_MSG('ERROR',"generate(): VALIDATE ARGS FAILED! Invalid Mail mode".print_r($_POST,true));
					return false;
				}
				LOG_MSG('INFO',"generate(): Validated args");

				// Required for attachment
				$attachments_arr=array();
				if ( $mail_mode == 'attachment' ) {
					require_once("lib/dompdf/dompdf_config.inc.php");
					ob_start();
					include($row['filename']);
					$html=ob_get_contents();		// Set mail content
					ob_get_clean();
					$dompdf = new DOMPDF();
					$dompdf->load_html($html);
					LOG_MSG('INFO',"Maximum memory reached. String Length=[".strlen($html)."bytes]");
					// Due to the memory allocation error, send as html mail as a work around instead of attaching PDF
					if ( strlen($html) > 80000 ) {
						LOG_MSG('INFO',"Sending HTML mail instead of attachment as Maximum memoty reached. String Length=[".strlen($html)."bytes]");
						$message.="<br/> $html";		// Set mail content
						$plain_message=''; 				// the contents are already in $message
					} else {
					// For blue dart report,the table width is too large to fit into an A4-portrait
					if ( $row['report_name'] == 'Bluedart_Report' ) { 
						$dompdf->set_paper('a3', 'landscape');
					}
					$dompdf->render();
					$attachments_arr[0]['filename']=$row['report_name'].'.pdf';
					$attachments_arr[0]['data']=$dompdf->output();
					$plain_message=$html;			// this will contain the attachment contents
					}
				} else {

				//Store in buffer and load to mail message	
				ob_start();
				include($row['filename']);
					$message.='<br/>'.ob_get_contents();		// Set mail content
				ob_get_clean();
				$plain_message=''; 				// the contents are already in $message
				}
				send_email($to,$from,'','',$subject,$message,$attachments_arr,$plain_message); // Send Mail
				break;
	}
	LOG_MSG('INFO',"generate(): END");
	return true;
}


function init_travel($domain) {

        LOG_MSG('INFO',"init_travel(): START");

        $travel_row=db_get_list('ARRAY','travel_id,domain,name AS travel_name','tTravel',"domain='$domain'");
        if ( $travel_row[0]['STATUS'] != 'OK' ) { 
	       LOG_MSG('ERROR',"init_travel(): Error getting travel_id for domain=[$domain] ");
               add_msg('ERROR','There was an error loading. Please try later');
               return;
        }

        define('TRAVEL_ID',$travel_row[0]['travel_id']);
        define('DOMAIN',$travel_row[0]['domain']);
        define('TRAVEL_NAME',$travel_row[0]['travel_name']);

        LOG_MSG('INFO',"init_travel(): END");

}



function validate_imagefile( $image_file_path ) {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"validate_imagefile(): START image_file_path=[$image_file_path]");

	$response["STATUS"]="ERROR";
	$response["MESSAGE"]="";

	/**********************************************************************/
	/*  Validate the file contents                                        */
	/**********************************************************************/
	// Step 1: Check filesize
	$image_file_size=filesize($image_file_path);

	if ( $image_file_size > MAX_IMAGE_SIZE ) {														// Image size exceeds the maximum limit
		$response["MESSAGE"]="The Image Size exceeds the maximum upload size(".MAX_IMAGE_SIZE.")";
		return $response;
	} else if ( $image_file_size < MIN_IMAGE_SIZE ) {												// Image size exceeds the minimum limit
		$response["MESSAGE"]="The Image Size is lower than the minimum upload size(".MIN_IMAGE_SIZE.")";
		return $response;
	}

	// Step 2: Check if it is an image
	if ( !$image_info_array=getimagesize($image_file_path) ) {										// Get the image properties
		$response["MESSAGE"]="The file is not an image file";
	} else {
		if( !in_array( $image_info_array[2], array( IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG ) ,true ) ) {
			$response["MESSAGE"]="The image should be of the format jpg, jpeg ,png or gif";
			return $response;
		}
	}

	$response["STATUS"]="OK";
	$response["MESSAGE"]="The image is valid";
	LOG_MSG('INFO',"validate_imagefile(): END");
	return $response;
}


function generate_imagename( $image_file_path ) {

	global $ERROR_MESSAGE;
	LOG_MSG('INFO',"generate_imagename(): START image_file_path=[$image_file_path]");

	/**********************************************************************/
	/*  Validate and clean up the filename                                */
	/**********************************************************************/
	// Step 3: Get the base filename
	$ext = pathinfo($image_file_path, PATHINFO_EXTENSION);
	$uploaded_image_name = basename($image_file_path, ".".$ext);									// Extract image name

	// step 4. Check image extension type
	$image_extension=make_clean_url( pathinfo( $image_file_path, PATHINFO_EXTENSION ) );			// Extract image extension
	if ( $image_extension != 'jpg' &&
		 $image_extension != 'jpeg' &&
		 $image_extension != 'png' &&
		 $image_extension != 'gif' ) {
			return false;
	}

	// Step 5: Clean the filename 
	$new_image_name=substr( make_clean_url( $uploaded_image_name ), 0, 130 ).".".date('YmdHis').".".$image_extension; 	// New image name

	LOG_MSG('INFO',"generate_imagename(): END");
	return $new_image_name;
}

function app_sync( $table_name,$primary_id, $status, $travel_id ) {

	LOG_MSG('INFO',"app_sync(): START");

	$ROW=db_appsync_insert ( $table_name,
							$primary_id,
							$status,
							$travel_id ) ;
	if ( $ROW['STATUS'] !== 'OK' ) {
		LOG_MSG('ERROR',"app_sync(): Error inserting app_sync or no row found");
		add_msg("ERROR","There was an error adding to appsync");
		return false;
	}

	$supervisor_row=db_is_sync_supervisor_update(0);
	if ( $ROW['STATUS'] != "OK" ) {
		add_msg("ERROR","There was an error updating the supervisor .");
		return false;;
	}

	LOG_MSG('INFO',"app_sync(): END");
	return true;

}
?>
