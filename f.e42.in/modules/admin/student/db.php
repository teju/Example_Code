<?php


// SELECT
function db_student_select(
			$student_id="",
			$name="",
			$student_photo="",
			$id_number="",
			$address="",
			$phone="",
			$guardian_name="",
			$email_id="",
			$guardian_photo="",
			$client_id="",
			$vehicle_id="",
			$is_active="",
			$exp_dt="") {


	LOG_MSG('INFO',"db_student_select: START { 
							student_id=[$student_id],
							name=[$name],
							guardian_name=[$guardian_name],
							id_number=[$id_number],
							address=[$address],
							phone=[$phone],
							student_photo=[$student_photo],
							guardian_photo=[$guardian_photo],
							exp_dt=[$exp_dt],
							is_active=[$is_active]	\n}");



	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator=" AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." s.student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
	} else {
		if ( $name !== "" ) { 
			$where_clause.=$seperator." s.name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","name","%".$name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $student_photo !== "" ) { 
			$where_clause.=$seperator." s.student_photo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","student_photo","%".$student_photo."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $exp_dt !== "" ) { 
			$where_clause.=$seperator." s.exp_dt like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","exp_dt","%".$exp_dt."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $id_number !== "" ) { 
			$where_clause.=$seperator." s.id_number like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","id_number","%".$id_number."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $address !== "" ) { 
			$where_clause.=$seperator." s.address like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","address","%".$address."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $phone !== "" ) { 
			$where_clause.=$seperator." s.phone like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","phone","%".$phone."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $guardian_name !== "" ) { 
			$where_clause.=$seperator." s.guardian_name like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","guardian_name","%".$guardian_name."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $email_id !== "" ) { 
			$where_clause.=$seperator." s.email_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","email_id","%".$email_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $guardian_photo !== "" ) { 
			$where_clause.=$seperator." s.guardian_photo like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","guardian_photo","%".$guardian_photo."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $client_id !== "" ) { 
			$where_clause.=$seperator." c.client_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","client_id","%".$client_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $vehicle_id !== "" ) { 
			$where_clause.=$seperator." s.vehicle_id like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","vehicle_id","%".$vehicle_id."%",true);
			$seperator="AND ";
			$is_search=true;
		}
		if ( $is_active !== "" ) { 
			$where_clause.=$seperator." s.is_active like ? ";
			$param_arr=_db_prepare_param($param_arr,"s","is_active","%".$is_active."%",true);
			$seperator="AND ";
			$is_search=true;
		}
	}

	// No where clause
	if ( $where_clause ===  "WHERE s.travel_id=".TRAVEL_ID ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_student_select(): WHERE CLAUSE = [$where_clause]");



	$resp=execSQL("SELECT 
						s.student_id,
						s.name,
						s.student_photo,
						s.id_number,
						s.address,
						s.phone,
						s.guardian_name,
						s.email_id,
						s.guardian_photo,
						c.client_id ,
						c.name client_name ,
						s.vehicle_id,
						s.exp_dt,
						v.reg_no,
						s.is_active
					FROM 
						tStudent s
						LEFT OUTER JOIN tClient c ON(s.client_id=c.client_id AND s.travel_id=c.travel_id)
						LEFT OUTER JOIN tVehicle v ON(s.vehicle_id=v.vehicle_id AND s.travel_id=v.travel_id)
					".$where_clause
					,$param_arr['params'], 
					false);
					

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_student_select(): END");
	return $resp;
}


// INSERT
function db_student_insert(
							$name,
							$student_photo,
							$id_number,
							$address,
							$phone,
							$guardian_name,
							$email_id,
							$guardian_photo,
							$client_id,
							$vehicle_id,
							$exp_dt,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_student_insert(): START { 
						
							name=[$name],
							id_number=[$id_number],
							address=[$address],
							phone=[$phone],
							is_active=[$is_active]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","student_photo",$student_photo);
	$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","phone",$phone);
	$param_arr=_db_prepare_param($param_arr,"s","guardian_photo",$guardian_photo);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id);
	$param_arr=_db_prepare_param($param_arr,"s","guardian_name",$guardian_name);
	$param_arr=_db_prepare_param($param_arr,"s","client_id",$client_id);
	$param_arr=_db_prepare_param($param_arr,"s","vehicle_id",$vehicle_id);
	$param_arr=_db_prepare_param($param_arr,"s","is_active",$is_active);
	$param_arr=_db_prepare_param($param_arr,"s","exp_dt",$exp_dt);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);
	

	$resp=execSQL("INSERT INTO 
						tStudent
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_insert(): END");
	return $resp;

}



