<?php

function db_connect() {
	global $conn, $TRANSACTION_STATUS;
	LOG_MSG('DEBUG',"db_connect(): START");

	// New Connection
	$conn = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_NAME);
	$TRANSACTION_STATUS=false;	// To track transaction status and prevent nested transactions

	// Check for errors
	if(mysqli_connect_errno()){
		add_msg("ERROR","Error connecting to DB: ".mysqli_connect_error());
	}
	LOG_MSG('INFO',"db_connect(): Connected");
}

// Close connection
function db_close() {
	global $conn;
	LOG_MSG('DEBUG',"db_close(): START");

	$conn->close();
	LOG_MSG('INFO',"db_close(): Closed");
}

/* set autocommit to off */
function db_transaction_start() {
	global $conn, $TRANSACTION_STATUS;
	$TRANSACTION_STATUS=true;	// To track transaction status and prevent nested transactions
	LOG_MSG('INFO',"**********************STARTING TRANSACTION***********************");
	$conn->autocommit(FALSE);
}


/* commit transaction */
function db_transaction_commit() {
	global $conn, $TRANSACTION_STATUS;
	$TRANSACTION_STATUS=false;	// To track transaction status and prevent nested transactions
	LOG_MSG('INFO',"************************COMMIT TRANSACTION***********************");
	$conn->commit();
}

/* rollback transaction */
function db_transaction_rollback() {
	global $conn, $TRANSACTION_STATUS;
	$TRANSACTION_STATUS=false;	// To track transaction status and prevent nested transactions
	LOG_MSG('INFO',"**********************ROLLBACK TRANSACTION***********************");
	$conn->rollback();
}

/* set autocommit to on */
function db_transaction_end() {
	global $conn;
	LOG_MSG('INFO',"*************************END TRANSACTION************************");
	$conn->autocommit(TRUE);
}


// Generic function to prepare and execute a SQL
// query - is the actual query with placeholders
// params - is the datatype and values to bind_params. Null if nothing is required
// close - is true if there is no data to be returned (eg insert). False if there is data (eg select)
function execSQL($query, $params, $close){
	global $error_message;
	global $conn;

	// LOG
	LOG_MSG('DEBUG',"execSQL(): START");
	LOG_MSG('DEBUG'," QUERY=[".$query."]");
	LOG_MSG('DEBUG'," PARAMS\n[".print_r($params,true)."]");


	$log_query=preg_replace("/\t/"," ",$query);
	$log_query=preg_replace("/\n/"," ",$log_query);
	$log_query=preg_replace("/[\s]+/"," ",$log_query);
	LOG_MSG('INFO'," QUERY=[$log_query] PARAMS=[".implode("|",$params)."]");

	// Reset result set before starting
	$resp = array("STATUS"=>"ERROR");	// For DMLs
	$resp[0]['STATUS']="ERROR";			// For Selects
	$error_message="There was an error proccessing your request. Please check and try again";



	// INIT STATEMENT
	if ( !$stmt = mysqli_stmt_init($conn) ) {
		LOG_MSG('ERROR',"execSQL(): Error initializing statement: [".mysqli_errno($conn).": ".mysqli_error($conn)."]. ");
		$resp['SQL_ERROR_CODE']=mysqli_errno($conn);
		return $resp;
	}
	LOG_MSG('DEBUG',"execSQL():\t Init query");


	// PREPARE
	if ( !mysqli_stmt_prepare($stmt,$query) ) {
		LOG_MSG('ERROR',"execSQL(): Error preparing statement: [".mysqli_errno($conn).": ".mysqli_error($conn)."].");
		$resp['SQL_ERROR_CODE']=mysqli_errno($conn);
		return $resp;
	}
	LOG_MSG('DEBUG',"execSQL():\t Prepared query");


	// BIND PARAMS
	if ( !empty($params) ) {
		// Bind input params
		if (!call_user_func_array(array($stmt, 'bind_param'), refValues($params))) {
			LOG_MSG('ERROR',"execSQL(): Error binding input params: [".mysqli_errno($conn).": ".mysqli_error($conn)."].");
			$resp['SQL_ERROR_CODE']=mysqli_errno($conn);
			mysqli_stmt_close($stmt);			// Close statement
			return $resp;
		}
	}
	LOG_MSG('DEBUG',"execSQL():\t Bound query parameters");


	// EXECUTE 
	$qry_exec_time=microtime(true);
	if ( isset($_SESSION['admin']['shop']) && get_arg($_SESSION['admin']['shop'],'domain') == DEMO_STORE  && $close ) { 
		$status=true;	
	} else {
		$status=mysqli_stmt_execute($stmt); 
	}
	$qry_exec_time=number_format(microtime(true)-$qry_exec_time,4);

	if ( !$status ) {
		LOG_MSG('ERROR',"execSQL(): Error executing statement: [".mysqli_errno($conn).": ".mysqli_error($conn)."].");
		$resp['SQL_ERROR_CODE']=mysqli_errno($conn);
		mysqli_stmt_close($stmt);			// Close statement
		return $resp;
	}
	LOG_MSG('INFO',"      Executed query in $qry_exec_time secs");


	// DMLs (insert/update/delete)
	// If CLOSE, then return no of rows affected
	if ($close) {
		unset($resp[0]);
		$error_message="";
		$resp["STATUS"]="OK";
		$resp["EXECUTE_STATUS"]=$status;
		$resp["NROWS"]=$conn->affected_rows;
		$resp["INSERT_ID"]=$conn->insert_id;
		mysqli_stmt_close($stmt);			// Close statement
		LOG_MSG('INFO',"      Status=[OK] Affected rows [".$resp['NROWS']."]");
		LOG_MSG('DEBUG',"execSQL(): UPDATE/INSERT response:\n[".print_r($resp,true)."]");
		LOG_MSG('DEBUG',"execSQL(): END");
		return $resp;
	}

	// SELECT
	$result_set = mysqli_stmt_result_metadata($stmt);
	while ( $field = mysqli_fetch_field($result_set) ) {
		$parameters[] = &$row[$field->name];
	}

	// BIND OUTPUT
	if ( !call_user_func_array(array($stmt, 'bind_result'), refValues($parameters))) {
		LOG_MSG('ERROR',"execSQL(): Error binding output params: [".mysqli_errno($conn).": ".mysqli_error($conn)."].");
		$resp[0]['SQL_ERROR_CODE']=mysqli_errno($conn);
		mysqli_free_result($result_set);	// Close result set
		mysqli_stmt_close($stmt);			// Close statement
		return $resp;
	}
	LOG_MSG('DEBUG',"execSQL():\t Bound output parameters");


	// FETCH DATA
	$i=0;
	while ( mysqli_stmt_fetch($stmt) ) {  
		$x = array();
		foreach( $row as $key => $val ) {  
			$x[$key] = $val;  
		}
		$results[] = $x; 
		$i++;
	}
	$results[0]["NROWS"]=$i;

	$error_message="";					// Reset Error message
	$results[0]["STATUS"]="OK";			// Reset status
	mysqli_free_result($result_set);	// Close result set
	mysqli_stmt_close($stmt);			// Close statement

	LOG_MSG('INFO',"      Status=[OK] Affected rows [".$results[0]['NROWS']."]");
	LOG_MSG('DEBUG',"execSQL(): SELECT Response:\n[".print_r($results[0],true)."]");
	LOG_MSG('DEBUG',"execSQL(): END");

	return  $results;
}

