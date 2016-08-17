<?php
	global $BATCH_SIZE, $IS_TEST_ONLY;
	$BATCH_SIZE=5000;
	$_SERVER['SERVER_NAME']='localhost';

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../modules/utils/db.php");
	include("../../lib/users/model.php");
	include("../../lib/users/db.php");
	include("../../modules/utils/utils.php");


	include("../../modules/admin/product/model.php");
	include("../../modules/admin/product/db.php");
	include("../../modules/admin/product/tag/db.php");
	include("../../modules/admin/product/prodtag/model.php");
	include("../../modules/admin/product/prodtag/db.php");
	include("../../modules/admin/inventory/db.php");
	include("../../modules/admin/type/db.php");
	include("../../modules/admin/category/db.php");
	include("../../modules/admin/subcategory/db.php");
	include("../../modules/admin/type/typetag/db.php");
	include("../../modules/admin/shop/shopcurrency/db.php");
	include("../../modules/admin/product/SDF/model.php");
	include("../../lib/solr/config.php");
	include("../../modules/admin/product/SDF/catmap.php"); 

	global $is_optimise;

	// PROCESS ARGUMENTS
	$shortopts="f:s:e:dot";
	$longopts=array("file","start_rec","end_rec","delete_all");
	$opts = getopt($shortopts,$longopts);
	//echo "=======[".print_r($opts,true)."]===========\n";
	$sdf_file=(get_arg($opts,'f')) ? get_arg($opts,'f') : '';
	$start_rec=(get_arg($opts,'s')) ? intval(get_arg($opts,'s')) : $start_rec=0;
	$end_rec=(get_arg($opts,'e')) ? intval(get_arg($opts,'e')): $end_rec=0;
	$is_delete_all=(isset($opts['d'])) ? true : false;
	$is_optimise=(isset($opts['o'])) ? true : false;
	$IS_TEST_ONLY=(isset($opts['t'])) ? true : false;
	echo "Starting File=[$sdf_file] Rec=[$start_rec] ending rec=[$end_rec] delete_all[$is_delete_all] is_optimise[$is_optimise] is_test_only=[$IS_TEST_ONLY]\n";

	// Check arguments
	if ($sdf_file == '' ) {echo "SDF file cannot be empty\n"; exit; }
	if ($start_rec > $end_rec) {echo "invalid inputs: startrec is greater than endrec\n"; exit; }



	define('CLI_MODE',true);
	define('SHOP_ID',210);
	db_connect();
	if ($is_delete_all) reset_all();
	//optimise_db();
	do_product_import_SDF_cli($sdf_file, $start_rec,$end_rec);
	db_close();


function optimise_db() {
	global $is_optimise;

	if (!$is_optimise) return;
	echo "=========OPTIMISIZG TABLES start=========\n";
	db_transaction_start();
	$tables=array("tInventory", "tProduct", "tTag", "tProdTag");
	foreach ($tables as $table) {
		echo "Optimising Table: $table\n";
		//$resp=execSQL("ANALYZE TABLE $table",array(),true);
		$resp=execSQL("ALTER TABLE $table ENGINE = InnoDB;",array(),true);
		if ($resp['STATUS'] != 'OK') {
			echo "ERROR: "; print_arr($resp);
		}
	}
	db_transaction_end();
	echo "=========OPTIMISIZG TABLES DONE=========\n";
}


function reset_all() {
	echo "************WARNING!! THIS WILL DELETE THE ENTIRE DATABASE***********\n Are you sure you want to proceed? ";
	$confirm=fread(STDIN, 1);
	if ($confirm != 'y') exit;

	$tables=array("tType", "tTag", "tOrder", "tProduct");
	foreach ($tables as $table) {
		echo "Deleting Table: $table\n";
		$resp=execSQL("DELETE FROM $table",array(),true);
		if ($resp['STATUS'] != 'OK') {
			echo "ERROR: "; print_arr($resp);
			exit;
		}
	}
}

