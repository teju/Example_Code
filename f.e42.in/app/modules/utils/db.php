<?php

//SNC128: Fixed to prevent duplicate names
// Get number of products with this tag
function db_get_menu_hierarchy() {

       LOG_MSG('INFO',"db_get_menu_hierarchy(): START ");

       $resp=execSQL("SELECT
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
						tType t,
						tCategory c,
						tSubCategory sc
					WHERE
						t.shop_id = ".SHOP_ID." AND 
						c.shop_id = ".SHOP_ID." AND 
						sc.shop_id = ".SHOP_ID."  AND 
						t.type_id = c.type_id AND
						c.category_id = sc.category_id
					ORDER BY 
						t.type_id, 
						c.category_id, 
						sc.subcategory_id
				   ",array(),
				   false);

       LOG_MSG('INFO',"db_get_menu_hierarchy(): END");
       return $resp;
}


// Get the hierarchy structure. This function will get even those that do not have all the 3 levels.
// eg:
// type_id  type    category_id   Category         subcategory_id    subcategory
// ------   -----   -----------   --------         --------------    -----------
// 2215     Women   7397          Clothing Women   7402              Jeans      
// 2216     Men     7401          Footwear Men     7406              Sandals    
// 2217     XXX     7403          yyy              NULL              NULL       
// 2217     XXX     7404          zzz              NULL              NULL       
// 2218     abc     NULL          NULL             NULL              NULL       
function db_get_allmenu_hierarchy() {

	LOG_MSG('INFO',"db_get_allmenu_hierarchy(): START ");

	$resp=execSQL("SELECT
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
			LEFT OUTER JOIN tCategory AS c ON (t.type_id = c.type_id AND c.shop_id =".SHOP_ID.")
			LEFT OUTER JOIN tSubCategory AS sc ON (c.category_id = sc.category_id AND sc.shop_id =".SHOP_ID.")
		WHERE
			t.shop_id = ".SHOP_ID."
		ORDER BY 
			t.type_id, 
			c.category_id, 
			sc.subcategory_id
	   ",array(),
	   false);

	LOG_MSG('INFO',"db_get_allmenu_hierarchy(): END");
	return $resp;
}



//SNC128: Fixed to prevent duplicate names
// Get all the locations of SHOP_ID
function db_get_location_hierarchy($area_str='') {

	LOG_MSG('INFO',"db_get_location_hierarchy(): START area_str=[$area_str]");

	$param_arr=_init_db_params();
	if ( $area_str !== '' ) {
		$where_clause=" AND CONCAT(LOWER(a.area_name),' - ',a.pincode) LIKE LOWER(?) ";
		$param_arr=_db_prepare_param($param_arr,"s","area_name",'%'.$area_str.'%',true);
	} else {
		$where_clause='';
		$param_arr['params']=array();
	}

	$resp=execSQL("
				SELECT
					s.state_id,
					s.state_name,
					c.city_id,
					c.city_name,
					a.area_id,
					a.area_name,
					a.pincode,
					CONCAT(a.area_name,' - ',a.pincode) as area_str
				FROM
					tState s,
					tCity c,
					tArea a
				WHERE
					s.state_id = c.state_id AND
					s.is_active=1 AND 
					s.shop_id=".SHOP_ID." AND  
					c.city_id = a.city_id AND 
					c.is_active=1 AND 
					c.shop_id=".SHOP_ID." AND 
					a.is_active=1 AND 
					a.shop_id=".SHOP_ID."
				$where_clause 
				ORDER BY
					s.state_id,
					c.city_id,
					a.area_id
				LIMIT 20 
				",$param_arr['params'],
				false);

	LOG_MSG('INFO',"db_get_location_hierarchy(): END");
	//LOG_ARR("INFO","LOCATION RESPONSE", $resp);
	return $resp;
}