function refValues($arr){
	if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
	{
		$refs = array();
		foreach($arr as $key => $value)
			$refs[$key] = &$arr[$key];
		return $refs;
	}
	return $arr;
}

function _init_db_params() {

	$arr=array();

	// For Prepare
	$arr["fields"]="";
	$arr["placeholders"]="";
	$arr["update_fields"]="";
	$arr["where_clause"]="";

	// For Bind Params
	$arr["params"]=array();
	$arr["params"][0]="";

	return $arr;
}

// Field names   - eg: user_id, fname, mobile, address
// Values        - eg: 23, 'Lalith', '+9832432432', '2nd cross'
// Type          - Datatype of the values eg: isssi
// place holders - eg: ?,?,NULL,?
// update_fields - For update statements eg: fname=?, lname=NULL,mobile="+9132132132'
// is_clause     - is this a where clause value, in which case we don't need placeholders or uplaceholders
function _db_prepare_param($arr,$type,$name,$val=NULL,$is_clause=false) {
	global $conn;
	$is_val_null=false;

	if ( !$is_clause) { // skip where clause calls
		// Field Names 
		if ( $arr["fields"] == "" ) { $arr["fields"]=$name;} else { $arr["fields"].=",".$name;}
		// Null Values
		if ( is_null($val) || $val==="") {
				$is_val_null=true;
				if ( $arr["placeholders"] == "" ) { $arr["placeholders"]="NULL";} else { $arr["placeholders"].=",NULL";}
				if ( $arr["update_fields"] == "" ) { $arr["update_fields"]=$name."=NULL";} else { $arr["update_fields"].=", ".$name."=NULL";}
		} else { // Not null Values
				if ( $arr["placeholders"] == "" ) { $arr["placeholders"]="?"; } else { $arr["placeholders"].=",?";}
				if ( $arr["update_fields"] == "" ) { $arr["update_fields"]=$name."=?";} else { $arr["update_fields"].=", ".$name."=?";}
		}
	}

	// Add the value and its type only if its not null
	if ( !$is_val_null ) {
		$arr["params"][0].=$type;
		array_push($arr["params"],$val);
	}

	return $arr;
	//echo "<pre>     arr=[".print_r($arr,true)."]</pre>";
}




