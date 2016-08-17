<?php 

	define('IGNORE_SWIFT','TRUE');
	$_SERVER['SERVER_NAME']='shopnix.in';

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../lib/users/db.php");
	include("../../lib/users/model.php");
	include("../../modules/utils/db.php");
	include("../../modules/utils/utils.php");

	//define('CLI_MODE',true);
	db_connect();
	echo "Start Transaction.../n";
	db_transaction_start();
	generate_footer();

	db_transaction_commit();
	echo "End Transaction/n";
	db_close();





function generate_footer($shop_id='') {

	$shop_row=db_get_list('ARRAY','name,domain,support_email,support_phone,shop_id','tShop', "domain='orthomedical-india.shopnix.in'");
	if ( $shop_row[0]['STATUS'] != 'OK' || $shop_row[0]['NROWS'] < 1 ) {
		LOG_MSG("ERROR","generate_footer(): Error fetching shops");
		return;
	}


	for ( $x=0;$x<$shop_row[0]['NROWS'];$x++ ) {
		echo "Processing: ".$shop_row[$x]['domain']."\n";
		$hierarchy_row=execSQL("SELECT
								t.type_id,
								t.type,
								c.category_id,
								c.category,
								sc.subcategory_id,
								sc.subcategory,
								t.disp_order AS type_disp_order,
								c.disp_order AS category_disp_order,
								sc.disp_order AS subcategory_disp_order
							FROM
								tType t
								LEFT OUTER JOIN tCategory c ON(t.type_id = c.type_id AND c.shop_id = ".$shop_row[$x]['shop_id'].")
								LEFT OUTER JOIN tSubCategory sc ON(c.category_id = sc.category_id AND sc.shop_id = ".$shop_row[$x]['shop_id'].")
							WHERE
								t.shop_id = ".$shop_row[$x]['shop_id']."   
							ORDER BY 
								t.type_id, 
								c.category_id, 
								sc.subcategory_id
						   ",array(),
						   false);
		if ( $hierarchy_row[0]['STATUS'] != 'OK' ) {
			LOG_MSG("ERROR","generate_footer(): Error fetching category hierarchy");
			return;
		}

		$brands_row=db_get_list('ARRAY','distinct t.tag, t.value, t.clean_value','tProdTag pt LEFT OUTER JOIN tTag AS t ON (t.tag_id = pt.tag_id)',"t.tag='Brand' and t.shop_id=".$shop_row[$x]['shop_id']);
		if ( $brands_row[0]['STATUS'] != 'OK' ) {
			LOG_MSG("ERROR","generate_footer(): Error fetching brands");
			return;
		}

		ob_start();
		include('footer_template.html');
		$footer_content=ob_get_contents();
		ob_clean();


		execSQL("DELETE from tThemeWidget where widget_name='Footer' and shop_id=".$shop_row[$x]['shop_id'],array(),true);
		$widget_resp=execSQL("INSERT INTO 
						tThemeWidget
							(widget_name,
							clean_widget,
							disp_page,
							disp_order,
							widget_type,
							content,
							is_active,
							shop_id)
							SELECT
								'Footer',
								'footer',
								'FOOTER',
								MAX(disp_order)+1,
								'HTML',
								".'"'.$footer_content.'"'.",
								1,
								".$shop_row[$x]['shop_id']."
							FROM 
								tThemeWidget
							WHERE
								shop_id=".$shop_row[$x]['shop_id']
					,array(), 
					true);
		if ($widget_resp['STATUS'] != 'OK') {
			LOG_MSG("ERROR","generate_footer(): Error inserting HTML widget");
			return;
		}
		if ( file_exists('../../../media/'.$shop_row[$x]['domain'].'/uploads') ) recurse_copy_dir('../../../media/default/uploads','../../../media/'.$shop_row[$x]['domain'].'/uploads');
		//return;
	}
}
