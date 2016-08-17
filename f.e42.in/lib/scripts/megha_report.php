<?php  

	ini_set('display_errors',1);

	$_SERVER['SERVER_NAME']='SERVER';
	$_SERVER['REMOTE_ADDR']=true;
	error_reporting(E_ERROR | E_PARSE);
	define("IGNORE_SWIFT","true");
	$DOMAIN="script";

	require_once '../../lib/swiftmailer/swift_required.php';
	include("../../lib/utils.php");
	include("../../tConf.php");
	include("../../lib/db.php");

	db_connect();

	// Select vehicle 
	$vehicle_report_select=execSQL("SELECT
										reg_no,
										imei,
										client_name,
										odometer_reading,
										fuel_filled,
										DATE_FORMAT(created_dt,'%Y-%m-%d %h:%i %p')
									FROM
										tSearch
									WHERE
										travel_id=4 AND
										created_dt like '".date("Y-m-d")."%' AND
										fuel_filled <> 0",
										array(),
										false);
										

	if ( $vehicle_report_select[0]['STATUS'] !== 'OK' ) {
		echo "Error fetching vehicle ";
		echo print_arr($vehicle_report_select);
		exit;
	}

	$fp = fopen("megha_report.csv","w");
	$nrows=$vehicle_report_select[0]['NROWS'];
	unset($vehicle_report_select[0]['NROWS']);
	unset($vehicle_report_select[0]['STATUS']);

	$report = "REG NO, IMEI, CLIENT, ODOMETER READING, FUEL FILLED, DATE"."\r\n";
	for ( $i=0;$i<$nrows;$i++ ) { 
		//echo implode( ',' ,$vehicle_report_select[$i])."\r\n";
		$report=$report.implode( ',' ,$vehicle_report_select[$i])."\r\n";
	}
	$fp=fwrite($fp, $report);

	rewind($fp);
	$attachments_arr=array();
	$attachments_arr[0]['filename']='megha_report.csv';
	$attachments_arr[0]['data']=file_get_contents("megha_report.csv");
	echo "[".$attachments_arr[0]['data']."]";

	if( !send_email('tejaswini@cloudnix.com','pranoy@cloudnix.com','','','Daily Report','Hi, Please find attachment below for
				vehicle daily report',$attachments_arr,'',"csv") ) {
		echo "Email not sent";
	}
	fclose($filename);


?>