// Field names   - eg: user_id, fname, mobile, address
// Values        - eg: 23, 'Lalith', '+9832432432', '2nd cross'
// Type          - Datatype of the values eg: isssi
// place holders - eg: ?,?,NULL,?
// uplaceholders - For update statements eg: fname=?, lname=NULL,mobile="+9132132132'
function _db_prepare_param2($arr,$type,$name,$val=NULL) {
	// Field Names 
	if ( $arr["fields"] == "" ) { $arr["fields"]=$name;} else { $arr["fields"].=",".$name;}

	/* Major lesson in PHP Comparisons!
	echo "<pre>".$name." val=[".$val."]
		 isset(val)=[".isset($val)."]
		 is_null(val)=[".is_null($val)."]
		 is_numeric(val)=[".is_numeric($val)."]
		 (val==0) =[".($val == 0)."]
		 (val=='') =[".($val == '')."]
		 (val==='') =[".($val === '')."]
		 </pre><br>";
	*/
	if ( is_null($val) || $val==="") {
		if ( $arr["placeholders"] == "" ) { $arr["placeholders"]="NULL";} else { $arr["placeholders"].=",NULL";}
		if ( $arr["uplaceholders"] == "" ) { $arr["uplaceholders"]=$name."=NULL";} else { $arr["uplaceholders"].=", ".$name."=NULL";}
	} else {
		if ( $arr["placeholders"] == "" ) { $arr["placeholders"]="?"; } else { $arr["placeholders"].=",?";}
		if ( $arr["uplaceholders"] == "" ) { $arr["uplaceholders"]=$name."=?";} else { $arr["uplaceholders"].=", ".$name."=?";}
		if ( $arr["values"] == "" ) { $arr["values"]=$val; } else { $arr["values"].=",".$val;}
		$arr["types"].=$type; 
		//if ( $arr["bind_params"] == "" ) { $arr["bind_params"]="$".$name; } else { $arr["bind_params"].=", $".$name;}
	}
}



// Escape special characters
function mysql_escape_mimic($inp) {
	if(is_array($inp)) 
		return array_map(__METHOD__, $inp); 
	
	if(!empty($inp) && is_string($inp)) { 
		return str_replace(	array('\\', 	"\0", 	"\n", 	"\r", 	"'", 	'"', 	",",	"\x1a"), 
							array('\\\\', 	'\\0', 	'\\n', 	'\\r', 	"\\'", 	'\\"', 	'\,',	'\\Z'), $inp); 
	} 
	
	return $inp; 
}

/**********************************************************************/
/*                    FOREIGN KEY FUNCTIONS                           */
/**********************************************************************/

function db_get_fk_values($table,$foreign_key,$opt_fields="") {

	LOG_MSG("INFO","####### db_get_fk_values(): table=$table key=$foreign_key ");
	$unique_field=get_unique_field($table);
	LOG_MSG("INFO","####### db_get_fk_values: unique field = $unique_field");
	
	if ( $opt_fields !== "" ) {
		$opt_fields=", ".$opt_fields;
	}
	
	$row=execSQL("	SELECT 
						$foreign_key,
						$unique_field AS name 
						".$opt_fields."
					FROM 
						$table 
						ORDER BY 2"
				,array(),false);
	return $row;
}

function db_get_list($TYPE='LIST',$fields,$table,$where_clause="") {

	LOG_MSG("INFO","####### db_get_list(): TYPE=$TYPE, fields=$fields,table=$table,where_clause=$where_clause ");

	// Only allow single fields for LIST
	if ( $TYPE == 'LIST' && preg_match('/,/',$fields) ) {
		add_msg('ERROR','Internal error. Pease contact customer service.');
		LOG_MSG('ERROR','db_get_list(): Type LIST should have only one SELECT field');
		return false;
	}

	if ( $where_clause ) {
			$where_clause=" WHERE ".$where_clause;
	}

	$row=execSQL("	SELECT 
						$fields 
					FROM 
						$table 
						$where_clause
					ORDER 
						BY 1"
				,array(),false);
	if($row[0]['STATUS'] != 'OK') {
		add_msg('ERROR','Internal error. Pease contact customer service.');
		LOG_ARR('INFO','row',$row);
		return false;
	}

	if ( $TYPE == 'LIST' ) {
		$values="";
		$seperator="";
		//LOG_MSG('INFO',"=======================".print_r($row,true));
		for ($i=0;$i<$row[0]['NROWS'];$i++) {
			$values=$values.$seperator.$row[$i][$fields];
			$seperator=",";
			//LOG_MSG('INFO',"======[$fields]===[$values]");
		}
		return $values;
	} else {
		return $row;
	}
}


