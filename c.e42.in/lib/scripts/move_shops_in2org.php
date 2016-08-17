<?php 

	define('MEDIA_DIR','../../../media/');
	define('IGNORE_SWIFT','TRUE');
	$_SERVER['SERVER_NAME']='shopnix.in';

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../lib/users/db.php");
	include("../../lib/users/model.php");
	include("../../modules/utils/db.php");
	include("../../modules/utils/utils.php");
	include("../../modules/admin/shop/db.php");
	include("../../modules/admin/shop/model.php");
        include("../../lib/intercom/intercom.php");

	define('SCRIPT_MODE',true);

	db_connect();
	echo "Starting...\n";	
	$TO_MOVE=db_get_list("ARRAY","shop_id,domain","tShop","domain like '%.shopnix.in'  ");
	echo print_r($TO_MOVE,true);

	for ($i=0;$i<$TO_MOVE[0]['NROWS'];$i++) {
		//echo "[$i]==[".$TO_MOVE[$i]['shop_id']."]===\n";continue;
		$old_domain=strtolower($TO_MOVE[$i]['domain']);
		$shop_id=$TO_MOVE[$i]['shop_id'];
		$domain=str_replace(".shopnix.in",".shopnix.org",$old_domain);
		echo "[$shop_id]: Moving [$old_domain] => [$domain]: ";

	  	if (false === strpos($old_domain,".shopnix.in") ){
			echo "Not a shopnix.in subdomain. Skipping...\n";
	    		continue;
		}

		// Update database
		$ROW=db_shop_update_domain($old_domain,$domain);
		if ( $ROW['STATUS'] != "OK" ) {
				switch ($ROW["SQL_ERROR_CODE"]) {
						case 1062: // unique key
								$json_response['message']="The Shop domain [$domain] is already in use. Cannot move [$old_domain] to [$domain]!";
						default:
								$json_response['message']="There was an error updating the shop $domain. SQL Code = [".$ROW["SQL_ERROR_CODE"]."]";
				}
				echo $json_response['message']."\n";
				exit;
		}

		// move folder
		echo "Moving folders: ";
		rename(MEDIA_DIR.$old_domain,MEDIA_DIR.$domain); // Rename shop media directory

		// Send intercom data
		echo "Updating ICOM [$shop_id]: ";
	        intercom_send_data($shop_id);
		echo "\n";
		//break;
	}
	echo "--------Ended-------\n";
	db_close();


