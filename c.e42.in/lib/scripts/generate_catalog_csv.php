<?php

	//error_reporting(E_ERROR & ~E_DEPRECATED);
	define('IGNORE_SWIFT',true);

	$_SERVER['SERVER_NAME']='shopnix.in';

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/swiftmailer/swift_required.php");
	include("../../lib/utils.php");
	include("../../modules/utils/db.php");
	include("../../../lib/settings.php");

	define('CLI_MODE',true);

	db_connect();

	// if ( !shopsetting_init() ) exit;	// Initialize shopsetting

	db_transaction_start();

        $csv_header='DOMAIN, TYPE, CATEGORY, SUBCATEGORY'."\r\n";
        $filename='/mnt/catalog.csv';
        $file=fopen($filename,'w');
        if(!fwrite($file,$csv_header)) {
                echo "Error while writing header to file [$filename] \n";
                return;
        }

        $resp=execSQL("SELECT
                        s.domain,t.type, c.category, sc.subcategory
                FROM tType t
                LEFT OUTER JOIN tCategory c ON ( t.type_id = c.type_id )
                LEFT OUTER JOIN tSubCategory sc ON ( c.category_id = sc.category_id )
		LEFT OUTER JOIN tShop s ON ( s.shop_id=t.shop_id )  WHERE s.domain LIKE '%shopnix.org' ORDER BY s.domain",array(),false);

        if ( $resp[0]['STATUS'] != 'OK' ) {
                echo "ERROR fetching catalogs \n";
        	exit;
	}

	$domain='';
	for ( $i=0;$i<$resp[0]['NROWS'];$i++ ) {

		if ( $domain != $resp[$i]['domain'] ) echo "\n  Writing for shop [".$resp[$i]['domain']."]";
		$csv_body=		$resp[$i]['domain'].','.
					get_arg($resp[$i],'type').','.
					get_arg($resp[$i],'category').','.
					get_arg($resp[$i],'subcategory')."\r\n";
        	if(!fwrite($file,$csv_body)) {
                	echo "Error while writing body to file [$filename] \n";
	                return;
        	}
		$domain=$resp[$i]['domain'];
	}

	echo 'DONE! TOTAL=['.$resp[0]['NROWS'].']';

	db_close();

?>
	