function do_product_import_SDF_cli($sdf_file, $start_rec,$end_rec) {

	global $GO,$ROW,$UPLOAD_STATUS,$ERROR_MESSAGE,$CURRENCYS,$CATMAP,$STANDARD_TAGS, $BATCH_SIZE, $IS_TEST_ONLY;
	$_SESSION['admin']=array();

	$_search_st_tm=round(microtime(true), 2);


	$STANDARD_TAGS = array(
		'product_code' => 'Product Code',
		'author_1' => 'Author',
		'author_2' => 'Author',
		'author_3' => 'Author',
		'isbn' => 'ISBN',
		'binding' => 'Binding',
		'edition' => 'Edition',
		'published_year' => 'Published Year',
		'publisher' => 'Publisher',
		'language' => 'Language',
		'category_code' => 'Cat Short Code',
		'division' => 'Division'
	);


	LOG_MSG('INFO',"do_product_import_sdf(): START");

	$GO='upload_sdf';

	$supplier_id=db_get_list("LIST","supplier_id","tSupplier","shop_id=".SHOP_ID);
	LOG_MSG("INFO","supplier_id=$supplier_id");
	$_SESSION['admin']['supplier_id']=$supplier_id;
	
	// LOAD THE CURRENCY MAPPINGS
	$CURRENCYS=array();
	$resp=db_shopcurrency_select();
	if ( $resp[0]['STATUS'] != "OK" ) {
		add_msg('ERROR',"Error loading currency information"); 
		return;
	}
	for($i=0;$i<$resp[0]['NROWS'];$i++) {
		$code=$resp[$i]['code'];
		$CURRENCYS[$code]=$resp[$i]['conversion_rate'];
	}
	unset($resp);// don't need this anymore



	// Open the SDF
	$sdf_handle = fopen($sdf_file, "r");
	if (!$sdf_handle) {
		add_msg('ERROR','There was an error processing the SDF file. Please contact customer care.');
		LOG_ARR('INFO','FILES',$_FILES);
		return;
	}


	// Upload each product
	$row_num=0;
	$not_uploaded_count=0;
	$uploaded_count=0;
	$_batch_st_tm=round(microtime(true), 2);
	db_transaction_start();
	while (($buffer = fgets($sdf_handle, 4096)) !== false) {
		$row_num++;
		if ($row_num < $start_rec ) { continue; }
		if ($row_num > $end_rec ) { break; }
		LOG_MSG('INFO',"################################## PROCESSING ROW [$row_num] ####################################");
		echo "Processing $row_num: ";

		$item=array();
		$sdf_row_type=substr($buffer,0,1);
		switch ($sdf_row_type) {
			case '4':
					LOG_MSG('INFO',"RETAIL FORMAT - row type [$sdf_row_type]");
					$item['product_code']=trim(substr($buffer,1,10));
					break;
			case '3':
					LOG_MSG('INFO',"RETAIL FORMAT - row type [$sdf_row_type]");
					$item['product_code']=trim(substr($buffer,1,10));
					$item['stock_level']=ltrim(trim(substr($buffer,11,7)),'0');
					$item['ccy']=trim(substr($buffer,18,3));
					$item['amount']=floatval(trim(substr($buffer,21,10)));
					break;
			case '2':
					LOG_MSG('INFO',"RETAIL FORMAT - row type [$sdf_row_type]");
					$item['product_code']=trim(substr($buffer,1,10));
					$item['category_code']=trim(substr($buffer,53,3));
					break;
			case '1':
					LOG_MSG('INFO',"RETAIL FORMAT - row type [$sdf_row_type]");
					$wait_for_next_row=true;
					$item['product_code']=trim(substr($buffer,1,10));
					$item['isbn']=trim(substr($buffer,11,13));
					$item['name']=clean_string(substr($buffer,24,100));
					$item['author_1_code']=trim(substr($buffer,124,7));
					$item['author_1']=clean_string(substr($buffer,131,35));
					$item['author_2_code']=trim(substr($buffer,166,7));
					$item['author_2']=clean_string(substr($buffer,173,35));
					$item['author_3_code']=trim(substr($buffer,208,7));
					$item['author_3']=clean_string(substr($buffer,215,35));
					$item['publisher_code']=trim(substr($buffer,250,7));
					$item['publisher']=clean_string(substr($buffer,257,35));
					//$item['edition_code']=clean_string(substr($buffer,292,7));			// new
					$item['binding']=clean_string(substr($buffer,299,35));
					$item['published_year']=clean_string(substr($buffer,334,4));		// new
					$item['edition']=clean_string(substr($buffer,338,2));		// new
					//$item['language_code']=clean_string(substr($buffer,340,7));		// new
					$item['language']=clean_string(substr($buffer,347,35));
					$item['ccy']=trim(substr($buffer,382,3));
					$item['amount']=floatval(trim(substr($buffer,385,9)));
					if ($item['ccy'] == '' || $item['amount'] == '' || $item['amount'] == 0 ) {
						//$item['converted_ccy']=trim(substr($buffer,394,3));			// new
						//$item['converted_amount']=trim(substr($buffer,397,9));		// new
						$item['ccy']=trim(substr($buffer,394,3));			// new
						$item['amount']=floatval(trim(substr($buffer,397,9)));		// new
					}
					//$item['unkown_1']=trim(substr(490,9));
					//$item['unkown_1']=trim(substr(418,1));
					//$item['unkown_1']=trim(substr(419,1));
					//$item['is_instock']=1;			// trim(substr($buffer,379,1));
					$item['stock_level']=0;			// ltrim(trim(substr($buffer,380,7)),'0');
					//$item['branch_code']='E';		// trim(substr($buffer,387,1));
					//$item['category_code']='XXX';	// trim(substr($buffer,394,26));
					break;
			default :
					$sdf_row_type=0;
					LOG_MSG('INFO',"MASTER FORMAT - row type [$sdf_row_type]");
					$item['product_code']=trim(substr($buffer,0,10));
					$item['sku']=trim(substr($buffer,0,10));
					$item['name']=clean_string(substr($buffer,10,100));
					$item['author_1_code']=trim(substr($buffer,110,7));
					$item['author_1']=clean_string(substr($buffer,117,35));
					$item['author_2_code']=trim(substr($buffer,152,7));
					$item['author_2']=clean_string(substr($buffer,159,35));
					$item['author_3_code']=trim(substr($buffer,194,7));
					$item['author_3']=clean_string(substr($buffer,201,35));
					$item['isbn']=trim(substr($buffer,236,13));
					$item['binding']=clean_string(substr($buffer,249,35));
					$item['edition']=trim(substr($buffer,284,7));
					$item['published_year']=trim(substr($buffer,291,14));
					$item['publisher_code']=trim(substr($buffer,305,14));
					$item['publisher']=clean_string(substr($buffer,319,35));
					$item['language']=clean_string(substr($buffer,354,35));
					$item['ccy']=trim(substr($buffer,389,16));
					$item['amount']=floatval(trim(substr($buffer,405,13)));
					$item['is_instock']=trim(substr($buffer,379,1));

					// Stocks
					$item['bng_stock_level']=intval(ltrim(trim(substr($buffer,440,5)),'0'));
					$item['bm_stock_level']=intval(ltrim(trim(substr($buffer,445,5)),'0'));
					$item['ind_stock_level']=intval(ltrim(trim(substr($buffer,450,5)),'0'));
					$item['krm_stock_level']=intval(ltrim(trim(substr($buffer,455,5)),'0'));
					$item['res_stock_level']=intval(ltrim(trim(substr($buffer,460,5)),'0'));
					$item['se_stock_level']=intval(ltrim(trim(substr($buffer,465,5)),'0'));
					$item['stock_level']=$item['bng_stock_level']+
										 $item['bm_stock_level']+
										 $item['ind_stock_level']+
										 $item['krm_stock_level']+
										 $item['res_stock_level']+
										 $item['se_stock_level']-1;	// stock will always be -1 as per nijesh
					if ($item['stock_level'] < 0) $item['stock_level']=0;

					$item['division']=trim(substr($buffer,470,13));
					$item['category_code']=trim(substr($buffer,483,10));
					break;
		}
		$item['sdf_row_type']=$sdf_row_type;
		LOG_ARR('INFO',"item[$row_num]",$item);
		LOG_MSG('INFO',"item[$row_num] [".implode('|',$item)."]");


		if ( $IS_TEST_ONLY ) {
			echo "item[$row_num] [".print_arr($item)."]";
			exit;
		}

		$UPLOAD_STATUS[$row_num]=array();
		$UPLOAD_STATUS[$row_num]['row_num']=$row_num;
		$UPLOAD_STATUS[$row_num]['row_type']=($sdf_row_type == 0)? 'Master' : "Retail $sdf_row_type";
		$UPLOAD_STATUS[$row_num]['name']=$item['product_code'];
		$UPLOAD_STATUS[$row_num]['message']='';
		$UPLOAD_STATUS[$row_num]['uploaded']=false;



		// Convert currency first
		if ( isset($item['ccy']) && isset($item['amount']) && $item['ccy'] != 'RS' ) {
			//echo "item[$row_num] [".implode('|',$item)."]";
			$ccy_code=$item['ccy'];
			if (!isset($CURRENCYS[$ccy_code])) {
				$UPLOAD_STATUS[$row_num]['message']="No conversion rate defined for currency code <b>$ccy_code</b>";
				$not_uploaded_count++;
				echo '['.$item['sku'].']-['.$item['name'].']: '.$UPLOAD_STATUS[$row_num]['message']."\n";
				continue;
			}
			$mrp=$item['amount'];
			$converted_mrp=round(($CURRENCYS[$ccy_code]*$mrp));
			$item['amount'] = $converted_mrp;
			echo "do_product_import_SDF(): Currency converted [$ccy_code.$mrp] to [RS.$converted_mrp] at rate [".$CURRENCYS[$ccy_code]."]\n";
			LOG_MSG('INFO',"do_product_import_SDF(): Currency converted [$ccy_code.$mrp] to [RS.$converted_mrp] at rate [".$CURRENCYS[$ccy_code]."]");
		}

		// Product insert
		if ($sdf_row_type == 0 || $sdf_row_type == 1) {
			$product_arr=SDF_create_product($item);
			if ($product_arr === false) {
				LOG_MSG('ERROR',"do_product_import_SDF(): Product Insert failed!");
				$UPLOAD_STATUS[$row_num]['message']=$ERROR_MESSAGE;
				$ERROR_MESSAGE='';
				$not_uploaded_count++;
				echo '['.$item['sku'].']-['.$item['name'].']: '.$UPLOAD_STATUS[$row_num]['message']."\n";
				continue;
			}
		}

		// Insert Hiearchy details
		if ($sdf_row_type == 0 || $sdf_row_type == 2 ) {
			$resp=SDF_create_hierarchy($item);
			if ($resp === false) {
				LOG_MSG('ERROR',"do_product_import_SDF(): Category insert failed!");
				$UPLOAD_STATUS[$row_num]['message']=$ERROR_MESSAGE;
				$ERROR_MESSAGE='';
				$not_uploaded_count++;
				echo '['.$item['sku'].']-['.$item['name'].']: '.$UPLOAD_STATUS[$row_num]['message']."\n";
				continue;
			}
			$UPLOAD_STATUS[$row_num]['message']='Mapped product to category code <b>'.$item['category_code'].'</b>';
		}


		// Stock and price update
		if ( $sdf_row_type == 0 || $sdf_row_type == 1 || $sdf_row_type == 3 ) {
			$resp=SDF_update_stock_price($item);
			if ($resp === false) {
				LOG_MSG('ERROR',"do_product_import_SDF(): Stock/Price update failed!");
				$UPLOAD_STATUS[$row_num]['message']=$ERROR_MESSAGE;
				$ERROR_MESSAGE='';
				$not_uploaded_count++;
				echo '['.$item['sku'].']-['.$item['name'].']: '.$UPLOAD_STATUS[$row_num]['message']."\n";
				continue;
			}
			$UPLOAD_STATUS[$row_num]['message']='Updated stock level and price';
		}



		// Useless row
		if ($sdf_row_type == 4 ) {
			$UPLOAD_STATUS[$row_num]['message']='Skipping row (type 4)';
			echo '['.$item['sku'].']-['.$item['name'].']: '.$UPLOAD_STATUS[$row_num]['message']."\n";
			continue;
		}


		// Master+Retail: Product insert Commit
		if ($sdf_row_type == 0 || $sdf_row_type == 1) {
			// Display success message for format 0 and 1
			LOG_MSG('INFO','do_product_import_SDF(): ================= Uploaded Product ['.$product_arr['product_id'].']: ['.$item['sku'].']-['.$item['name'].'] inserted ====================');
			if ($product_arr['is_new_product'] == 1) $UPLOAD_STATUS[$row_num]['message']='New product ['.$item['sku'].']-['.$item['name'].'] created';
			else $UPLOAD_STATUS[$row_num]['message']='Existing product ['.$item['sku'].']-['.$item['name'].'] updated';
		}
	
		// SUCCESS
		$UPLOAD_STATUS[$row_num]['uploaded']=true;
		$uploaded_count++;
		echo $UPLOAD_STATUS[$row_num]['message']."\n";


		// commit at 1k records
		if (($uploaded_count % $BATCH_SIZE) === 0) {
			if (!sync_SOLR()) {return;} 
			db_transaction_end();

			// Log time for prev batch
			$_search_tm=round(microtime(true)-$_batch_st_tm, 2);
			echo "row_num=[$row_num] uploaded_count=[$uploaded_count] BATCH_SIZE=[$BATCH_SIZE]\n";
			echo "|------------------Syncing with SOLR: ".($row_num-$BATCH_SIZE+1)."-$row_num products (in $_search_tm secs) --------------|\n";

			sleep(10);

			// reset batch
			$_batch_st_tm=round(microtime(true), 2);
			db_transaction_start();
		}

		// Optimise at 10k records
		if (($uploaded_count % ($BATCH_SIZE*10)) === 0) {
			optimise_db();
		}


	} // end product loop
	fclose($sdf_handle);
	$UPLOAD_STATUS[0]['NROWS']=$row_num;

	//echo "Delete from tType where shop_id=210; Delete from tTag where shop_id=210; Delete from tProduct where shop_id=210;" | mysql -u shopnix -p
	// Update SOLR
	if (!sync_SOLR()) {return;} 
	db_transaction_end();
	optimise_db();
	$_search_tm=round(microtime(true)-$_batch_st_tm, 2);
	echo "|------------------FINAL Syncing with SOLR: ".($row_num-$BATCH_SIZE+1)."-$row_num products (in $_search_tm secs) --------------|\n";

	// SUCCESS MESSAGE
	if ($row_num-$not_uploaded_count > 0) add_msg("NOTICE","Uploaded <b>".($row_num-$not_uploaded_count)."</b> rows ($_search_tm secs).");
	if ($not_uploaded_count > 0) add_msg("ERROR", "Skipped <b>$not_uploaded_count</b> rows.");
	$_search_tm=round(microtime(true)-$_search_st_tm, 2);
	LOG_MSG('INFO',"--DONE--: Processed [$row_num] rows. Total script time: $_search_tm secs");
	echo "Total script time: [$_search_tm]\n";
}





?>



