<?php  

	ini_set('display_errors',0);

	$_SERVER['SERVER_NAME']='SERVER';
	$_SERVER['REMOTE_ADDR']=true;
	error_reporting(E_ERROR | E_PARSE);
	define("IGNORE_SWIFT","true");
	$DOMAIN="script";

	include("../../lib/utils.php");
	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../modules/admin/client/db.php");
	include("../../modules/admin/vehicle/db.php");

	define('TRAVEL_ID',6);

	db_connect();

	$filename='vinayaka.csv';
	$handle = fopen($filename, "r");
	if ( $handle === false ) {
		echo 'Error opening the file';
		exit;
	}

	db_transaction_start();

	$upload_count=0;
	while ( ($item = fgetcsv($handle, 65535, ',','"')) !== FALSE ) {
		// Get the vehicle details
		$reg_no=			clean_csv_string(get_arg($item,0));
		$vehicle_model=		clean_csv_string(get_arg($item,1));
		$client_name=		clean_csv_string(get_arg($item,3));
		$sticker_no=		clean_csv_string(get_arg($item,2));
		echo "[$sticker_no]"; 

		//upload the details into csv if sticker no is not empty
		if( $sticker_no == "" ) continue;

		// Check if the client is already available
		$client_row=db_client_select("",$client_name);
		if ( $client_row[0]['STATUS'] != 'OK' ) {
			echo "Error getting client details for $client_name";
			exit;
		}

		// Insert new Client
		if ( $client_row[0]['NROWS'] == 0 ) {
			$resp=db_client_insert($client_name,'no_image.jpg','','');
			if ( $resp['STATUS'] != 'OK' ) {
				echo "Error inserting client for $client_name";
				exit;
			}
			$client_id=$resp['INSERT_ID'];
		} else {
			$client_id=$client_row[0]['client_id'];
		}

		// Check if the vehicle is already available
                $vehicle_row=db_vehicle_select("",$reg_no);
                if ( $vehicle_row[0]['STATUS'] != 'OK' ) {
                        echo "Error getting client details for $client_name";
                        exit;
                }

		if ( $vehicle_row[0]['NROWS'] > 0 ) {
			echo "$reg_no already present. Continue... \n";
			continue;
			exit;
		}

		// Inert the vehicle details
		$resp=db_vehicle_insert(
								$reg_no,
								0,
								$vehicle_model,
								"PERMANENT",
								$sticker_no,
								"DUMMYRCNO-$reg_no",
								"2099-12-31",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								"",
								$client_id,
								"",
								1);
		if ( $resp['STATUS'] != 'OK' ) {
			echo "Error inserting vehicle for $reg_no";
			echo print_arr($resp);
			continue;
			exit;
		}
		$upload_count++;

		echo "$reg_no added successfully \n";

		if ( $upload_count%500 == 0 ) {
			echo "Sleeping for 5 secs \n";
			sleep(5);
		}
	}
        db_transaction_end();
?>
