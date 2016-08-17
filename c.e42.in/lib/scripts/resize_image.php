<?php
	ini_set('display_errors',0);

	$_SERVER['SERVER_NAME']='SERVER';
	$_SERVER['REMOTE_ADDR']=true;
	//error_reporting(E_ERROR | E_PARSE);
	define("IGNORE_SWIFT","true");
	include("../../lib/utils.php");
	include("../../tConf.php");
	include("../../lib/db.php");

	define('xCLI_MODE',true);

	echo("\n************ RESIZE IMAGE() STARTED *************\n\n");

	db_connect();

	/****************************************************************************/
	/*                       STEP 1:Get all the shops                           */
	/****************************************************************************/
	$shop_row=array();
	$shop_row= execSQL("SELECT shop_id,domain,theme from tShop where domain='resize-image.shopnix.org'",array(),false);
	if ( $shop_row[0]['STATUS'] != "OK" || $shop_row[0]['NROWS'] == 0 ) {
		echo("\nError getting the shop details\n\n");
		return false;
	}


	/****************************************************************************/
	/*                STEP 2:Apply changes to each shop                         */
	/****************************************************************************/
	$resize_counter=1;
	for( $i=0; $i<$shop_row[0]['NROWS']; $i++ ) {

		db_transaction_start();

		$shop_id=$shop_row[$i]['shop_id'];
		$domain=$shop_row[$i]['domain'];
		$shop_theme=$shop_row[$i]['theme'];
		$image_count=0;

		echo "\n Processing for shop $domain... \n\n";
		$small_image_width=db_get_list('LIST','value','tShopSetting',"name='theme_prod_width' AND shop_id=$shop_id");
		$small_image_height=db_get_list('LIST','value','tShopSetting',"name='theme_prod_height' AND shop_id=$shop_id");
		$is_continue=false;

		echo "small_image_width=[$small_image_width], small_image_height=[$small_image_height] \n";
		switch($shop_theme) {
			case "layout1":
				$medium_image_width=3*$small_image_width;
				$medium_image_height=3*$small_image_height;
				break;
			case "layout2":
				$medium_image_width=1.4*$small_image_width;
				$medium_image_height=1.4*$small_image_height;
				break;
			default:	// Sapna
				echo "Unknown theme [$shop_theme] for shop [$domain]";
				$is_continue=true;
				break;
		}

		if ( $is_continue ) continue;

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

		// Loop through the dir
		$product_resp=execSQL("	SELECT 
									p.product_id, 
									p.name, 
									p.image,
									pd.image1,
									pd.image2,
									pd.image3,
									pd.image4,
									pd.image5
								FROM 
									tProduct p
									LEFT OUTER JOIN tProductDetail AS pd ON(p.product_id=pd.product_id)
								WHERE 
									p.shop_id=$shop_id
									",
								array(),
								false);		// select all the image from the Shop
		if ( $product_resp[0]['STATUS'] != "OK" ||  $product_resp[0]['NROWS'] == 0 ) {
			echo("\nError getting the images from the shop shop_id = [$shop_id] \n\n");
			continue;
		}
		echo print_r($product_resp,true);
		for( $j=0; $j<$product_resp[0]['NROWS']; $j++) {

			/****************************************************************************/
			/*                   Resize the product image                               */
			/****************************************************************************/
			$product_id=$product_resp[$j]['product_id'];
			$name=$product_resp[$j]['name'];
			$old_image_name=$product_resp[$j]['image'];

			if ( $old_image_name == "no_image.jpg" ) continue;
			echo "Processing image [$old_image_name]  \n";

			// If file does not exists, update the image to no_image
			if ( !file_exists ( $product_image_dir.$old_image_name ) ) {
				$resp=db_product_update_image($product_id,'no_image.jpg');
				if ( $resp['STATUS'] != 'OK' ) {
					echo "There was an error updating the image name to no_image.jpg \n";
					continue;
				}
				echo "File does not exists $old_image_name  \n";
				continue;
			}

			// Generate new file name
			$path = $product_image_dir.$old_image_name;
			$image_extension = pathinfo($path, PATHINFO_EXTENSION);
			$image_name=substr( make_clean_url($name).'-'.$product_id, 0, 130 ).".".date('YmdHis').".".$image_extension; 	// New image name
			echo "New image name [$image_name]  \n";

			// Resize image
			if ( !upload_image_resize(
                                $product_id,
                                $old_image_name,
                                $image_name,
                                $product_image_dir,
                                $medium_image_dir,
                                $small_image_dir,
                                $medium_image_width,
                                $medium_image_height,
                                $small_image_width,
                                $small_image_height,
                                $image_count,
                                $resize_counter,
				$domain) ) {
				echo "Error resizeing the image \n";
				continue;
			}

			// Update the image name
			$resp=db_product_update_image($product_id,$image_name);
			if ( $resp['STATUS'] != 'OK' ) {
				echo "There was an error updating the image name to no_image.jpg \n";
				continue;
			}
			echo "Updated new image [$image_name] \n";

			// Unlink the old image in the dir
			unlink($product_image_dir.$old_image_name);
			unlink($medium_image_dir.$old_image_name);
			unlink($small_image_dir.$old_image_name);
			echo "Deleted the image $old_image_name successful \n";

			/****************************************************************************/
			/*                   Resize the product detail image                        */
			/****************************************************************************/
			for ( $image_index=1;$image_index<=5;$image_index++ ) {
				$old_image_name=$product_resp[$j]["image$image_index"];

				if ( $old_image_name == "no_image.jpg" || $old_image_name == '' ) continue;
				echo "Processing image [$old_image_name]  \n";

				// If file does not exists, update the image to no_image
				if ( !file_exists ( $product_image_dir.$old_image_name ) ) {
					$resp=db_productdetail_insert_update($product_id,"image$image_index",'no_image.jpg',$shop_id);
					if ( $resp['STATUS'] != 'OK' ) {
						echo "There was an error updating the image name to no_image.jpg \n";
						continue;
					}
					continue;
				}


				// Generate new file name
				$path = $product_image_dir.$old_image_name;
				$image_extension = pathinfo($path, PATHINFO_EXTENSION);
				$image_name=substr( make_clean_url($name).'-'.$product_id, 0, 129 ).'.'."$image_index".'.'.date('YmdHis').".".$image_extension; 	// New image name
				echo "New image name [$image_name]  \n";

				// Resize image
				                        // Resize image
        	                if ( !upload_image_resize(
	                                $product_id,
                                	$old_image_name,
                        	        $image_name,
                	                $product_image_dir,
        	                        $medium_image_dir,
	                                $small_image_dir,
                                	$medium_image_width,
                        	        $medium_image_height,
                	                $small_image_width,
        	                        $small_image_height,
	                                $image_count,
                                	$resize_counter,
                        	        $domain) ) {
                	                echo "Error resizeing the image \n";
        	                        continue;
	                        }


				// Update the image name
				$resp=db_productdetail_insert_update($product_id,"image$image_index",$image_name,$shop_id);
				if ( $resp['STATUS'] != 'OK' ) {
					echo "There was an error updating the image name to $image_name \n";
					continue;
				}
				echo "Updated new image [$image_name] \n";

				// Unlink the old image in the dir
				unlink($product_image_dir.$old_image_name);
				unlink($medium_image_dir.$old_image_name);
				unlink($small_image_dir.$old_image_name);
				echo "Deleted the image $old_image_name successful \n";
			}
		} // end products loop
		
		db_transaction_end();
	} // end shops loop

	db_close();
	echo("\n************ RESIZE IMAGE END *************\n\n");