// UPDATE
function db_student_update(	
							$student_id,
							$name,
							$student_photo,
							$id_number,
							$address,
							$phone,
							$guardian_name,
							$email_id,
							$guardian_photo,
							$client_id,
							$vehicle_id,
							$exp_dt,
							$is_active) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_student_update(): START {
							student_id=[$student_id],
							name=[$name],
							id_number=[$id_number],
							address=[$address],
							phone=[$phone],
							student_photo=[$student_photo],
							client_id=[$client_id],
							vehicle_id=[$vehicle_id],
							is_active=[$is_active]	
							exp_dt=[$exp_dt]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","name",$name);
	$param_arr=_db_prepare_param($param_arr,"s","student_photo",$student_photo);
	$param_arr=_db_prepare_param($param_arr,"s","id_number",$id_number);
	$param_arr=_db_prepare_param($param_arr,"s","address",$address);
	$param_arr=_db_prepare_param($param_arr,"s","phone",$phone);
	$param_arr=_db_prepare_param($param_arr,"s","guardian_name",$guardian_name);
	$param_arr=_db_prepare_param($param_arr,"s","email_id",$email_id);
	$param_arr=_db_prepare_param($param_arr,"s","guardian_photo",$guardian_photo);
	$param_arr=_db_prepare_param($param_arr,"s","client_id",$client_id);
	$param_arr=_db_prepare_param($param_arr,"s","vehicle_id",$vehicle_id);
	$param_arr=_db_prepare_param($param_arr,"s","exp_dt",$exp_dt);
	$param_arr=_db_prepare_param($param_arr,"s","is_active",$is_active);
	

	// For the where clause
	$where_clause=" WHERE student_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);


	$resp=execSQL("UPDATE  
						tStudent
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_update(): END");
	return $resp;
}



// DELETE
function db_student_delete($student_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_student_delete(): START { student_id=[$student_id]");

	// For the where clause
	$where_clause=" WHERE student_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);


	$resp=execSQL("DELETE FROM  
						tStudent"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_delete(): END");
	return $resp;
}

// UPDATE
function db_student_image_update(
							$student_id) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_student_image_update(): START {
							student_id=[$student_id]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","student_photo","no_image.jpg");

	// For the where clause
	$where_clause=" WHERE student_id=? AND travel_id=".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);


	$resp=execSQL("UPDATE  
						tStudent
					SET ".$param_arr['update_fields']
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_student_image_update(): END");
	return $resp;
}

// SELECT
function db_group_select($student_id ="",$group_id="") {

	LOG_MSG('INFO',"db_group_select: START { }");

	$param_arr=_init_db_params();
	$where_clause="WHERE s.travel_id=".TRAVEL_ID;
	$seperator="  AND";
	$is_search=false;

	// WHERE CLAUSE
	// If primary key is passed, then we use it only
	// Otherwise we search based on the fields required
	if ( $student_id !== "" ) { 
		$where_clause.=$seperator." s.student_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id,true);
	} 

	if ( $group_id !== "" ) { 
		$where_clause.=$seperator." s.group_id = ? ";
		$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);
	} 

	// No where clause
	if ( $where_clause === "WHERE s.travel_id=".TRAVEL_ID  ) {
		$param_arr['params']=array();
	}
	LOG_MSG('INFO',"db_group_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						s.student_id,
						s.group_id,
						g.group_name group_name
					FROM 
						tStudentGroup s
						LEFT OUTER JOIN tGroup g ON(s.group_id=g.group_id AND s.travel_id = g.travel_id)
					".$where_clause
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_group_select(): END");
	return $resp;
}

function db_group_location_insert($student_id,$group_id) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_group_location_insert(): START { 
							student_id=[$student_id],
							group_id=[$group_id]\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","student_id",$student_id);
	$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id);
	$param_arr=_db_prepare_param($param_arr,"i","travel_id",TRAVEL_ID);

	$resp=execSQL("INSERT INTO 
						tStudentGroup
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_group_location_insert(): END");
	return $resp;

}
function db_group_location_delete($group_id) {

	$param_arr=_init_db_params();
	LOG_MSG('INFO',"db_group_location_delete(): START { group_id=[$group_id]");

	// For the where clause
	$where_clause=" WHERE group_id=? AND travel_id = ".TRAVEL_ID;
	$param_arr=_db_prepare_param($param_arr,"i","group_id",$group_id,true);

	$resp=execSQL("DELETE FROM  
						tStudentGroup"
					.$where_clause
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_group_location_delete(): END");
	return $resp;
}

?>