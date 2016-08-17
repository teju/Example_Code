<?php

	include('domains.php');
	ini_set('display_errors',0);

	$_SERVER['SERVER_NAME']='SERVER';
	$_SERVER['REMOTE_ADDR']=true;
	//error_reporting(E_ERROR | E_PARSE);
	define("IGNORE_SWIFT","true");
	include("../../lib/utils.php");
	include("../../tConf.php");
	include("../../lib/db.php");

	define('xCLI_MODE',true);

	// Make IN ACTIVE when cross Expiry Date
	echo("\n************ Checking for the Expiry Date *************\n");

	db_transaction_start();

	/******************************* DRIVER **********************************/
	// Inactive Driver if crosses END date or License expires
	$driver_resp=execSQL(" 	UPDATE 
								tDriver
							SET
								is_active = 0
							WHERE 
								license_exp_dt < NOW() OR 
								end_dt < NOW() "
							,array(),
							true);
	if ( $driver_resp['STATUS'] != "OK" ) {
		echo "Error while updating the driver table\n");
		exit;
	}

	/******************************* SUPERVISOR **********************************/
	// Inactive Supervisor if crosses END date or License expires
	$supervisor_resp=execSQL(" 	UPDATE 
								tSupervisor
							SET
								is_active = 0
							WHERE 
								end_dt < NOW() "
							,array(),
							true);
	if ( $driver_resp['STATUS'] != "OK" ) {
		echo "Error while updating the supervisor table\n");
		exit;
	}

	/******************************* VEHICLE **********************************/
	// Inactive Supervisor if crosses END date or License expires
	$vehicle_resp=execSQL(" 	UPDATE 
								tVehicle
							SET
								is_active = 0
							WHERE 
								rc_exp_dt < NOW() OR 
								insurance_exp_dt < NOW() OR 
								road_tax_exp_dt < NOW() OR 
								end_dt < NOW() "
							,array(),
							true);
	if ( $driver_resp['STATUS'] != "OK" ) {
		echo "Error while updating the supervisor table\n");
		exit;
	}

	db_transaction_end();

	echo("\n************ BACKUP() END *************\n");

?>