function upload_image_resize(	
				$product_id,
				$old_image_name,
				$image_name,
				$product_image_dir,
				$medium_image_dir,
				$small_image_dir,
				$medium_image_width,
				$medium_image_height,
				$small_image_width,
				$small_image_height,
				$image_count,
				$resize_counter,
				$shop_domain) {

			$image_count++;
			$is_org_copied=true;
			$is_org_resized=true;
			$is_medium_copied=true;
			$is_medium_resized=true;
			$is_small_copied=true;
			$is_small_resized=true;

			// FOR THE ORGINAL SIZE IMAGES
			// Copy image
			if ( !copy($product_image_dir.$old_image_name,$product_image_dir.$image_name) ) {
				$is_org_copied=false;
				echo "ORIGINAL: Error copying the image [$old_image_name] into $product_image_dir for shop [$shop_domain]\n";
				return false;
			}
			// Resize image
			if ( $is_org_copied && !_image_resize($product_image_dir.$image_name,'','') ) {
				$is_org_resized=false;
				echo "ORIGINAL: Error resizing the image = [$image_name] in $medium_image_dir for shop [$shop_domain]\n";
				return false;
			} else {
				$resize_counter++;
			}
			if ( $is_org_copied && $is_org_resized ) echo "ORIGINAL: [$image_count] Image $image_name copied and resized successful\n";

			// FOR THE MEDIUM SIZE IMAGES
			// Copy image
			if ( !copy($product_image_dir.$old_image_name,$medium_image_dir.$image_name) ) {
				$is_medium_copied=false;
				echo "MEDIUM: Error copying the image [$old_image_name] into $medium_image_dir for shop [$shop_domain]\n";
				return false;			
			}

			$home_banner_width=str_replace('px','',shopsetting_get('home_banner_width'));
			$home_banner_height=str_replace('px','',shopsetting_get('home_banner_height'));
			// Resize image
			if ( !_image_resize(SHOP_THEME_DIR.$home_banner,$home_banner_width,$home_banner_height) ) {
				add_msg("ERROR","There was an error resizing the home banner images.");
				LOG_MSG('ERROR','do_shop_theme_save(): Error while resizing the banner image');
				$home_banner=false;
			}
				$resize_counter++;
			}
			if ( $is_medium_copied && $is_medium_resized ) echo "MEDIUM: [$image_count] Medium Image $image_name copied and resized successful\n";

			// FOR THE SMALL SIZE IMAGES
			// Copy image 
			if ( !copy($product_image_dir.$old_image_name,$small_image_dir.$image_name) ) {
				$is_small_copied=false;
				echo "SMALL: Error copying the image [$old_image_name] into $small_image_dir for shop [$shop_domain] \n";
                                return false;
			}
			// Resize image
			if ( $is_small_copied && !_image_resize($small_image_dir.$image_name,$small_image_width,$small_image_height) ) {
				$is_small_resized=false;
				echo "SMALL: Error resizing the image = [$image_name] in $small_image_dir \n";
                                return false;
			} else {
				$resize_counter++;
			}

			if ( $is_small_copied && $is_small_resized ) echo "SMALL: [$image_count] Small Image $image_name copied and resized successful\n";

			if ( $resize_counter%20 == 0 ) { echo "---sleeping---\n";sleep(10); }
			return true;

}

