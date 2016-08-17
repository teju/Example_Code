<?php

// SELECT
function db_total_fuel_select(
			$st_date="",
			$en_date="") {

	LOG_MSG('INFO',"db_total_fuel_select(): START { 
							st_date=[$st_date],
							en_date=[$en_date]	\n}");

	$param_arr=_init_db_params();
	$where_clause="WHERE travel_id=".TRAVEL_ID;
	$seperator=" AND ";
	$is_search=false;

	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$st_date,true);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$en_date,true);

	LOG_MSG('INFO',"db_total_fuel_select(): WHERE CLAUSE = [$where_clause]");

	$resp=execSQL("SELECT 
						SUM(fuel_filled) total_fuel_filled
					FROM  
						tSearch
					WHERE
						travel_id=".TRAVEL_ID." AND 
						DATE_FORMAT(created_dt,'%Y-%m-%d') BETWEEN ? AND ? "
					,$param_arr['params'], 
					false);

	$resp[0]['IS_SEARCH']=$is_search;
	LOG_MSG('INFO',"db_driver_select(): END");
	return $resp;

}

// SELECT
function db_today_fuel_select() {

	LOG_MSG('INFO',"db_today_fuel_select(): START");

	$resp=execSQL("SELECT 
						CASE 
							WHEN SUM(fuel_filled) IS NULL THEN 0
							ELSE SUM(fuel_filled)
						END AS today_fuel_filled
					FROM 
						tSearch
					WHERE
						travel_id=".TRAVEL_ID." AND
						DATE_FORMAT(created_dt,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d') AND 
						travel_id=".TRAVEL_ID
					,array(), 
					false);

	LOG_MSG('INFO',"db_today_fuel_select(): END");
	return $resp;

}

// SELECT
function db_monthly_fuel_select(
			$st_date="",
			$en_date="") {


	LOG_MSG('INFO',"db_monthly_fuel_select(): START { 
							st_date=[$st_date],
							en_date=[$en_date]	\n}");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$st_date,true);
	$param_arr=_db_prepare_param($param_arr,"s","created_dt",$en_date,true);

	$resp=execSQL("SELECT 
						SUM(fuel_filled) fuel_filled,
						DATE_FORMAT(created_dt,'%Y-%m-%d') created_dt
					FROM  
						tSearch
					WHERE
						travel_id=".TRAVEL_ID." AND
						DATE_FORMAT(created_dt,'%Y-%m-%d') BETWEEN ? AND ? 
					GROUP BY 2"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_monthly_fuel_select(): END");
	return $resp;

}

function db_total_students($date) {

	LOG_MSG('INFO',"db_total_students_on_route(): START {
												date=[$date]	\n}");

	$resp=execSQL("SELECT 
						count(s.student_id) total_students,
						s.client_id,
						s.vehicle_id,
						c.name client_name,
						v.reg_no,
						v.route,
						v.travel_id
					FROM 
						tVehicle v
						LEFT OUTER JOIN tStudent s ON(s.vehicle_id=v.vehicle_id)
						LEFT OUTER JOIN tClient c ON(v.client_id=c.client_id)
					WHERE 
						v.travel_id=".TRAVEL_ID." AND
						v.travel_id=".TRAVEL_ID."
					GROUP BY 
						v.reg_no"
					,array(), 
					false);
					

	LOG_MSG('INFO',"db_total_students_on_route(): END");
	return $resp;

}

function db_total_student_on_board($time_of_day,$time_in) {

	LOG_MSG('INFO',"db_total_student_on_board(): START
											time_of_day=[$time_of_day] \n");

	$param_arr=_init_db_params();

	$param_arr=_db_prepare_param($param_arr,"s","time_in",'%'.$time_in.'%',true);

	$resp=execSQL("SELECT 
						count(student_id) total_students_on_board,
						reg_no
					FROM 
						tStudentLog 
					WHERE 
						time_of_day = '$time_of_day' AND 
						time_in LIKE ? 
					GROUP BY 
						reg_no"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_total_student_on_board(): END");
	return $resp;

}

function db_invalid_route_select($time_of_day, $time_in) {

	LOG_MSG('INFO',"db_route_select(): START");

	$param_arr=_init_db_params();
	$param_arr=_db_prepare_param($param_arr,"s","time_in",'%'.$time_in.'%',true);

	$resp=execSQL("SELECT 
						reg_no,
						st_reg_no
					FROM 
						tStudentLog 
					WHERE 
						time_of_day = '$time_of_day' AND 
						time_in LIKE ? 
					ORDER BY
						1"
					,$param_arr['params'], 
					false);

	LOG_MSG('INFO',"db_route_select(): END");
	return $resp;

}

?>