function get_unique_field($table) {

	LOG_MSG("INFO","####### GETTING UNIQUE KEY COLUMN NAME : for ".DB_NAME.$table);
	$row=execSQL("
				SELECT 
					column_name
				FROM 
					information_schema.COLUMNS
				WHERE 
					table_name = '".$table."' AND
					table_schema = '".DB_NAME."' AND
					column_key='UNI';",
				array(),false); 

	// If no unique key was found above
	if (!isset($row[0]['column_name'])) {
		LOG_MSG("INFO","####### GETTING PRIMARY KEY COLUMN NAME : for ".DB_NAME.$table);
		$row=execSQL("
				SELECT 
					column_name
				FROM 
					information_schema.COLUMNS
				WHERE 
					table_name = '".$table."' AND
					table_schema = '".DB_NAME."' AND
					column_key='PRI';",
				array(),false); 
	}
	return $row[0]['column_name'];

}


// INSERT
function db_emails_insert(
							$from,
							$to,
							$cc,
							$bcc,
							$subject,
							$message,
							$status,
							$headers,
							$emailer) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_emails_insert(): START { 
							from=[$from],
							to=[$to],
							cc=[$cc],
							bcc=[$bcc],
							subject=[$subject],
							message=[$message],
							status=[$status],
							headers=[$headers],
							emailer=[$emailer]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"s","`from`",$from);
	$param_arr=_db_prepare_param($param_arr,"s","`to`",$to);
	$param_arr=_db_prepare_param($param_arr,"s","`cc`",$cc);
	$param_arr=_db_prepare_param($param_arr,"s","`bcc`",$bcc);
	$param_arr=_db_prepare_param($param_arr,"s","`subject`",$subject);
	$param_arr=_db_prepare_param($param_arr,"s","`message`",$message);
	$param_arr=_db_prepare_param($param_arr,"s","`status`",$status);
	$param_arr=_db_prepare_param($param_arr,"s","`headers`",$headers);
	$param_arr=_db_prepare_param($param_arr,"s","`emailer`",$emailer);
	$param_arr=_db_prepare_param($param_arr,"i","`shop_id`",SHOP_ID);

	$resp=execSQL("INSERT INTO 
						tEmailSent
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_emails_insert(): END");
	return $resp;

}



// SELECT
function db_lib_smsgateway_select() {

	LOG_MSG('INFO',"db_lib_smsgateway_select: START");

	$resp=execSQL("SELECT 
						smsgateway_id,
						name,
						website,
						gateway_url,
						username,
						password,
						api_key,
						default_sender_id,
						is_active
					FROM 
						tSMSGateway
					WHERE is_active=1 AND
						shop_id=".SHOP_ID."
					LIMIT 1"
					,array(), 
					false);

	LOG_MSG('INFO',"db_lib_smsgateway_select(): END");
	return $resp;
}

// INSERT
function db_smssent_insert(
							$smsgateway_id,
							$from,
							$to,
							$message,
							$url,
							$response,
							$status) {
	$param_arr=_init_db_params();

	LOG_MSG('INFO',"db_smsgateway_insert(): START { 
							smsgateway_id=[$smsgateway_id],
							from=[$from],
							to=[$to],
							message=[$message],
							url=[$url],
							response=[$response],
							status=[$status]	\n}");

	// Add params to params_arr
	$param_arr=_db_prepare_param($param_arr,"i","`smsgateway_id`",$smsgateway_id);
	$param_arr=_db_prepare_param($param_arr,"s","`from`",$from);
	$param_arr=_db_prepare_param($param_arr,"s","`to`",$to);
	$param_arr=_db_prepare_param($param_arr,"s","`message`",$message);
	$param_arr=_db_prepare_param($param_arr,"s","`url`",$url);
	$param_arr=_db_prepare_param($param_arr,"s","`response`",$response);
	$param_arr=_db_prepare_param($param_arr,"s","`status`",$status);
	$param_arr=_db_prepare_param($param_arr,"i","`shop_id`",SHOP_ID);

	$resp=execSQL("INSERT INTO 
						tSMSSent
							(".$param_arr['fields'].")
						VALUES
							(".$param_arr['placeholders'].")"
					,$param_arr['params'], 
					true);

	LOG_MSG('INFO',"db_smsgateway_insert(): END");
	return $resp;

}
?>