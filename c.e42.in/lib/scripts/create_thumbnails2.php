<?php
	ini_set('display_errors',0);
	//error_reporting(E_ERROR | E_PARSE);
	define("IGNORE_SWIFT","true");
	include("../../lib/utils.php");
	include("../../tConf.php");
	include("../../lib/db.php");

	define('CLI_MODE',true);

	echo("\n************create_thumbnails() STARTED *************\n\n");

	db_connect();

	/****************************************************************************/
	/*                       STEP 1:Get all the shops                           */
	/****************************************************************************/
	$shop_row=array();
	$shop_row= execSQL("SELECT shop_id,domain,theme from tShop where domain='sevaaa.com' order by shop_id desc",array(),false);
	//$shop_row= execSQL("SELECT shop_id,domain,theme from tShop where domain = 'l1-start.shopnix.org'",array(),false);
	if ( $shop_row[0]['STATUS'] != "OK" || $shop_row[0]['NROWS'] == 0 ) {
		echo("\nError getting the shop details\n\n");
		return false;
	}


	/****************************************************************************/
	/*                STEP 2:Apply changes to each shop                         */
	/****************************************************************************/
	$resize_counter=1;
	for( $i=0; $i<$shop_row[0]['NROWS']; $i++ ) {
		$shop_id=$shop_row[$i]['shop_id'];
		$domain=$shop_row[$i]['domain'];
		$shop_theme=$shop_row[$i]['theme'];
		$image_count=0;
		echo "\n".date('Y-m-d H:i:s')." - Processing for shop ".strtoupper($domain)."... \n\n";
		switch($shop_theme) {
			case "layout1":
				$small_image_width=138;
				$small_image_height=138;
				$medium_image_width=3*$small_image_width;
				$medium_image_height=3*$small_image_height;
				break;
			case "layout2":
				$small_image_width=218;
				$small_image_height=327;
				$medium_image_width=1.4*$small_image_width;
				$medium_image_height=1.4*$small_image_height;
				break;
			case "azkaban":	// Sapna
				$small_image_width=122;
				$small_image_height=187;
				$medium_image_width=round(1.45*$small_image_width);	// 177
				$medium_image_height=round(1.45*$small_image_height);	// 271
				break;
		}

		// Initialize directories
		$product_image_dir="../../../media/$domain/images/products/";
		$small_image_dir="../../../media/$domain/images/products/small/";
		$medium_image_dir="../../../media/$domain/images/products/medium/";


		/****************************************************************************/
		/*                   Check if the media directory exists                    */
		/****************************************************************************/
		if ( !file_exists ( "../../../media/$domain" ) ) {
			echo("\nThe specified media directory does not exist for the shop shop_id = [$shop_id] and domain = [$domain]\n\n");
			continue;
		}

		$product_resp=array();
		$product_resp[0]=array();
		$product_resp=execSQL("SELECT image from tProduct WHERE shop_id=$shop_id ",array(),false);		// select all the image from the Shop
		if ( $product_resp[0]['STATUS'] != "OK" ||  $product_resp[0]['NROWS'] == 0 ) {
			echo("\nError getting the images from the shop shop_id = [$shop_id] \n\n");
			continue;
		}

		/****************************************************************************/
		/*                   Create small and medium Directories                    */
		/****************************************************************************/
		if ( !file_exists ( $medium_image_dir ) ) mkdir($medium_image_dir);
		if ( !file_exists ( $small_image_dir ) ) mkdir($small_image_dir);

		if (!chown($medium_image_dir,'www-data') || !chgrp($medium_image_dir,'www-data')) echo "    Error changing owner of [$medium_image_dir] to www-data";
		if (!chown($small_image_dir,'www-data') || !chgrp($small_image_dir,'www-data') ) echo "    Error changing owner of [$small_image_dir] to www-data";

		/****************************************************************************/
		/*                 Create small and medium product images                   */
		/****************************************************************************/
		for( $j=0; $j<$product_resp[0]['NROWS']; $j++) {

			$image_count++;
			$is_medium_copied=true;
			$is_medium_resized=true;
			$is_small_copied=true;
			$is_small_resized=true;
			$image_name=$product_resp[$j]['image'];
			echo "  Processing image [$image_name] ==> [$medium_image_dir$image_name] \n";
			//if ( $image_name == "no_image.jpg" ) continue;


			// FOR THE MEDIUM SIZE IMAGES
			// Copy image
			if ( 1 || !file_exists ( $medium_image_dir.$image_name ) ) {
			
			  //echo "        File does not exist [$medium_image_dir.$image_name]\n";
			  if ( !copy($product_image_dir.$image_name,$medium_image_dir.$image_name) ) {
				$is_medium_copied=false;
				echo "Error copying the image [$image_name] into $medium_image_dir \n";
			  }
			  //echo "        copied image to [$medium_image_dir.$image_name]\n";
			  // Resize image
			  if ( $is_medium_copied && !image_resize($medium_image_dir.$image_name,$medium_image_width,$medium_image_height) ) {
				$is_medium_resized=false;
				echo "Error resizing the image = [$image_name] in $medium_image_dir \n";
			  } {
			  	echo "        resized image [$medium_image_dir.$image_name]\n";
				$resize_counter++;
			  }
			  //if ( $is_medium_copied && $is_medium_resized ) echo "        [$image_count] Medium Image $image_name copied and resized successful\n";
			} else {
			  //echo "        File Exists [$medium_image_dir.$image_name]\n";
			}

			// FOR THE SMALL SIZE IMAGES
			// Copy image 
			echo "  Processing image [$image_name] ==> [$small_image_dir$image_name] \n";
			if ( 1 || !file_exists ( $small_image_dir.$image_name ) ) {
			  if ( !copy($product_image_dir.$image_name,$small_image_dir.$image_name) ) {
				$is_small_copied=false;
				echo "Error copying the image [$image_name] into $small_image_dir \n";
			}
			// Resize image
			if ( $is_small_copied && !image_resize($small_image_dir.$image_name,$small_image_width,$small_image_height) ) {
				$is_small_resized=false;
				echo "Error resizing the image = [$image_name] in $small_image_dir \n";
			} {
			  	echo "        resized image [$small_image_dir.$image_name]\n";
				$resize_counter++;
			}

			//if ( $is_small_copied && $is_small_resized ) echo "        [$image_count] Small Image $image_name copied and resized successful\n";
			}

			if ( $resize_counter%100 == 0 ) { echo "---sleeping [$resize_counter]---\n";sleep(20);}

		} // end products loop
	} // end shops loop
	db_close();
	echo("\n************ create_thumbnails() END *************\n\n");

?>