// UPDATE
function db_product_update_image(
								$product_id,
								$image) {

	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_product_update_image(): START {
							product_id=[$product_id]
							image=[$image]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","image",$image);

	$param_arr=_db_prepare_param($param_arr,"i","product_id",$product_id,true);

	$resp=execSQL("UPDATE  
						tProduct
					SET ".$param_arr['update_fields']
					." WHERE 
						product_id = ? "
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_product_update_image(): END");
	return $resp;
}


// INSERT/UPDATE productdetails
function db_productdetail_insert_update( $product_id,
                                                                                 $image_column,
                                                                                 $image,
										 $shop_id) {
        $param_arr=_init_db_params();
        LOG_MSG('INFO',"db_productdetail_insert_update(): START { product_id=[$product_id],image_column=[$image_column],image=[$image] \n}");

        // Add params to params_arr
        $param_arr=_db_prepare_param($param_arr,'i','product_id',$product_id);
        $param_arr=_db_prepare_param($param_arr,'i','shop_id',$shop_id);
        $param_arr=_db_prepare_param($param_arr,'s',$image_column,$image);

        // On Duplicate Key Update
        $param_arr=_db_prepare_param($param_arr,'s',$image_column,$image,true);

        $resp=execSQL("INSERT INTO
                                                tProductDetail
                                                        (".$param_arr['fields'].")
                                                VALUES
                                                        (".$param_arr['placeholders'].")
                                                ON DUPLICATE KEY UPDATE
                                                        $image_column=?"
                                        ,$param_arr['params'],
                                        true);
        LOG_MSG('INFO',"db_productdetail_insert_update(): END");
        return $resp;
}

?>

