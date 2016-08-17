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

	//define('CLI_MODE',true);

	db_connect();
	echo "Starting...\n";
	$TO_DELETE=array( "123.shopnix.in","smoshoops.shopnix.in","mcaggs.shopnix.in","meittechie.shopnix.in","ceyenar.shopnix.in","aksingha.shopnix.in","saisukrithkarsupplements.shopnix.in","sparklingbubs.shopnix.in","sumithranom.shopnix.in","mnshgautam.shopnix.in","subegupag.shopnix.in","qaechlorexpo.shopnix.in","rambnijustorch.shopnix.in","esemsubbau.shopnix.in","toprennsilkhe.shopnix.in");
	foreach ($TO_DELETE as $domain) {
		$shop_id=db_get_list("LIST","shop_id","tShop","domain='$domain'");
		echo "[$domain] shop_id=[$shop_id]\n";
		if ($shop_id != '') {
			$resp=db_shop_delete($shop_id);
			if ($resp['STATUS'] != 'OK' ) {
				echo "ERROR deleting shop [$domain] id [$shop_id]\n";
				print_r($resp);
				exit;
			}
		} else {echo "======Coould not get shop_id [$shop_id]\n";}
	}
	echo "--------Ended-------\n";
	db_close();


