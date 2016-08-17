<?php 

	$_start_tm4=round(microtime(true), 2);
	$_SERVER['SERVER_NAME']='localhost';
	//error_reporting(0);
	//define('CLI_MODE',true);
	define('IGNORE_SWIFT',true);
	define('SHOP_ID',25);
	define('SHOP_DOMAIN','coorgshoppe.com');

	// Write sku to file
	$fh=fopen('product_sku.txt','a');

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../lib/solr/config.php");
	include("../../modules/utils/db.php");
	include("../../modules/admin/product/db.php");

	global $ERROR_MESSAGE;

	echo "############### MULTIPLE REMOVING SCRIPT ################### \n";
	echo "STARTING....... \n";

	// Multiple ISBN
	$isbn="SELECT 
				p.sku, 
				p.product_id, 
				p.image 
			FROM 
				tProduct p
			WHERE 
				shop_id=".SHOP_ID;

	db_connect();

	// Get products
	//echo "Running query isbn \n";
	$product_row=execSQL( $isbn,
						array(),
						false);
	if ( $product_row[0]['STATUS'] != 'OK' ) {
		echo "Error retrieving products";
		exit;
	}

	echo 'Total products fetched=['.$product_row[0]['NROWS']."] \n";
	if ( $product_row[0]['NROWS'] < 1 ) {
		echo "No products found";
		exit;
	}

	// Iterate for each product
	for ( $i=0;$i<$product_row[0]['NROWS'];$i++ ) {
		$product_id=$product_row[$i]['product_id'];
		$sku=$product_row[$i]['sku'];
		$image=$product_row[$i]['image'];

		fwrite($fh,"$sku,$product_id\n");
		echo "SKU=$sku PRODUCT_ID=$product_id\n";

		// Rename Image file to 
		## After SDF is uploaded, bulk upload the images from MEDIA_DIR/sapnaonline.com/images/
		if ( file_exists('../../'.MEDIA_DIR.SHOP_DOMAIN."/images/products/$image") && $image != 'no_image.jpg' ) {
			copy('../../'.MEDIA_DIR.SHOP_DOMAIN."/images/products/$image","/mnt/DATA/coorgshopee/$sku.jpg");	// Rename to sku.jpg
			//unlink('../../'.MEDIA_DIR."sapnaonline.com/images/products/$image");		// Remove from Dir
			echo "    Copied image $image and to $sku".'jpg'." \n";
		}
		$product_id_arr[$i]=$product_id;

	}

	db_close();

	echo "[".$product_row[0]['NROWS']."] products=[".round(microtime(true)-$_start_tm4, 2)."seconds] \n";
	echo "############### DONE ################### \n";
	exit;

?>