// SELECT
function db_user_shop_select($shop_id) {


	LOG_MSG('INFO',"db_shop_select(): START { 
							shop_id=[$shop_id]\n}");

	$param_arr=_init_db_params();

	// WHERE CLAUSE
	$where_clause="WHERE s.shop_id= ?";
	$param_arr=_db_prepare_param($param_arr,"i","shop_id",$shop_id,true);

	$resp=execSQL("SELECT 
						s.shop_id,
						s.name,
						s.domain,
						s.title,
						s.description,
						s.support_email,
						s.support_phone,
						s.theme,
						s.color,
						s.is_multistore,
						s.is_active,
						s.created_dt,
						u.fname
					FROM 
						tShop s 
						LEFT OUTER JOIN tUser u ON(s.shop_id = u.shop_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	//echo "<br><pre> RESP=".print_r($resp,true)."</pre><br>";
	LOG_MSG('INFO',"db_shop_select(): END");
	return $resp;
}

function db_user_shop_intercom_select($shop_id='') {


	LOG_MSG('INFO',"db_user_shop_intercom_select(): START { shop_id=[$shop_id]}");

	$param_arr=_init_db_params();

	// WHERE CLAUSE
	if ($shop_id != "" ) {
		$where_clause="WHERE s.shop_id= ?";
		$param_arr=_db_prepare_param($param_arr,"i","shop_id",$shop_id,true);
	} else {
		$where_clause=" WHERE domain like '%.shopnix.org'";
		$param_arr['params']=array();
	}

	$resp=execSQL("SELECT 
						s.shop_id,
						s.name,
						s.domain,
						s.created_dt,
						u.user_id,
						u.email_id,
						u.fname,
						u.mobile
					FROM 
						tShop s 
						LEFT OUTER JOIN tUser u ON(s.shop_id = u.shop_id)
					".$where_clause." 
					ORDER BY 1  desc
					"
					,$param_arr['params'], 
					false);

	//echo "<br><pre> RESP=".print_r($resp,true)."</pre><br>";
	LOG_MSG('INFO',"db_user_shop_intercom_select(): END");
	return $resp;
}


function db_shopsetting_intercom_select($shop_id) {


	LOG_MSG('INFO',"db_shopsetting_intercom_select(): START { shop_id=[$shop_id]}");

	// Get subscription info for the shop
	$resp=execSQL("SELECT 
						name,
						value
					FROM 
						tShopSetting
					WHERE
						shop_id = $shop_id 
						AND name IN ('subscription_end_date','subscription_is_trial','subscription_is_renewed','max_no_of_products') "
					,array(), 
					false);

	//echo "<br><pre> RESP=".print_r($resp,true)."</pre><br>";
	LOG_MSG('INFO',"db_shopsetting_intercom_select(): END");
	return $resp;
}




// UPDATE the products timestamp 
// So whenever a prodtag row is delete, we tell solr that the product was updated as well.
// bcoz solr does not know when a prodtag row is deleted.
function db_product_touch($product_id_arr) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_product_update_timestamp(): START product_id_arr=[$product_id_arr]");

	$placeholders='';
	foreach ( $product_id_arr as $key => $product_id ) {
		$param_arr=_db_prepare_param($param_arr,"i","product_id",$product_id,true);
		if ( !$placeholders ) $placeholders='?'; else $placeholders.=',?';
	}

	$resp=execSQL("UPDATE  
						tProduct 
					SET 
						last_modified=now()
					WHERE
						product_id IN ($placeholders)
						AND shop_id = ".SHOP_ID
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_product_update_timestamp(): END");
	return $resp;
}

// Get Shop plans from the shop payments.shopnix.in
function db_get_plans() {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_get_plans(): START");

	$resp=execSQL("SELECT 
						p.product_id, 
						p.name, 
						p.product_url, 
						p.net_price
					 FROM 
						tProduct AS p 
						LEFT OUTER JOIN tShop AS s ON (p.shop_id = s.shop_id) 
					 WHERE 
						s.domain = 'payments.shopnix.in' 
					 ORDER BY 
							product_id ASC"
					,array(), 
					false);


	LOG_MSG('INFO',"db_get_plans(): END");
	return $resp;
}



// Get city and state names of area
function db_get_state_city_area($pincode) {

	LOG_MSG('INFO',"db_get_state_city_area(): START { pincode=[$pincode]	\n}");


	$param_arr=_init_db_params();
	$param_arr=_db_prepare_param($param_arr,"i","pincode",$pincode,true);

	$resp=execSQL("SELECT
						s.state_name,
						c.city_name,
						a.area_name 
					FROM
						tState AS s 
						LEFT OUTER JOIN tCity AS c ON (s.state_id =c.state_id AND s.is_active=1 AND c.is_active=1 )
						LEFT OUTER JOIN tArea AS a ON (c.city_id =a.city_id AND a.is_active=1)
					WHERE
						a.pincode = ? AND 
						a.shop_id=".SHOP_ID."
					ORDER BY 
						s.state_name"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_get_state_city_area(): END");
	return $resp;

}

?>
