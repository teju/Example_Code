<?php

	include("../../lib/utils.php");
	/*include("../../tConf.php");
	include("../../lib/db.php");
	include("../../modules/utils/db.php");
	include("../../lib/users/model.php");
	include("../../lib/users/db.php");
	include("../../modules/utils/utils.php");
	include("../../modules/admin/product/db.php");
	include("../../modules/admin/product/tag/db.php");
	include("../../modules/admin/product/prodtag/db.php");
	include("../../modules/admin/inventory/db.php");

	define('CLI_MODE',true);
	//db_connect();
	do_product_save_cli();
	//db_close();
	*/
	
	//convertcsv();
	csv2arr();

function convertcsv() {

	$filename="map.csv";
	$handle=fopen($filename, "r");
	if ( $handle === false ) {
		echo "Error operning file $filename\n";
		exit;
	}

	$row_no=0;
	$c=0;
	$prev_short_code='';
	while (($item = fgetcsv($handle, 10000, ',','"')) !== FALSE)	{
		$row_no++;
		if ($row_no == 1) continue; // Skip header
		//if ($row_no == 6) break; // Skip header
		//echo print_r($item);

		$short_code=trim(get_arg($item,1));
		//$long_code=trim(get_arg($item,2));
		if ($prev_short_code !== $short_code) {
			$prev_short_code=$short_code;
			//echo "[$short_code]\n";
		}

		// Process catgories
		$idx=0;
		$hierarchy_arr=array();
		for ($i=3;$i<29;$i++) {
			$hierarchy_str=ucwords(strtolower(trim(get_arg($item,$i))));
			if ($hierarchy_str == '') continue;
			if ( $i < 19) {
				//echo "    $i: Adding [$idx][$hierarchy_str]\n";
				$hierarchy_arr[$idx]=$hierarchy_str;
				$idx++;
			} else {
				// Remove
				//echo print_r($hierarchy_arr,true);
				$pos=array_search($hierarchy_str,$hierarchy_arr);
				//echo "    $i: Removing [$pos][$hierarchy_str]\n";
				unset($hierarchy_arr[$pos]);
			}
		}
		//echo print_r($hierarchy_arr,true)."\n======================\n";
		$hierarchy_arr=array_values($hierarchy_arr);
		//echo print_r($hierarchy_arr,true)."\n======================\n";


		foreach ($hierarchy_arr as $this_hierarchy_str) {
			$this_hierarchy_arr=preg_split('/>/',$this_hierarchy_str);
			//echo print_r($this_hierarchy_arr,true);
			$new_this_hierarchy_arr=array();
			$new_this_hierarchy_arr[0]=$prev_short_code;
			$new_this_hierarchy_arr[1]='Books';
			$j=2;
			foreach ($this_hierarchy_arr as $value) {
				$value=ucwords(strtolower(trim($value)));
				if ($value == '') continue;
				$new_this_hierarchy_arr[$j]=$value;
				$j++;
			}
			//echo print_r($new_this_hierarchy_arr,true);
			//echo implode(",",$new_this_hierarchy_arr)."\n";
			$CSV_ARR[$c++]=$new_this_hierarchy_arr;
		}
	}
	
	//echo print_r($CSV_ARR,true);
	usort($CSV_ARR,"compare_arr");// Sort the array
	echo print_r($CSV_ARR,true);


	$op_filename="new_map.csv";
	$op_handle = fopen($op_filename, 'w');
	if ( $op_handle === false ) {
		echo "Error operning output file $op_filename\n";
		exit;
	}
	foreach ($CSV_ARR as $new_this_hierarchy_arr) {
		fputcsv($op_handle, $new_this_hierarchy_arr,',','"');
		 
	}

	fclose($handle);
	fclose($op_handle);
}



function csv2arr() {

	$filename="new_map.csv";
	$handle=fopen($filename, "r");
	if ( $handle === false ) {
		echo "Error operning file $filename\n";
		exit;
	}


	$op_filename="catmap.php";
	$op_handle = fopen($op_filename, 'w');
	if ( $op_handle === false ) {
		echo "Error operning output file $op_filename\n";
		exit;
	}

	echo "Starting conversion...\n";

	/*
	$ARR=array('ACT' => array(	array('type'=>'adsa', 'cat' => 'asdas', 'subcat' => 'asdsads'),
								array('type'=>'adsa', 'cat' => 'asdas', 'subcat' => 'asdsads'),
								),
			   'ARG' => array(	array('type'=>'adsa', 'cat' => 'asdas', 'subcat' => 'asdsads'),
								array('type'=>'adsa', 'cat' => 'asdas', 'subcat' => 'asdsads'),
							)
				);
	echo print_r($ARR,true);
	*/




	$row_no=0;
	$prev_short_code='';
	$short_code_counter=0;
	$CATMAP=array();
	fwrite($op_handle,"<?php\n\$CATMAP=array();\n");
	while (($item = fgetcsv($handle, 10000, ',','"')) !== FALSE)	{
		$row_no++;
		if ($row_no == 1) continue; // Skip header
		//if ($row_no == 20) break; // Skip header
		//echo print_r($item);


		$short_code=get_arg($item,0);
		$type=get_arg($item,1);
		$cat=get_arg($item,2);
		$subcat=get_arg($item,3);

		if ($prev_short_code != $short_code) {
			$prev_short_code=$short_code;
			$short_code_counter=0;
			echo "\n";
			fwrite($op_handle,"\n");
		}

		if (!empty($type)) {
			fwrite($op_handle,"\$CATMAP['$short_code'][$short_code_counter]['type']='$type';\n");
			echo "\$CATMAP[$short_code][$short_code_counter]['type']='$type'\n";
			$CATMAP[$short_code][$short_code_counter]['type']=$type;
		}
		if (!empty($cat)) {
			fwrite($op_handle,"\$CATMAP['$short_code'][$short_code_counter]['cat']='$cat';\n");
			echo "\$CATMAP[$short_code][$short_code_counter]['cat']='$cat'\n";
			$CATMAP[$short_code][$short_code_counter]['cat']=$cat;
		}
		if (!empty($subcat)) {
			fwrite($op_handle,"\$CATMAP['$short_code'][$short_code_counter]['subcat']='$subcat';\n");
			echo "\$CATMAP[$short_code][$short_code_counter]['subcat']='$subcat'\n";
			$CATMAP[$short_code][$short_code_counter]['subcat']=$subcat;
		}
		$short_code_counter++;
	}
	fwrite($op_handle,"?>\n");


	//echo print_r($CATMAP['ANA'],true);
	fclose($handle);
	echo "Done\n";
}










function compare_arr($a,$b)
{
	
	if ( strcmp(get_arg($a,0),get_arg($b,0)) !== 0 ) return strcmp(get_arg($a,0),get_arg($b,0));
	if ( strcmp(get_arg($a,1),get_arg($b,1)) !== 0 ) return strcmp(get_arg($a,1),get_arg($b,1));
	if ( strcmp(get_arg($a,2),get_arg($b,2)) !== 0 ) return strcmp(get_arg($a,2),get_arg($b,2));
	else strcmp(get_arg($a,3),get_arg($b,3));
}

?>
