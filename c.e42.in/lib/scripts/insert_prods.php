<?php

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../modules/utils/db.php");
	include("../../lib/users/model.php");
	include("../../lib/users/db.php");
	include("../../modules/utils/utils.php");
	include("../../modules/admin/product/db.php");
	include("../../modules/admin/product/tag/db.php");
	include("../../modules/admin/product/prodtag/db.php");
	include("../../modules/admin/inventory/db.php");

	define('CLI_MODE',true);
	db_connect();
	do_product_save_cli();
	db_close();



function do_product_save_cli() {

	global $GO,$ROW,$AJAX_MODE,$ERROR_MESSAGE;

	LOG_MSG('INFO',"do_product_save(): START");
	define('SHOP_ID',170);


	$MAX_COUNT=10000000;
	//$MAX_COUNT=1000;
	$PRODUCT_COUNTER=0;
	$BRAND_COUNTER=1;
	$TYPE_COUNTER=1;
	$CATEGORY_COUNTER=1;
	$SUBCATEGORY_COUNTER=1;
	$_total_script_tm=0;

	for ($i=0;$i<$MAX_COUNT;$i++) {
		$_script_st_tm=round(microtime(true), 2);

		$PRODUCT_COUNTER++;
		if (!($PRODUCT_COUNTER % 10000)) {
			$SUBCATEGORY_COUNTER++;
			echo "[elapsed time: $_total_script_tm secs]. sleeping...\n";
			sleep(5);
		}
		if (!($SUBCATEGORY_COUNTER % 100000)) $CATEGORY_COUNTER++;
		if (!($PRODUCT_COUNTER % 1000000)) $BRAND_COUNTER++;
		if (!($CATEGORY_COUNTER % 1000000)) $TYPE_COUNTER++;


		$name="Product $PRODUCT_COUNTER";
		$description="$name description";
		$price1=rand(10,500)*2.5;
		$price2=rand(10,500)*2.5;
		if ($price1 > $price2 ) {$mrp=$price1; $net_price=$price2;}
		else  {$mrp=$price2; $net_price=$price1;}
		$discount_rate=money_format('%!.2i',($mrp-$net_price)*100/$mrp);
		$tax_rate=0;
		$shipping_charges=0;
		$preorder_available_by='';
		$is_preorder=0;
		$is_wholesale=0;
		$is_active=1;
		$stock_level=rand(0,10);
		$brand="Brand $BRAND_COUNTER";
		$type="Type $TYPE_COUNTER";
		$category="Category $CATEGORY_COUNTER";
		$subcategory="SubCategory $SUBCATEGORY_COUNTER";

		##################################################
		#                 DB INSERT                      #
		##################################################
		db_transaction_start();
		$ROW=db_product_insert(
							$name,
							$description,	//SNT0082: Product description added
							'no_image.jpg',
							$mrp,
							$discount_rate,
							$tax_rate,
							$shipping_charges,
							$net_price,
							$preorder_available_by,
							$is_preorder,
							$is_wholesale,
							$is_active,
							100
							);
		if ( $ROW['STATUS'] != "OK" ) {
			switch ($ROW["SQL_ERROR_CODE"]) {
				case 1062: // unique key
					echo "SKIPPED unique product: [".money_format("%!.0i",$PRODUCT_COUNTER)."] $type|$category|$subcategory|$name|$description|$mrp|$net_price|$discount_rate|$tax_rate|$shipping_charges|$is_wholesale|$is_active\n";
					add_msg("INFO","The product <strong>$name</strong> is already in use. Please use a different name");
					break;
				default:
					echo "ERROR: [".money_format("%!.0i",$PRODUCT_COUNTER)."] $type|$category|$subcategory|$name|$description|$mrp|$net_price|$discount_rate|$tax_rate|$shipping_charges|$is_wholesale|$is_active\n";
					add_msg("INFO","There was an error adding the product <strong>$name</strong>");
					exit;
			}
			LOG_MSG('INFO',"do_product_save(): Add row failed");
			continue;
		}
		$product_id=$ROW['INSERT_ID'];


		$_SESSION['admin']=array();
		$_SESSION['admin']['supplier_id']=116;
		//Insert into Inventory only if single store
		$inventory_row=db_inventory_insert(
											$product_id,
											$discount_rate,
											$tax_rate,
											$shipping_charges,
											$net_price,
											$stock_level);
		if ( $inventory_row['STATUS'] !== 'OK' ) {
			add_msg("INFO","There was an error adding the product <strong>$name</strong>");
			LOG_MSG('INFO',"do_product_save(): Error inserting the product details into the inventory");
			echo "ERROR inserting inventory: [".money_format("%!.0i",$PRODUCT_COUNTER)."] $product_id|$type|$category|$subcategory|$name|$description|$mrp|$net_price|$discount_rate|$tax_rate|$shipping_charges|$is_wholesale|$is_active\n";
			continue;
		}

		//Add new brand, type, category and subcategory to the prodtag table
		add_prodtag($product_id,'Brand',$brand);
		add_prodtag($product_id,'Type',$type);
		add_prodtag($product_id,'Category',$category);
		add_prodtag($product_id,'SubCategory',$subcategory);
		db_transaction_end();

		// Success msgs
		add_msg("SUCCESS","New product <strong>$name</strong> added");


		LOG_MSG('INFO',"do_product_save(): END");

		$_script_tm=round(microtime(true)-$_script_st_tm, 2);
		$_total_script_tm+=$_script_tm;
		echo "INSERTED [".money_format("%!.0i",$PRODUCT_COUNTER)."] $product_id|$type|$category|$subcategory|$name|$description|$mrp|$net_price|$discount_rate|$tax_rate|$shipping_charges|$is_wholesale|$is_active:   ${_script_tm} secs\n";
	}

}

?>
