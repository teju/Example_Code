
<?php
	global $_SERVER;
	$_SERVER['SERVER_NAME']='localhost';

	include("../../tConf.php");
	include("../../lib/db.php");
	include("../../lib/utils.php");
	include("../../lib/users/db.php");
	include("../../lib/users/model.php");
	include("../../modules/utils/db.php");
	include("../../modules/utils/utils.php");


	/*include("../../modules/admin/type/db.php");
	include("../../modules/admin/product/db.php");
	include("../../modules/admin/product/tag/db.php");
	include("../../modules/admin/product/prodtag/db.php");
	include("../../modules/admin/inventory/db.php");*/

	define('CLI_MODE',true);
	db_connect();
	echo "Start Transaction...\n";
	db_transaction_start();
	insert_courier_zone_rates();

	//do_type_tags_update();
	//do_category_tags_update();
	//do_subcategory_tags_update();

	db_transaction_commit();
	db_close();

function wait() {
	echo "\nPress enter to continue: ";
	$fp = fopen("php://stdin","r");
	fgets($fp);
}


function insert_courier_zone_rates() {

	$MY_SHOP_ID=210;

	$filename="./courier_zone_rates.csv";
	$handle=fopen($filename, "r");
	if ( $handle === false ) {
		echo "Error operning file $filename\n";
		exit;
	}

	$row_no=0;
	$prev_courier_name='';
	while (($item = fgetcsv($handle, 10000, '|','"')) !== FALSE)	{
		$row_no++;
		if ($row_no == 1) continue; // Skip header
		//echo "\n".print_arr($item)."\n";
		//echo "$row_no:[".implode("|",$item)."]\n";

		$courier_name=trim(get_arg($item,0));
		$tax_rate=floatval(trim(get_arg($item,1)));
		$surcharges_rate=floatval(trim(get_arg($item,2)));
		$zone_name=trim(get_arg($item,3));
		$weight_from=floatval(trim(get_arg($item,4)));
		$weight_to=floatval(trim(get_arg($item,5)));
		$price=floatval(trim(get_arg($item,6)));

		echo "$row_no: COURIER[$courier_name] TAX_TATE[$tax_rate] SURCHARGES_RATE[$surcharges_rate] ZONE_NAME=[$zone_name] WEIGHT_FROM[$weight_from] WEIGHT_TO[$weight_to] PRICE[$price]\n";


		// UPDATE COURIER - tax_rate and surcharges_rate
		if ($prev_courier_name != $courier_name) {
			echo "    Inserting tCourier [$courier_name] ...:";
			$query="INSERT INTO 
						tCourier(name,tracking_link,tax_rate,surcharges_rate,is_active,shop_id)
					VALUES('$courier_name','',$tax_rate,$surcharges_rate,1,$MY_SHOP_ID)
					ON DUPLICATE KEY UPDATE
						courier_id = LAST_INSERT_ID(courier_id),
						tax_rate=$tax_rate, 
						surcharges_rate=$surcharges_rate";
			$resp=execSQL($query,array(),true);
			//echo $query."\n".print_arr($resp)."\n";
			if ( $resp['STATUS'] != 'OK' ) {
				echo "\nError updating Courier [$courier_name]".print_arr($resp);
				exit;
			}
			$courier_id=$resp['INSERT_ID'];
			echo " courier id [$courier_id]: ";
			echo "DONE\n";
			$prev_courier_name=$courier_name;
		}


		// INSER COURIER ZONE
		echo "    Inserting zone[$zone_name] courier_id[$courier_id] ...:";
		$resp=execSQL("
				INSERT INTO 
						tCourierZone (zone_name, courier_id, is_active, shop_id)
				VALUES ('$zone_name',$courier_id,1,$MY_SHOP_ID)
				ON DUPLICATE KEY update
					courierzone_id = LAST_INSERT_ID(courierzone_id)"
				,array(), 
				true);
		if ( $resp['STATUS'] != 'OK' ) {
			echo "\nError updating Inserting courier zone [$zone_name]".print_arr($resp);
			exit;
		}
		$courierzone_id=$resp['INSERT_ID'];
		echo " courierzone_id[$courierzone_id] : DONE\n";//.print_arr($resp);


		// INSERT courier rate zone
		
		echo "    Inserting WEIGHT_FROM[$weight_from] WEIGHT_TO[$weight_to] PRICE[$price] ...:";
		$resp=execSQL("
				INSERT INTO 
						tCourierZoneRate (courierzone_id,weight_from,weight_to,price,is_active,shop_id)
				VALUES ($courierzone_id,$weight_from,$weight_to,$price,1,$MY_SHOP_ID)
				ON DUPLICATE KEY update
					courierzonerate_id = LAST_INSERT_ID(courierzonerate_id)"
				,array(), 
				true);
		if ( $resp['STATUS'] != 'OK' ) {
			echo "\nError updating Inserting courier zone rate".print_arr($resp);
			exit;
		}
		echo " : DONE\n";//.print_arr($resp);

		//wait();
	}

}

                                                                     
                                                                     
                                                                     
                                             
function upload_states_cities() {

	$MY_SHOP_ID=35;

	$INDIA[0]=array('Port Blair','Andaman and Nicobar Islands');
	$INDIA[1]=array('Adilabad','Andhra Pradesh');
	$INDIA[2]=array('Adoni','Andhra Pradesh');
	$INDIA[3]=array('Amadalavalasa','Andhra Pradesh');
	$INDIA[4]=array('Amalapuram','Andhra Pradesh');
	$INDIA[5]=array('Anakapalle','Andhra Pradesh');
	$INDIA[6]=array('Anantapur','Andhra Pradesh');
	$INDIA[7]=array('Badepalle','Andhra Pradesh');
	$INDIA[8]=array('Banganapalle','Andhra Pradesh');
	$INDIA[9]=array('Bapatla','Andhra Pradesh');
	$INDIA[10]=array('Bellampalle','Andhra Pradesh');
	$INDIA[11]=array('Bethamcherla','Andhra Pradesh');
	$INDIA[12]=array('Bhadrachalam','Andhra Pradesh');
	$INDIA[13]=array('Bhainsa','Andhra Pradesh');
	$INDIA[14]=array('Bheemunipatnam','Andhra Pradesh');
	$INDIA[15]=array('Bhimavaram','Andhra Pradesh');
	$INDIA[16]=array('Bhongir','Andhra Pradesh');
	$INDIA[17]=array('Bobbili','Andhra Pradesh');
	$INDIA[18]=array('Bodhan','Andhra Pradesh');
	$INDIA[19]=array('Chapirevula','Andhra Pradesh');
	$INDIA[20]=array('Chilakaluripet','Andhra Pradesh');
	$INDIA[21]=array('Chirala','Andhra Pradesh');
	$INDIA[22]=array('Chittoor','Andhra Pradesh');
	$INDIA[23]=array('Cuddapah','Andhra Pradesh');
	$INDIA[24]=array('Devarakonda','Andhra Pradesh');
	$INDIA[25]=array('Dharmavaram','Andhra Pradesh');
	$INDIA[26]=array('Eluru','Andhra Pradesh');
	$INDIA[27]=array('Farooqnagar','Andhra Pradesh');
	$INDIA[28]=array('Gadwal','Andhra Pradesh');
	$INDIA[29]=array('Gooty','Andhra Pradesh');
	$INDIA[30]=array('Gudivada','Andhra Pradesh');
	$INDIA[31]=array('Gudur','Andhra Pradesh');
	$INDIA[32]=array('Guntakal','Andhra Pradesh');
	$INDIA[33]=array('Guntur','Andhra Pradesh');
	$INDIA[34]=array('Hanuman Junction','Andhra Pradesh');
	$INDIA[35]=array('Hindupur','Andhra Pradesh');
	$INDIA[36]=array('Hyderabad','Andhra Pradesh');
	$INDIA[37]=array('Ichchapuram','Andhra Pradesh');
	$INDIA[38]=array('Jaggaiahpet','Andhra Pradesh');
	$INDIA[39]=array('Jagtial','Andhra Pradesh');
	$INDIA[40]=array('Jammalamadugu','Andhra Pradesh');
	$INDIA[41]=array('Jangaon','Andhra Pradesh');
	$INDIA[42]=array('Kadapa','Andhra Pradesh');
	$INDIA[43]=array('Kadiri','Andhra Pradesh');
	$INDIA[44]=array('Kagaznagar','Andhra Pradesh');
	$INDIA[45]=array('Kakinada','Andhra Pradesh');
	$INDIA[46]=array('Kalyandurg','Andhra Pradesh');
	$INDIA[47]=array('Kamareddy','Andhra Pradesh');
	$INDIA[48]=array('Kandukur','Andhra Pradesh');
	$INDIA[49]=array('Karimnagar','Andhra Pradesh');
	$INDIA[50]=array('Kavali','Andhra Pradesh');
	$INDIA[51]=array('Khammam','Andhra Pradesh');
	$INDIA[52]=array('Kodad','Andhra Pradesh');
	$INDIA[53]=array('Koratla','Andhra Pradesh');
	$INDIA[54]=array('Kothagudem','Andhra Pradesh');
	$INDIA[55]=array('Kothapeta','Andhra Pradesh');
	$INDIA[56]=array('Kovvur','Andhra Pradesh');
	$INDIA[57]=array('Kurnool','Andhra Pradesh');
	$INDIA[58]=array('Kyathampalle','Andhra Pradesh');
	$INDIA[59]=array('Macherla','Andhra Pradesh');
	$INDIA[60]=array('Machilipatnam','Andhra Pradesh');
	$INDIA[61]=array('Madanapalle','Andhra Pradesh');
	$INDIA[62]=array('Mahbubnagar','Andhra Pradesh');
	$INDIA[63]=array('Mancherial','Andhra Pradesh');
	$INDIA[64]=array('Mandamarri','Andhra Pradesh');
	$INDIA[65]=array('Mandapeta','Andhra Pradesh');
	$INDIA[66]=array('Mangalagiri','Andhra Pradesh');
	$INDIA[67]=array('Manuguru','Andhra Pradesh');
	$INDIA[68]=array('Markapur','Andhra Pradesh');
	$INDIA[69]=array('Medak','Andhra Pradesh');
	$INDIA[70]=array('Miryalaguda','Andhra Pradesh');
	$INDIA[71]=array('Mogalthur','Andhra Pradesh');
	$INDIA[72]=array('Nagari','Andhra Pradesh');
	$INDIA[73]=array('Nagarkurnool','Andhra Pradesh');
	$INDIA[74]=array('Nandyal','Andhra Pradesh');
	$INDIA[75]=array('Narasapur','Andhra Pradesh');
	$INDIA[76]=array('Narasaraopet','Andhra Pradesh');
	$INDIA[77]=array('Narayanpet','Andhra Pradesh');
	$INDIA[78]=array('Narsipatnam','Andhra Pradesh');
	$INDIA[79]=array('Nellore','Andhra Pradesh');
	$INDIA[80]=array('Nidadavole','Andhra Pradesh');
	$INDIA[81]=array('Nirmal','Andhra Pradesh');
	$INDIA[82]=array('Nizamabad','Andhra Pradesh');
	$INDIA[83]=array('Nuzvid','Andhra Pradesh');
	$INDIA[84]=array('Ongole','Andhra Pradesh');
	$INDIA[85]=array('Palacole','Andhra Pradesh');
	$INDIA[86]=array('Palasa Kasibugga','Andhra Pradesh');
	$INDIA[87]=array('Pondur','Andhra Pradesh');
	$INDIA[88]=array('Palwancha','Andhra Pradesh');
	$INDIA[89]=array('Parvathipuram','Andhra Pradesh');
	$INDIA[90]=array('Pedana','Andhra Pradesh');
	$INDIA[91]=array('Peddapuram','Andhra Pradesh');
	$INDIA[92]=array('Pithapuram','Andhra Pradesh');
	$INDIA[93]=array('Ponnur','Andhra Pradesh');
	$INDIA[94]=array('Proddatur','Andhra Pradesh');
	$INDIA[95]=array('Punganur','Andhra Pradesh');
	$INDIA[96]=array('Puttur','Andhra Pradesh');
	$INDIA[97]=array('Rajahmundry','Andhra Pradesh');
	$INDIA[98]=array('Rajam','Andhra Pradesh');
	$INDIA[99]=array('Rajampet','Andhra Pradesh');
	$INDIA[100]=array('Ramachandrapuram','Andhra Pradesh');
	$INDIA[101]=array('Ramagundam','Andhra Pradesh');
	$INDIA[102]=array('Rayachoti','Andhra Pradesh');
	$INDIA[103]=array('Rayadurg','Andhra Pradesh');
	$INDIA[104]=array('Renigunta','Andhra Pradesh');
	$INDIA[105]=array('Repalle','Andhra Pradesh');
	$INDIA[106]=array('Sadasivpet','Andhra Pradesh');
	$INDIA[107]=array('Salur','Andhra Pradesh');
	$INDIA[108]=array('Samalkot','Andhra Pradesh');
	$INDIA[109]=array('Sangareddy','Andhra Pradesh');
	$INDIA[110]=array('Sattenapalle','Andhra Pradesh');
	$INDIA[111]=array('Siddipet','Andhra Pradesh');
	$INDIA[112]=array('Singapur','Andhra Pradesh');
	$INDIA[113]=array('Sircilla','Andhra Pradesh');
	$INDIA[114]=array('Srikakulam','Andhra Pradesh');
	$INDIA[115]=array('Srikalahasti','Andhra Pradesh');
	$INDIA[116]=array('Srisailam Project (Right Flank Colony) Township','Andhra Pradesh');
	$INDIA[117]=array('Suryapet','Andhra Pradesh');
	$INDIA[118]=array('Tadepalligudem','Andhra Pradesh');
	$INDIA[119]=array('Tadpatri','Andhra Pradesh');
	$INDIA[120]=array('Tandur','Andhra Pradesh');
	$INDIA[121]=array('Tanuku','Andhra Pradesh');
	$INDIA[122]=array('Tenali','Andhra Pradesh');
	$INDIA[123]=array('Tirupati','Andhra Pradesh');
	$INDIA[124]=array('Tiruvuru','Andhra Pradesh');
	$INDIA[125]=array('Tuni','Andhra Pradesh');
	$INDIA[126]=array('Uravakonda','Andhra Pradesh');
	$INDIA[127]=array('Venkatagiri','Andhra Pradesh');
	$INDIA[128]=array('Vicarabad','Andhra Pradesh');
	$INDIA[129]=array('Vijayawada','Andhra Pradesh');
	$INDIA[130]=array('Vinukonda','Andhra Pradesh');
	$INDIA[131]=array('Visakhapatnam','Andhra Pradesh');
	$INDIA[132]=array('Vizianagaram','Andhra Pradesh');
	$INDIA[133]=array('Wanaparthy','Andhra Pradesh');
	$INDIA[134]=array('Warangal','Andhra Pradesh');
	$INDIA[135]=array('Yellandu','Andhra Pradesh');
	$INDIA[136]=array('Yemmiganur','Andhra Pradesh');
	$INDIA[137]=array('Yerraguntla','Andhra Pradesh');
	$INDIA[138]=array('Zahirabad','Andhra Pradesh');
	$INDIA[139]=array('Along','Arunachal Pradesh');
	$INDIA[140]=array('Bomdila','Arunachal Pradesh');
	$INDIA[141]=array('Itanagar','Arunachal Pradesh');
	$INDIA[142]=array('Naharlagun','Arunachal Pradesh');
	$INDIA[143]=array('Pasighat','Arunachal Pradesh');
	$INDIA[144]=array('Abhayapuri','Assam');
	$INDIA[145]=array('Amguri','Assam');
	$INDIA[146]=array('Anandnagaar','Assam');
	$INDIA[147]=array('Barpeta','Assam');
	$INDIA[148]=array('Barpeta Road','Assam');
	$INDIA[149]=array('Bilasipara','Assam');
	$INDIA[150]=array('Bongaigaon','Assam');
	$INDIA[151]=array('Dhekiajuli','Assam');
	$INDIA[152]=array('Dhubri','Assam');
	$INDIA[153]=array('Dibrugarh','Assam');
	$INDIA[154]=array('Digboi','Assam');
	$INDIA[155]=array('Diphu','Assam');
	$INDIA[156]=array('Dispur','Assam');
	$INDIA[157]=array('Duliajan Oil Town','Assam');
	$INDIA[158]=array('Gauripur','Assam');
	$INDIA[159]=array('Goalpara','Assam');
	$INDIA[160]=array('Golaghat','Assam');
	$INDIA[161]=array('Guwahati','Assam');
	$INDIA[162]=array('Haflong','Assam');
	$INDIA[163]=array('Hailakandi','Assam');
	$INDIA[164]=array('Hojai','Assam');
	$INDIA[165]=array('Jorhat','Assam');
	$INDIA[166]=array('Karimganj','Assam');
	$INDIA[167]=array('Kokrajhar','Assam');
	$INDIA[168]=array('Lanka','Assam');
	$INDIA[169]=array('Lumding','Assam');
	$INDIA[170]=array('Mangaldoi','Assam');
	$INDIA[171]=array('Mankachar','Assam');
	$INDIA[172]=array('Margherita','Assam');
	$INDIA[173]=array('Mariani','Assam');
	$INDIA[174]=array('Marigaon','Assam');
	$INDIA[175]=array('Nagaon','Assam');
	$INDIA[176]=array('Nalbari','Assam');
	$INDIA[177]=array('North Lakhimpur','Assam');
	$INDIA[178]=array('Rangia','Assam');
	$INDIA[179]=array('Sibsagar','Assam');
	$INDIA[180]=array('Silapathar','Assam');
	$INDIA[181]=array('Silchar','Assam');
	$INDIA[182]=array('Tezpur','Assam');
	$INDIA[183]=array('Tinsukia','Assam');
	$INDIA[184]=array('Amarpur','Bihar');
	$INDIA[185]=array('Araria','Bihar');
	$INDIA[186]=array('Areraj','Bihar');
	$INDIA[187]=array('Arrah','Bihar');
	$INDIA[188]=array('Arwal','Bihar');
	$INDIA[189]=array('Asarganj','Bihar');
	$INDIA[190]=array('Aurangabad','Bihar');
	$INDIA[191]=array('Bagaha','Bihar');
	$INDIA[192]=array('Bahadurganj','Bihar');
	$INDIA[193]=array('Bairgania','Bihar');
	$INDIA[194]=array('Bakhtiarpur','Bihar');
	$INDIA[195]=array('Banka','Bihar');
	$INDIA[196]=array('Banmankhi Bazar','Bihar');
	$INDIA[197]=array('Barahiya','Bihar');
	$INDIA[198]=array('Barauli','Bihar');
	$INDIA[199]=array('Barbigha','Bihar');
	$INDIA[200]=array('Barh','Bihar');
	$INDIA[201]=array('Begusarai','Bihar');
	$INDIA[202]=array('Behea','Bihar');
	$INDIA[203]=array('Bettiah','Bihar');
	$INDIA[204]=array('Bhabua','Bihar');
	$INDIA[205]=array('Bhagalpur','Bihar');
	$INDIA[206]=array('Bihar Sharif','Bihar');
	$INDIA[207]=array('Bikramganj','Bihar');
	$INDIA[208]=array('Bodh Gaya','Bihar');
	$INDIA[209]=array('Buxar','Bihar');
	$INDIA[210]=array('Chanpatia','Bihar');
	$INDIA[211]=array('Chhapra','Bihar');
	$INDIA[212]=array('Colgong','Bihar');
	$INDIA[213]=array('Chandan Bara','Bihar');
	$INDIA[214]=array('Dalsinghsarai','Bihar');
	$INDIA[215]=array('Darbhanga','Bihar');
	$INDIA[216]=array('Daudnagar','Bihar');
	$INDIA[217]=array('Dehri-on-Sone','Bihar');
	$INDIA[218]=array('Dighwara','Bihar');
	$INDIA[219]=array('Dumraon','Bihar');
	$INDIA[220]=array('Fatwah','Bihar');
	$INDIA[221]=array('Forbesganj','Bihar');
	$INDIA[222]=array('Gaya','Bihar');
	$INDIA[223]=array('Gogri Jamalpur','Bihar');
	$INDIA[224]=array('Gopalganj','Bihar');
	$INDIA[225]=array('Hajipur','Bihar');
	$INDIA[226]=array('Hilsa','Bihar');
	$INDIA[227]=array('Hisua','Bihar');
	$INDIA[228]=array('Islampur','Bihar');
	$INDIA[229]=array('Jagdispur','Bihar');
	$INDIA[230]=array('Jamalpur','Bihar');
	$INDIA[231]=array('Jamui','Bihar');
	$INDIA[232]=array('Jehanabad','Bihar');
	$INDIA[233]=array('Jhajha','Bihar');
	$INDIA[234]=array('Jhanjharpur','Bihar');
	$INDIA[235]=array('Jogabani','Bihar');
	$INDIA[236]=array('Kanti','Bihar');
	$INDIA[237]=array('Katihar','Bihar');
	$INDIA[238]=array('Khagaria','Bihar');
	$INDIA[239]=array('Kharagpur','Bihar');
	$INDIA[240]=array('Kishanganj','Bihar');
	$INDIA[241]=array('Lakhisarai','Bihar');
	$INDIA[242]=array('Lalganj','Bihar');
	$INDIA[243]=array('Madhepura','Bihar');
	$INDIA[244]=array('Madhubani','Bihar');
	$INDIA[245]=array('Maharajganj','Bihar');
	$INDIA[246]=array('Mahnar Bazar','Bihar');
	$INDIA[247]=array('Makhdumpur','Bihar');
	$INDIA[248]=array('Maner','Bihar');
	$INDIA[249]=array('Manihari','Bihar');
	$INDIA[250]=array('Marhaura','Bihar');
	$INDIA[251]=array('Masaurhi','Bihar');
	$INDIA[252]=array('Mirganj','Bihar');
	$INDIA[253]=array('Mohania','Bihar');
	$INDIA[254]=array('Mokama','Bihar');
	$INDIA[255]=array('Mokameh','Bihar');
	$INDIA[256]=array('Motihari','Bihar');
	$INDIA[257]=array('Motipur','Bihar');
	$INDIA[258]=array('Munger','Bihar');
	$INDIA[259]=array('Murliganj','Bihar');
	$INDIA[260]=array('Muzaffarpur','Bihar');
	$INDIA[261]=array('Nalanda','Bihar');
	$INDIA[262]=array('Narkatiaganj','Bihar');
	$INDIA[263]=array('Naugachhia','Bihar');
	$INDIA[264]=array('Nawada','Bihar');
	$INDIA[265]=array('Nokha','Bihar');
	$INDIA[266]=array('Patna','Bihar');
	$INDIA[267]=array('Piro','Bihar');
	$INDIA[268]=array('Purnia','Bihar');
	$INDIA[269]=array('Rafiganj','Bihar');
	$INDIA[270]=array('Rajgir','Bihar');
	$INDIA[271]=array('Ramnagar','Bihar');
	$INDIA[272]=array('Raxaul Bazar','Bihar');
	$INDIA[273]=array('Revelganj','Bihar');
	$INDIA[274]=array('Rosera','Bihar');
	$INDIA[275]=array('Saharsa','Bihar');
	$INDIA[276]=array('Samastipur','Bihar');
	$INDIA[277]=array('Sasaram','Bihar');
	$INDIA[278]=array('Sheikhpura','Bihar');
	$INDIA[279]=array('Sheohar','Bihar');
	$INDIA[280]=array('Sherghati','Bihar');
	$INDIA[281]=array('Silao','Bihar');
	$INDIA[282]=array('Sitamarhi','Bihar');
	$INDIA[283]=array('Siwan','Bihar');
	$INDIA[284]=array('Sonepur','Bihar');
	$INDIA[285]=array('Sugauli','Bihar');
	$INDIA[286]=array('Sultanganj','Bihar');
	$INDIA[287]=array('Supaul','Bihar');
	$INDIA[288]=array('Warisaliganj','Bihar');
	$INDIA[289]=array('Chandigarh','Chandigarh');
	$INDIA[290]=array('Ahiwara','Chhattisgarh');
	$INDIA[291]=array('Akaltara','Chhattisgarh');
	$INDIA[292]=array('Ambagarh Chowki','Chhattisgarh');
	$INDIA[293]=array('Ambikapur','Chhattisgarh');
	$INDIA[294]=array('Arang','Chhattisgarh');
	$INDIA[295]=array('Bade Bacheli','Chhattisgarh');
	$INDIA[296]=array('Balod','Chhattisgarh');
	$INDIA[297]=array('Baloda Bazar','Chhattisgarh');
	$INDIA[298]=array('Basna','Chhattisgarh');
	$INDIA[299]=array('Bemetra','Chhattisgarh');
	$INDIA[300]=array('Bhatapara','Chhattisgarh');
	$INDIA[301]=array('Bhilai','Chhattisgarh');
	$INDIA[302]=array('Bilaspur','Chhattisgarh');
	$INDIA[303]=array('Birgaon','Chhattisgarh');
	$INDIA[304]=array('Champa','Chhattisgarh');
	$INDIA[305]=array('Chirmiri','Chhattisgarh');
	$INDIA[306]=array('Dalli-Rajhara','Chhattisgarh');
	$INDIA[307]=array('Dhamtari','Chhattisgarh');
	$INDIA[308]=array('Dipka','Chhattisgarh');
	$INDIA[309]=array('Dongargarh','Chhattisgarh');
	$INDIA[310]=array('Durg-Bhilai Nagar','Chhattisgarh');
	$INDIA[311]=array('Gobranawapara','Chhattisgarh');
	$INDIA[312]=array('Jagdalpur','Chhattisgarh');
	$INDIA[313]=array('Janjgir','Chhattisgarh');
	$INDIA[314]=array('Jashpurnagar','Chhattisgarh');
	$INDIA[315]=array('Kanker','Chhattisgarh');
	$INDIA[316]=array('Kawardha','Chhattisgarh');
	$INDIA[317]=array('Kondagaon','Chhattisgarh');
	$INDIA[318]=array('Korba','Chhattisgarh');
	$INDIA[319]=array('Mahasamund','Chhattisgarh');
	$INDIA[320]=array('Mahendragarh','Chhattisgarh');
	$INDIA[321]=array('Mungeli','Chhattisgarh');
	$INDIA[322]=array('Naila Janjgir','Chhattisgarh');
	$INDIA[323]=array('Raigarh','Chhattisgarh');
	$INDIA[324]=array('Raipur','Chhattisgarh');
	$INDIA[325]=array('Rajnandgaon','Chhattisgarh');
	$INDIA[326]=array('Sakti','Chhattisgarh');
	$INDIA[327]=array('Tilda Newra','Chhattisgarh');
	$INDIA[328]=array('Amli','Dadra and Nagar Haveli');
	$INDIA[329]=array('Silvassa','Dadra and Nagar Haveli');
	$INDIA[330]=array('Daman and Diu','Daman and Diu');
	$INDIA[331]=array('Asola','Delhi');
	$INDIA[332]=array('Bhajanpura','Delhi');
	$INDIA[333]=array('Delhi','Delhi');
	$INDIA[334]=array('New Delhi','Delhi');
	$INDIA[335]=array('Aldona','Goa');
	$INDIA[336]=array('Curchorem Cacora','Goa');
	$INDIA[337]=array('Goa Velha','Goa');
	$INDIA[338]=array('Madgaon','Goa');
	$INDIA[339]=array('Mapusa','Goa');
	$INDIA[340]=array('Margao','Goa');
	$INDIA[341]=array('Marmagao','Goa');
	$INDIA[342]=array('Panaji','Goa');
	$INDIA[343]=array('Adalaj','Gujarat');
	$INDIA[344]=array('Adityana','Gujarat');
	$INDIA[345]=array('Ahmedabad','Gujarat');
	$INDIA[346]=array('Alang','Gujarat');
	$INDIA[347]=array('Ambaji','Gujarat');
	$INDIA[348]=array('Ambaliyasan','Gujarat');
	$INDIA[349]=array('Amreli','Gujarat');
	$INDIA[350]=array('Anand','Gujarat');
	$INDIA[351]=array('Andada','Gujarat');
	$INDIA[352]=array('Anjar','Gujarat');
	$INDIA[353]=array('Anklav','Gujarat');
	$INDIA[354]=array('Ankleshwar','Gujarat');
	$INDIA[355]=array('Antaliya','Gujarat');
	$INDIA[356]=array('Arambhada','Gujarat');
	$INDIA[357]=array('Atul','Gujarat');
	$INDIA[358]=array('Bharuch','Gujarat');
	$INDIA[359]=array('Bhavnagar','Gujarat');
	$INDIA[360]=array('Bhuj','Gujarat');
	$INDIA[361]=array('Cambay','Gujarat');
	$INDIA[362]=array('Dahod','Gujarat');
	$INDIA[363]=array('Deesa','Gujarat');
	$INDIA[364]=array('Dehgam','Gujarat');
	$INDIA[365]=array('Dharampur','Gujarat');
	$INDIA[366]=array('Dholka','Gujarat');
	$INDIA[367]=array('Dwarka','Gujarat');
	$INDIA[368]=array('Gandhidham','Gujarat');
	$INDIA[369]=array('Gandhinagar','Gujarat');
	$INDIA[370]=array('Godhra','Gujarat');
	$INDIA[371]=array('Himatnagar','Gujarat');
	$INDIA[372]=array('Idar','Gujarat');
	$INDIA[373]=array('Jamnagar','Gujarat');
	$INDIA[374]=array('Junagadh','Gujarat');
	$INDIA[375]=array('Kadi','Gujarat');
	$INDIA[376]=array('Kalavad','Gujarat');
	$INDIA[377]=array('Kalol','Gujarat');
	$INDIA[378]=array('Kapadvanj','Gujarat');
	$INDIA[379]=array('Karjan','Gujarat');
	$INDIA[380]=array('Keshod','Gujarat');
	$INDIA[381]=array('Khambhalia','Gujarat');
	$INDIA[382]=array('Khambhat','Gujarat');
	$INDIA[383]=array('Kheda','Gujarat');
	$INDIA[384]=array('Khedbrahma','Gujarat');
	$INDIA[385]=array('Kheralu','Gujarat');
	$INDIA[386]=array('Kodinar','Gujarat');
	$INDIA[387]=array('Lathi','Gujarat');
	$INDIA[388]=array('Limbdi','Gujarat');
	$INDIA[389]=array('Lunawada','Gujarat');
	$INDIA[390]=array('Mahesana','Gujarat');
	$INDIA[391]=array('Mahuva','Gujarat');
	$INDIA[392]=array('Manavadar','Gujarat');
	$INDIA[393]=array('Mandvi','Gujarat');
	$INDIA[394]=array('Mangrol','Gujarat');
	$INDIA[395]=array('Mansa','Gujarat');
	$INDIA[396]=array('Mehmedabad','Gujarat');
	$INDIA[397]=array('Mithapur','Gujarat');
	$INDIA[398]=array('Modasa','Gujarat');
	$INDIA[399]=array('Morvi','Gujarat');
	$INDIA[400]=array('Nadiad','Gujarat');
	$INDIA[401]=array('Navsari','Gujarat');
	$INDIA[402]=array('Padra','Gujarat');
	$INDIA[403]=array('Palanpur','Gujarat');
	$INDIA[404]=array('Palitana','Gujarat');
	$INDIA[405]=array('Pardi','Gujarat');
	$INDIA[406]=array('Patan','Gujarat');
	$INDIA[407]=array('Petlad','Gujarat');
	$INDIA[408]=array('Porbandar','Gujarat');
	$INDIA[409]=array('Radhanpur','Gujarat');
	$INDIA[410]=array('Rajkot','Gujarat');
	$INDIA[411]=array('Rajpipla','Gujarat');
	$INDIA[412]=array('Rajula','Gujarat');
	$INDIA[413]=array('Ranavav','Gujarat');
	$INDIA[414]=array('Rapar','Gujarat');
	$INDIA[415]=array('Salaya','Gujarat');
	$INDIA[416]=array('Sanand','Gujarat');
	$INDIA[417]=array('Savarkundla','Gujarat');
	$INDIA[418]=array('Sidhpur','Gujarat');
	$INDIA[419]=array('Sihor','Gujarat');
	$INDIA[420]=array('Songadh','Gujarat');
	$INDIA[421]=array('Surat','Gujarat');
	$INDIA[422]=array('Talaja','Gujarat');
	$INDIA[423]=array('Thangadh','Gujarat');
	$INDIA[424]=array('Tharad','Gujarat');
	$INDIA[425]=array('Umbergaon','Gujarat');
	$INDIA[426]=array('Umreth','Gujarat');
	$INDIA[427]=array('Una','Gujarat');
	$INDIA[428]=array('Unjha','Gujarat');
	$INDIA[429]=array('Upleta','Gujarat');
	$INDIA[430]=array('Vadnagar','Gujarat');
	$INDIA[431]=array('Vadodara','Gujarat');
	$INDIA[432]=array('Valsad','Gujarat');
	$INDIA[433]=array('Vapi','Gujarat');
	$INDIA[435]=array('Veraval','Gujarat');
	$INDIA[436]=array('Vijapur','Gujarat');
	$INDIA[437]=array('Viramgam','Gujarat');
	$INDIA[438]=array('Visnagar','Gujarat');
	$INDIA[439]=array('Vyara','Gujarat');
	$INDIA[440]=array('Wadhwan','Gujarat');
	$INDIA[441]=array('Wankaner','Gujarat');
	$INDIA[442]=array('Asankhurd','Haryana');
	$INDIA[443]=array('Assandh','Haryana');
	$INDIA[444]=array('Ateli','Haryana');
	$INDIA[445]=array('Babiyal','Haryana');
	$INDIA[446]=array('Bahadurgarh','Haryana');
	$INDIA[447]=array('Ballabhgarh','Haryana');
	$INDIA[448]=array('Barwala','Haryana');
	$INDIA[449]=array('Bawal','Haryana');
	$INDIA[450]=array('Bhiwani','Haryana');
	$INDIA[451]=array('Charkhi Dadri','Haryana');
	$INDIA[452]=array('Cheeka','Haryana');
	$INDIA[453]=array('Ellenabad','Haryana');
	$INDIA[454]=array('Faridabad','Haryana');
	$INDIA[455]=array('Fatehabad','Haryana');
	$INDIA[456]=array('Ganaur','Haryana');
	$INDIA[457]=array('Gharaunda','Haryana');
	$INDIA[458]=array('Gohana','Haryana');
	$INDIA[459]=array('Gurgaon','Haryana');
	$INDIA[460]=array('Haibat(Yamuna Nagar)','Haryana');
	$INDIA[461]=array('Hansi','Haryana');
	$INDIA[462]=array('Hisar','Haryana');
	$INDIA[463]=array('Hodal','Haryana');
	$INDIA[464]=array('Jagadhri','Haryana');
	$INDIA[465]=array('Jhajjar','Haryana');
	$INDIA[466]=array('Jind','Haryana');
	$INDIA[467]=array('Kaithal','Haryana');
	$INDIA[468]=array('Kalan Wali','Haryana');
	$INDIA[469]=array('Kalka','Haryana');
	$INDIA[470]=array('Karnal','Haryana');
	$INDIA[471]=array('Kurukshetra','Haryana');
	$INDIA[472]=array('Ladwa','Haryana');
	$INDIA[473]=array('Mahendragarh','Haryana');
	$INDIA[474]=array('Mandi Dabwali','Haryana');
	$INDIA[475]=array('Manesar','Haryana');
	$INDIA[476]=array('Narnaul','Haryana');
	$INDIA[477]=array('Narwana','Haryana');
	$INDIA[478]=array('Palwal','Haryana');
	$INDIA[479]=array('Panchkula','Haryana');
	$INDIA[480]=array('Panipat','Haryana');
	$INDIA[481]=array('Pehowa','Haryana');
	$INDIA[482]=array('Pinjore','Haryana');
	$INDIA[483]=array('Rania','Haryana');
	$INDIA[484]=array('Ratia','Haryana');
	$INDIA[485]=array('Rewari','Haryana');
	$INDIA[486]=array('Rohtak','Haryana');
	$INDIA[487]=array('Safidon','Haryana');
	$INDIA[488]=array('Samalkha','Haryana');
	$INDIA[489]=array('Shahbad','Haryana');
	$INDIA[490]=array('Sirsa','Haryana');
	$INDIA[491]=array('Sohna','Haryana');
	$INDIA[492]=array('Sonipat','Haryana');
	$INDIA[493]=array('Taraori','Haryana');
	$INDIA[494]=array('Thanesar','Haryana');
	$INDIA[495]=array('Tohana','Haryana');
	$INDIA[496]=array('Yamunanagar','Haryana');
	$INDIA[497]=array('Arki','Himachal Pradesh');
	$INDIA[498]=array('Baddi','Himachal Pradesh');
	$INDIA[499]=array('Bilaspur','Himachal Pradesh');
	$INDIA[500]=array('Chamba','Himachal Pradesh');
	$INDIA[501]=array('Dalhousie','Himachal Pradesh');
	$INDIA[502]=array('Dharamsala','Himachal Pradesh');
	$INDIA[503]=array('Hamirpur','Himachal Pradesh');
	$INDIA[504]=array('Keylong','Himachal Pradesh');
	$INDIA[505]=array('Mandi','Himachal Pradesh');
	$INDIA[506]=array('Nahan','Himachal Pradesh');
	$INDIA[507]=array('Shimla','Himachal Pradesh');
	$INDIA[508]=array('Solan','Himachal Pradesh');
	$INDIA[509]=array('Sundarnagar','Himachal Pradesh');
	$INDIA[510]=array('Katra','Jammu & Kashmir');
	$INDIA[511]=array('Achabbal','Jammu and Kashmir');
	$INDIA[512]=array('Akhnoor','Jammu and Kashmir');
	$INDIA[513]=array('Anantnag','Jammu and Kashmir');
	$INDIA[514]=array('Arnia','Jammu and Kashmir');
	$INDIA[515]=array('Awantipora','Jammu and Kashmir');
	$INDIA[516]=array('Bandipore','Jammu and Kashmir');
	$INDIA[517]=array('Baramula','Jammu and Kashmir');
	$INDIA[518]=array('Jammu','Jammu and Kashmir');
	$INDIA[519]=array('Kathua','Jammu and Kashmir');
	$INDIA[520]=array('Leh','Jammu and Kashmir');
	$INDIA[521]=array('Punch','Jammu and Kashmir');
	$INDIA[522]=array('Rajauri','Jammu and Kashmir');
	$INDIA[523]=array('Sopore','Jammu and Kashmir');
	$INDIA[524]=array('Srinagar','Jammu and Kashmir');
	$INDIA[525]=array('Udhampur','Jammu and Kashmir');
	$INDIA[526]=array('Amlabad','Jharkhand');
	$INDIA[527]=array('Ara','Jharkhand');
	$INDIA[528]=array('Barughutu','Jharkhand');
	$INDIA[529]=array('Bokaro Steel City','Jharkhand');
	$INDIA[530]=array('Chaibasa','Jharkhand');
	$INDIA[531]=array('Chakradharpur','Jharkhand');
	$INDIA[532]=array('Chandil','Jharkhand');
	$INDIA[533]=array('Chandrapura','Jharkhand');
	$INDIA[534]=array('Chatra','Jharkhand');
	$INDIA[535]=array('Chirkunda','Jharkhand');
	$INDIA[536]=array('Churi','Jharkhand');
	$INDIA[537]=array('Daltonganj','Jharkhand');
	$INDIA[538]=array('Deoghar','Jharkhand');
	$INDIA[539]=array('Dhanbad','Jharkhand');
	$INDIA[540]=array('Dumka','Jharkhand');
	$INDIA[541]=array('Garhwa','Jharkhand');
	$INDIA[542]=array('Ghatshila','Jharkhand');
	$INDIA[543]=array('Giridih','Jharkhand');
	$INDIA[544]=array('Godda','Jharkhand');
	$INDIA[545]=array('Gomoh','Jharkhand');
	$INDIA[546]=array('Gumia','Jharkhand');
	$INDIA[547]=array('Gumla','Jharkhand');
	$INDIA[548]=array('Hazaribag','Jharkhand');
	$INDIA[549]=array('Hussainabad','Jharkhand');
	$INDIA[550]=array('Jamshedpur','Jharkhand');
	$INDIA[551]=array('Jamtara','Jharkhand');
	$INDIA[552]=array('Jhumri Tilaiya','Jharkhand');
	$INDIA[553]=array('Khunti','Jharkhand');
	$INDIA[554]=array('Lohardaga','Jharkhand');
	$INDIA[555]=array('Madhupur','Jharkhand');
	$INDIA[556]=array('Mihijam','Jharkhand');
	$INDIA[557]=array('Musabani','Jharkhand');
	$INDIA[558]=array('Pakaur','Jharkhand');
	$INDIA[559]=array('Patratu','Jharkhand');
	$INDIA[560]=array('Phusro','Jharkhand');
	$INDIA[561]=array('Ramngarh','Jharkhand');
	$INDIA[562]=array('Ranchi','Jharkhand');
	$INDIA[563]=array('Sahibganj','Jharkhand');
	$INDIA[564]=array('Saunda','Jharkhand');
	$INDIA[565]=array('Simdega','Jharkhand');
	$INDIA[566]=array('Tenu Dam-cum- Kathhara','Jharkhand');
	$INDIA[567]=array('Pereyaapatna','Karnataka');
	$INDIA[568]=array('Adyar','Karnataka');
	$INDIA[569]=array('Afzalpura','Karnataka');
	$INDIA[570]=array('Alandha','Karnataka');
	$INDIA[571]=array('Aalanavara','Karnataka');
	$INDIA[572]=array('Alur','Karnataka');
	$INDIA[573]=array('Ambikaanagara','Karnataka');
	$INDIA[574]=array('Anekal','Karnataka');
	$INDIA[575]=array('Ankola','Karnataka');
	$INDIA[576]=array('Annigeri','Karnataka');
	$INDIA[577]=array('Arsikere','Karnataka');
	$INDIA[578]=array('Arkalgud','Karnataka');
	$INDIA[579]=array('Athni','Karnataka');
	$INDIA[580]=array('Aurad','Karnataka');
	$INDIA[581]=array('Bangalore','Karnataka');
	$INDIA[582]=array('Belgaum','Karnataka');
	$INDIA[583]=array('Ballary','Karnataka');
	$INDIA[584]=array('Bengaluru','Karnataka');
	$INDIA[585]=array('Bidar','Karnataka');
	$INDIA[586]=array('Chamarajanagara','Karnataka');
	$INDIA[587]=array('Chikkodi','Karnataka');
	$INDIA[588]=array('Chikkamagalur','Karnataka');
	$INDIA[589]=array('Chinthaamani','Karnataka');
	$INDIA[590]=array('Chitradurga','Karnataka');
	$INDIA[591]=array('Chikkaballapura','Karnataka');
	$INDIA[592]=array('Davanagere','Karnataka');
	$INDIA[593]=array('Dharwad','Karnataka');
	$INDIA[594]=array('Gadhaga','Karnataka');
	$INDIA[595]=array('Gokak','Karnataka');
	$INDIA[596]=array('Gulbarga','Karnataka');
	$INDIA[597]=array('Gundlupet','Karnataka');
	$INDIA[598]=array('Haasana','Karnataka');
	$INDIA[599]=array('Hosapet','Karnataka');
	$INDIA[600]=array('Hubbali','Karnataka');
	$INDIA[601]=array('Hukkeri','Karnataka');
	$INDIA[602]=array('Kalburgi','Karnataka');
	$INDIA[603]=array('Kaarkala','Karnataka');
	$INDIA[604]=array('Karwar','Karnataka');
	$INDIA[605]=array('Kolaara','Karnataka');
	$INDIA[606]=array('Kota','Karnataka');
	$INDIA[607]=array('Lakshmishawara','Karnataka');
	$INDIA[608]=array('Lingsuguru','Karnataka');
	$INDIA[609]=array('Maddhuru','Karnataka');
	$INDIA[610]=array('Madhugiri','Karnataka');
	$INDIA[611]=array('Madikeri','Karnataka');
	$INDIA[612]=array('Maagadi','Karnataka');
	$INDIA[613]=array('Mahalingapura','Karnataka');
	$INDIA[614]=array('Malavalli','Karnataka');
	$INDIA[615]=array('Maaluru','Karnataka');
	$INDIA[616]=array('Mandya','Karnataka');
	$INDIA[617]=array('Mangalooru','Karnataka');
	$INDIA[618]=array('Maanvi','Karnataka');
	$INDIA[619]=array('Mudalagi','Karnataka');
	$INDIA[620]=array('Mudabidri','Karnataka');
	$INDIA[621]=array('Muddebihala','Karnataka');
	$INDIA[622]=array('Mudhola','Karnataka');
	$INDIA[623]=array('Mulabaagilu','Karnataka');
	$INDIA[624]=array('Mundaragi','Karnataka');
	$INDIA[625]=array('Mysore','Karnataka');
	$INDIA[626]=array('Nanjanagoodu','Karnataka');
	$INDIA[627]=array('Nippani','Karnataka');
	$INDIA[628]=array('Paavagada','Karnataka');
	$INDIA[629]=array('Puthooru','Karnataka');
	$INDIA[630]=array('Rabakavi Banahatti','Karnataka');
	$INDIA[631]=array('Raayachuru','Karnataka');
	$INDIA[632]=array('Raamanagara','Karnataka');
	$INDIA[633]=array('Raamadurga','Karnataka');
	$INDIA[634]=array('Ranibennur','Karnataka');
	$INDIA[635]=array('Robertson Pet','Karnataka');
	$INDIA[636]=array('Ron','Karnataka');
	$INDIA[637]=array('Sadalaga','Karnataka');
	$INDIA[638]=array('Sagara','Karnataka');
	$INDIA[639]=array('Sakaleshapura','Karnataka');
	$INDIA[640]=array('Sandur','Karnataka');
	$INDIA[641]=array('Sankeshwara','Karnataka');
	$INDIA[642]=array('Soudaththi-Yellamma','Karnataka');
	$INDIA[643]=array('Savanur','Karnataka');
	$INDIA[644]=array('Sedam','Karnataka');
	$INDIA[645]=array('Shahabad','Karnataka');
	$INDIA[646]=array('Shahapura','Karnataka');
	$INDIA[647]=array('Shiggaavi','Karnataka');
	$INDIA[648]=array('Shikapur','Karnataka');
	$INDIA[649]=array('Shivamogga','Karnataka');
	$INDIA[650]=array('Surapura','Karnataka');
	$INDIA[651]=array('Shree Rangapattana','Karnataka');
	$INDIA[652]=array('Sidhalaghatta','Karnataka');
	$INDIA[653]=array('Sindhagi','Karnataka');
	$INDIA[654]=array('Sindhanooru','Karnataka');
	$INDIA[655]=array('Sira','Karnataka');
	$INDIA[656]=array('Sirsi','Karnataka');
	$INDIA[657]=array('Sheraguppa','Karnataka');
	$INDIA[658]=array('Shreenivaasapura','Karnataka');
	$INDIA[659]=array('Thaalikote','Karnataka');
	$INDIA[660]=array('Tarikere','Karnataka');
	$INDIA[661]=array('Tekkalakote','Karnataka');
	$INDIA[662]=array('Thergallu','Karnataka');
	$INDIA[663]=array('Thipatooru','Karnataka');
	$INDIA[664]=array('Thumakooru','Karnataka');
	$INDIA[665]=array('Udupi','Karnataka');
	$INDIA[666]=array('Ugar','Karnataka');
	$INDIA[667]=array('Vijayapura','Karnataka');
	$INDIA[668]=array('Wadi','Karnataka');
	$INDIA[669]=array('Yaadhagiri','Karnataka');
	$INDIA[670]=array('Adoor','Kerala');
	$INDIA[671]=array('Akathiyoor','Kerala');
	$INDIA[672]=array('Alappuzha','Kerala');
	$INDIA[673]=array('Ancharakandy','Kerala');
	$INDIA[674]=array('Aroor','Kerala');
	$INDIA[675]=array('Ashtamichira','Kerala');
	$INDIA[676]=array('Attingal','Kerala');
	$INDIA[677]=array('Avinissery','Kerala');
	$INDIA[678]=array('Chalakudy','Kerala');
	$INDIA[679]=array('Changanassery','Kerala');
	$INDIA[680]=array('Chendamangalam','Kerala');
	$INDIA[681]=array('Chengannur','Kerala');
	$INDIA[682]=array('Cherthala','Kerala');
	$INDIA[683]=array('Cheruthazham','Kerala');
	$INDIA[684]=array('Chittur-Thathamangalam','Kerala');
	$INDIA[685]=array('Chockli','Kerala');
	$INDIA[686]=array('Erattupetta','Kerala');
	$INDIA[687]=array('Guruvayoor','Kerala');
	$INDIA[688]=array('Irinjalakuda','Kerala');
	$INDIA[689]=array('Idukki','Kerala');
	$INDIA[690]=array('Kadirur','Kerala');
	$INDIA[691]=array('Kalliasseri','Kerala');
	$INDIA[692]=array('Kalpetta','Kerala');
	$INDIA[693]=array('Kanhangad','Kerala');
	$INDIA[694]=array('Kanjikkuzhi','Kerala');
	$INDIA[695]=array('Kannur','Kerala');
	$INDIA[696]=array('Kasaragod','Kerala');
	$INDIA[697]=array('Kayamkulam','Kerala');
	$INDIA[698]=array('Kochi','Kerala');
	$INDIA[699]=array('Kodungallur','Kerala');
	$INDIA[700]=array('Kollam','Kerala');
	$INDIA[701]=array('Koothuparamba','Kerala');
	$INDIA[702]=array('Kothamangalam','Kerala');
	$INDIA[703]=array('Kottayam','Kerala');
	$INDIA[704]=array('Kozhikode','Kerala');
	$INDIA[705]=array('Kunnamkulam','Kerala');
	$INDIA[706]=array('Malappuram','Kerala');
	$INDIA[707]=array('Mattannur','Kerala');
	$INDIA[708]=array('Mavelikkara','Kerala');
	$INDIA[709]=array('Mavoor','Kerala');
	$INDIA[710]=array('Muvattupuzha','Kerala');
	$INDIA[711]=array('Nedumangad','Kerala');
	$INDIA[712]=array('Neyyattinkara','Kerala');
	$INDIA[713]=array('Nilambur','Kerala');
	$INDIA[714]=array('Ottappalam','Kerala');
	$INDIA[715]=array('Palai','Kerala');
	$INDIA[716]=array('Palakkad','Kerala');
	$INDIA[717]=array('Panamattom','Kerala');
	$INDIA[718]=array('Panniyannur','Kerala');
	$INDIA[719]=array('Pappinisseri','Kerala');
	$INDIA[720]=array('Paravoor','Kerala');
	$INDIA[721]=array('Pathanamthitta','Kerala');
	$INDIA[722]=array('Peringathur','Kerala');
	$INDIA[723]=array('Perinthalmanna','Kerala');
	$INDIA[724]=array('Perumbavoor','Kerala');
	$INDIA[725]=array('Ponkunnam','Kerala');
	$INDIA[726]=array('Ponnani','Kerala');
	$INDIA[727]=array('Punalur','Kerala');
	$INDIA[728]=array('Puthuppally','Kerala');
	$INDIA[729]=array('Quilandy','Kerala');
	$INDIA[730]=array('Shoranur','Kerala');
	$INDIA[731]=array('Taliparamba','Kerala');
	$INDIA[732]=array('Thiruvalla','Kerala');
	$INDIA[733]=array('Thiruvananthapuram','Kerala');
	$INDIA[734]=array('Thodupuzha','Kerala');
	$INDIA[735]=array('Thrissur','Kerala');
	$INDIA[736]=array('Tirur','Kerala');
	$INDIA[737]=array('Vadakara','Kerala');
	$INDIA[738]=array('Vaikom','Kerala');
	$INDIA[739]=array('Varkala','Kerala');
	$INDIA[740]=array('Amini','Lakshadweep');
	$INDIA[741]=array('Kavaratti','Lakshadweep');
	$INDIA[742]=array('Ashok Nagar','Madhya Pradesh');
	$INDIA[743]=array('Badagaon','Madhya Pradesh');
	$INDIA[744]=array('Balaghat','Madhya Pradesh');
	$INDIA[745]=array('Barwani','Madhya Pradesh');
	$INDIA[746]=array('Betul','Madhya Pradesh');
	$INDIA[747]=array('Bhopal','Madhya Pradesh');
	$INDIA[748]=array('Burhanpur','Madhya Pradesh');
	$INDIA[749]=array('Chhatarpur','Madhya Pradesh');
	$INDIA[750]=array('Chhindwara','Madhya Pradesh');
	$INDIA[751]=array('Chitrakoot','Madhya Pradesh');
	$INDIA[752]=array('Dabra','Madhya Pradesh');
	$INDIA[753]=array('Damoh','Madhya Pradesh');
	$INDIA[754]=array('Datia','Madhya Pradesh');
	$INDIA[755]=array('Dewas','Madhya Pradesh');
	$INDIA[756]=array('Dhar','Madhya Pradesh');
	$INDIA[757]=array('Fatehabad','Madhya Pradesh');
	$INDIA[758]=array('Guna','Madhya Pradesh');
	$INDIA[759]=array('Gwalior','Madhya Pradesh');
	$INDIA[760]=array('Harda','Madhya Pradesh');
	$INDIA[761]=array('Indore','Madhya Pradesh');
	$INDIA[762]=array('Itarsi','Madhya Pradesh');
	$INDIA[763]=array('Jabalpur','Madhya Pradesh');
	$INDIA[764]=array('Jhabua','Madhya Pradesh');
	$INDIA[765]=array('Kailaras','Madhya Pradesh');
	$INDIA[766]=array('Katni','Madhya Pradesh');
	$INDIA[767]=array('Khurai','Madhya Pradesh');
	$INDIA[768]=array('Kotma','Madhya Pradesh');
	$INDIA[769]=array('Lahar','Madhya Pradesh');
	$INDIA[770]=array('Lundi','Madhya Pradesh');
	$INDIA[771]=array('Maharajpur','Madhya Pradesh');
	$INDIA[772]=array('Mahidpur','Madhya Pradesh');
	$INDIA[773]=array('Maihar','Madhya Pradesh');
	$INDIA[774]=array('Malajkhand','Madhya Pradesh');
	$INDIA[775]=array('Manasa','Madhya Pradesh');
	$INDIA[776]=array('Manawar','Madhya Pradesh');
	$INDIA[777]=array('Mandideep','Madhya Pradesh');
	$INDIA[778]=array('Mandla','Madhya Pradesh');
	$INDIA[779]=array('Mandsaur','Madhya Pradesh');
	$INDIA[780]=array('Mauganj','Madhya Pradesh');
	$INDIA[781]=array('Mhow Cantonment','Madhya Pradesh');
	$INDIA[782]=array('Mhowgaon','Madhya Pradesh');
	$INDIA[783]=array('Morena','Madhya Pradesh');
	$INDIA[784]=array('Multai','Madhya Pradesh');
	$INDIA[785]=array('Murwara','Madhya Pradesh');
	$INDIA[786]=array('Nagda','Madhya Pradesh');
	$INDIA[787]=array('Nainpur','Madhya Pradesh');
	$INDIA[788]=array('Narsinghgarh','Madhya Pradesh');
	$INDIA[789]=array('Narsinghgarh','Madhya Pradesh');
	$INDIA[790]=array('Neemuch','Madhya Pradesh');
	$INDIA[791]=array('Nepanagar','Madhya Pradesh');
	$INDIA[792]=array('Niwari','Madhya Pradesh');
	$INDIA[793]=array('Nowgong','Madhya Pradesh');
	$INDIA[794]=array('Nowrozabad','Madhya Pradesh');
	$INDIA[795]=array('Pachore','Madhya Pradesh');
	$INDIA[796]=array('Pali','Madhya Pradesh');
	$INDIA[797]=array('Panagar','Madhya Pradesh');
	$INDIA[798]=array('Pandhurna','Madhya Pradesh');
	$INDIA[799]=array('Panna','Madhya Pradesh');
	$INDIA[800]=array('Pasan','Madhya Pradesh');
	$INDIA[801]=array('Pipariya','Madhya Pradesh');
	$INDIA[802]=array('Pithampur','Madhya Pradesh');
	$INDIA[803]=array('Porsa','Madhya Pradesh');
	$INDIA[804]=array('Prithvipur','Madhya Pradesh');
	$INDIA[805]=array('Raghogarh-Vijaypur','Madhya Pradesh');
	$INDIA[806]=array('Rahatgarh','Madhya Pradesh');
	$INDIA[807]=array('Raisen','Madhya Pradesh');
	$INDIA[808]=array('Rajgarh','Madhya Pradesh');
	$INDIA[809]=array('Ratlam','Madhya Pradesh');
	$INDIA[810]=array('Rau','Madhya Pradesh');
	$INDIA[811]=array('Rehli','Madhya Pradesh');
	$INDIA[812]=array('Rewa','Madhya Pradesh');
	$INDIA[813]=array('Sabalgarh','Madhya Pradesh');
	$INDIA[814]=array('Sagar','Madhya Pradesh');
	$INDIA[815]=array('Sanawad','Madhya Pradesh');
	$INDIA[816]=array('Sarangpur','Madhya Pradesh');
	$INDIA[817]=array('Sarni','Madhya Pradesh');
	$INDIA[818]=array('Satna','Madhya Pradesh');
	$INDIA[819]=array('Sausar','Madhya Pradesh');
	$INDIA[820]=array('Sehore','Madhya Pradesh');
	$INDIA[821]=array('Sendhwa','Madhya Pradesh');
	$INDIA[822]=array('Seoni','Madhya Pradesh');
	$INDIA[823]=array('Seoni-Malwa','Madhya Pradesh');
	$INDIA[824]=array('Shahdol','Madhya Pradesh');
	$INDIA[825]=array('Shajapur','Madhya Pradesh');
	$INDIA[826]=array('Shamgarh','Madhya Pradesh');
	$INDIA[827]=array('Sheopur','Madhya Pradesh');
	$INDIA[828]=array('Shivpuri','Madhya Pradesh');
	$INDIA[829]=array('Shujalpur','Madhya Pradesh');
	$INDIA[830]=array('Sidhi','Madhya Pradesh');
	$INDIA[831]=array('Sihora','Madhya Pradesh');
	$INDIA[832]=array('Singrauli','Madhya Pradesh');
	$INDIA[833]=array('Sironj','Madhya Pradesh');
	$INDIA[834]=array('Sohagpur','Madhya Pradesh');
	$INDIA[835]=array('Tarana','Madhya Pradesh');
	$INDIA[836]=array('Tikamgarh','Madhya Pradesh');
	$INDIA[837]=array('Ujhani','Madhya Pradesh');
	$INDIA[838]=array('Ujjain','Madhya Pradesh');
	$INDIA[839]=array('Umaria','Madhya Pradesh');
	$INDIA[840]=array('Vidisha','Madhya Pradesh');
	$INDIA[841]=array('Wara Seoni','Madhya Pradesh');
	$INDIA[842]=array('Achalpur','Maharashtra');
	$INDIA[843]=array('Ahmednagar','Maharashtra');
	$INDIA[844]=array('Ahmedpur','Maharashtra');
	$INDIA[845]=array('Ajra','Maharashtra');
	$INDIA[846]=array('Akkalkot','Maharashtra');
	$INDIA[847]=array('Akola','Maharashtra');
	$INDIA[848]=array('Akot','Maharashtra');
	$INDIA[849]=array('Alandi','Maharashtra');
	$INDIA[850]=array('Alibag','Maharashtra');
	$INDIA[851]=array('Amalner','Maharashtra');
	$INDIA[852]=array('Ambad','Maharashtra');
	$INDIA[853]=array('Ambejogai','Maharashtra');
	$INDIA[854]=array('Ambivali Tarf Wankhal','Maharashtra');
	$INDIA[855]=array('Amravati','Maharashtra');
	$INDIA[856]=array('Anjangaon','Maharashtra');
	$INDIA[857]=array('Arvi','Maharashtra');
	$INDIA[858]=array('Ashta','Maharashtra');
	$INDIA[859]=array('Aurangabad','Maharashtra');
	$INDIA[860]=array('Ausa','Maharashtra');
	$INDIA[861]=array('Baramati','Maharashtra');
	$INDIA[862]=array('Bhandara','Maharashtra');
	$INDIA[863]=array('Bhiwandi','Maharashtra');
	$INDIA[864]=array('Bhusawal','Maharashtra');
	$INDIA[865]=array('Chalisgaon','Maharashtra');
	$INDIA[866]=array('Chandrapur','Maharashtra');
	$INDIA[867]=array('Chinchani','Maharashtra');
	$INDIA[868]=array('Chiplun','Maharashtra');
	$INDIA[869]=array('Daund','Maharashtra');
	$INDIA[870]=array('Devgarh','Maharashtra');
	$INDIA[871]=array('Dhule','Maharashtra');
	$INDIA[872]=array('Dombivli','Maharashtra');
	$INDIA[873]=array('Durgapur','Maharashtra');
	$INDIA[874]=array('Gadchiroli','Maharashtra');
	$INDIA[875]=array('Ghatanji','Maharashtra');
	$INDIA[876]=array('Gondiya','Maharashtra');
	$INDIA[877]=array('Ichalkaranji','Maharashtra');
	$INDIA[878]=array('Jalna','Maharashtra');
	$INDIA[879]=array('Jalgaon','Maharashtra');
	$INDIA[880]=array('Junnar','Maharashtra');
	$INDIA[881]=array('Kalyan','Maharashtra');
	$INDIA[882]=array('Kamthi','Maharashtra');
	$INDIA[883]=array('Karad','Maharashtra');
	$INDIA[884]=array('karjat','Maharashtra');
	$INDIA[885]=array('Kolhapur','Maharashtra');
	$INDIA[886]=array('Latur','Maharashtra');
	$INDIA[887]=array('Loha','Maharashtra');
	$INDIA[888]=array('Lonar','Maharashtra');
	$INDIA[889]=array('Lonavla','Maharashtra');
	$INDIA[890]=array('Mahabaleswar','Maharashtra');
	$INDIA[891]=array('Mahad','Maharashtra');
	$INDIA[892]=array('Mahuli','Maharashtra');
	$INDIA[893]=array('Malegaon','Maharashtra');
	$INDIA[894]=array('Malkapur','Maharashtra');
	$INDIA[895]=array('Manchar','Maharashtra');
	$INDIA[896]=array('Mangalvedhe','Maharashtra');
	$INDIA[897]=array('Mangrulpir','Maharashtra');
	$INDIA[898]=array('Manjlegaon','Maharashtra');
	$INDIA[899]=array('Manmad','Maharashtra');
	$INDIA[900]=array('Manwath','Maharashtra');
	$INDIA[901]=array('Mehkar','Maharashtra');
	$INDIA[902]=array('Mhaswad','Maharashtra');
	$INDIA[903]=array('Mira-Bhayandar','Maharashtra');
	$INDIA[904]=array('Miraj','Maharashtra');
	$INDIA[905]=array('Morshi','Maharashtra');
	$INDIA[906]=array('Mukhed','Maharashtra');
	$INDIA[907]=array('Mul','Maharashtra');
	$INDIA[908]=array('Mumbai','Maharashtra');
	$INDIA[909]=array('Murtijapur','Maharashtra');
	$INDIA[910]=array('Nagpur','Maharashtra');
	$INDIA[911]=array('Nalasopara','Maharashtra');
	$INDIA[912]=array('Nanded-Waghala','Maharashtra');
	$INDIA[913]=array('Nandgaon','Maharashtra');
	$INDIA[914]=array('Nandura','Maharashtra');
	$INDIA[915]=array('Nandurbar','Maharashtra');
	$INDIA[916]=array('Narkhed','Maharashtra');
	$INDIA[917]=array('Nashik','Maharashtra');
	$INDIA[918]=array('Navi Mumbai','Maharashtra');
	$INDIA[919]=array('Nawapur','Maharashtra');
	$INDIA[920]=array('Nilanga','Maharashtra');
	$INDIA[921]=array('Osmanabad','Maharashtra');
	$INDIA[922]=array('Ozar','Maharashtra');
	$INDIA[923]=array('Pachora','Maharashtra');
	$INDIA[924]=array('Paithan','Maharashtra');
	$INDIA[925]=array('Palghar','Maharashtra');
	$INDIA[926]=array('Pandharkaoda','Maharashtra');
	$INDIA[927]=array('Pandharpur','Maharashtra');
	$INDIA[928]=array('Panvel','Maharashtra');
	$INDIA[929]=array('Parbhani','Maharashtra');
	$INDIA[930]=array('Parli','Maharashtra');
	$INDIA[931]=array('Parola','Maharashtra');
	$INDIA[932]=array('Partur','Maharashtra');
	$INDIA[933]=array('Pathardi','Maharashtra');
	$INDIA[934]=array('Pathri','Maharashtra');
	$INDIA[935]=array('Patur','Maharashtra');
	$INDIA[936]=array('Pauni','Maharashtra');
	$INDIA[937]=array('Pen','Maharashtra');
	$INDIA[938]=array('Phaltan','Maharashtra');
	$INDIA[939]=array('Pulgaon','Maharashtra');
	$INDIA[940]=array('Pune','Maharashtra');
	$INDIA[941]=array('Purna','Maharashtra');
	$INDIA[942]=array('Pusad','Maharashtra');
	$INDIA[943]=array('Raichuri','Maharashtra');
	$INDIA[944]=array('Rajura','Maharashtra');
	$INDIA[945]=array('Ramtek','Maharashtra');
	$INDIA[946]=array('Ratnagiri','Maharashtra');
	$INDIA[947]=array('Raver','Maharashtra');
	$INDIA[948]=array('Risod','Maharashtra');
	$INDIA[949]=array('Sailu','Maharashtra');
	$INDIA[950]=array('Sangamner','Maharashtra');
	$INDIA[951]=array('Sangli','Maharashtra');
	$INDIA[952]=array('Sangole','Maharashtra');
	$INDIA[953]=array('Sasvad','Maharashtra');
	$INDIA[954]=array('Satana','Maharashtra');
	$INDIA[955]=array('Satara','Maharashtra');
	$INDIA[956]=array('Savner','Maharashtra');
	$INDIA[957]=array('Sawantwadi','Maharashtra');
	$INDIA[958]=array('Shahade','Maharashtra');
	$INDIA[959]=array('Shegaon','Maharashtra');
	$INDIA[960]=array('Shendurjana','Maharashtra');
	$INDIA[961]=array('Shirdi','Maharashtra');
	$INDIA[962]=array('Shirpur-Warwade','Maharashtra');
	$INDIA[963]=array('Shirur','Maharashtra');
	$INDIA[964]=array('Shrigonda','Maharashtra');
	$INDIA[965]=array('Shrirampur','Maharashtra');
	$INDIA[966]=array('Sillod','Maharashtra');
	$INDIA[967]=array('Sinnar','Maharashtra');
	$INDIA[968]=array('Solapur','Maharashtra');
	$INDIA[969]=array('Soyagaon','Maharashtra');
	$INDIA[970]=array('Talegaon Dabhade','Maharashtra');
	$INDIA[971]=array('Talode','Maharashtra');
	$INDIA[972]=array('Tasgaon','Maharashtra');
	$INDIA[973]=array('Thane','Maharashtra');
	$INDIA[974]=array('Tirora','Maharashtra');
	$INDIA[975]=array('Tuljapur','Maharashtra');
	$INDIA[976]=array('Tumsar','Maharashtra');
	$INDIA[977]=array('Uchgaon','Maharashtra');
	$INDIA[978]=array('Udgir','Maharashtra');
	$INDIA[979]=array('Umarga','Maharashtra');
	$INDIA[980]=array('Umarkhed','Maharashtra');
	$INDIA[981]=array('Umred','Maharashtra');
	$INDIA[982]=array('Uran','Maharashtra');
	$INDIA[983]=array('Uran Islampur','Maharashtra');
	$INDIA[984]=array('Vadgaon Kasba','Maharashtra');
	$INDIA[985]=array('Vaijapur','Maharashtra');
	$INDIA[986]=array('Vasai','Maharashtra');
	$INDIA[987]=array('Virar','Maharashtra');
	$INDIA[988]=array('Vita','Maharashtra');
	$INDIA[989]=array('Wadgaon Road','Maharashtra');
	$INDIA[990]=array('Wai','Maharashtra');
	$INDIA[991]=array('Wani','Maharashtra');
	$INDIA[992]=array('Wardha','Maharashtra');
	$INDIA[993]=array('Warora','Maharashtra');
	$INDIA[994]=array('Warud','Maharashtra');
	$INDIA[995]=array('Washim','Maharashtra');
	$INDIA[996]=array('Yavatmal','Maharashtra');
	$INDIA[997]=array('Yawal','Maharashtra');
	$INDIA[998]=array('Yevla','Maharashtra');
	$INDIA[999]=array('Imphal','Manipur');
	$INDIA[1000]=array('Kakching','Manipur');
	$INDIA[1001]=array('Lilong','Manipur');
	$INDIA[1002]=array('Mayang Imphal','Manipur');
	$INDIA[1003]=array('Thoubal','Manipur');
	$INDIA[1004]=array('Jowai','Meghalaya');
	$INDIA[1005]=array('Nongstoin','Meghalaya');
	$INDIA[1006]=array('Shillong','Meghalaya');
	$INDIA[1007]=array('Tura','Meghalaya');
	$INDIA[1008]=array('Aizawl','Mizoram');
	$INDIA[1009]=array('Champhai','Mizoram');
	$INDIA[1010]=array('Lunglei','Mizoram');
	$INDIA[1011]=array('Saiha','Mizoram');
	$INDIA[1012]=array('Dimapur','Nagaland');
	$INDIA[1013]=array('Kohima','Nagaland');
	$INDIA[1014]=array('Mokokchung','Nagaland');
	$INDIA[1015]=array('Tuensang','Nagaland');
	$INDIA[1016]=array('Wokha','Nagaland');
	$INDIA[1017]=array('Zunheboto','Nagaland');
	$INDIA[1018]=array('Anandapur','Orissa');
	$INDIA[1019]=array('Anugul','Orissa');
	$INDIA[1020]=array('Asika','Orissa');
	$INDIA[1021]=array('Balangir','Orissa');
	$INDIA[1022]=array('Balasore','Orissa');
	$INDIA[1023]=array('Baleshwar','Orissa');
	$INDIA[1024]=array('Bamra','Orissa');
	$INDIA[1025]=array('Bargarh','Orissa');
	$INDIA[1026]=array('Barbil','Orissa');
	$INDIA[1027]=array('Bargarh','Orissa');
	$INDIA[1028]=array('Baripada','Orissa');
	$INDIA[1029]=array('Basudebpur','Orissa');
	$INDIA[1030]=array('Belpahar','Orissa');
	$INDIA[1031]=array('Berhampur','Orissa');
	$INDIA[1032]=array('Bhadrak','Orissa');
	$INDIA[1033]=array('Bhawanipatna','Orissa');
	$INDIA[1034]=array('Bhuban','Orissa');
	$INDIA[1035]=array('Bhubaneswar','Orissa');
	$INDIA[1036]=array('Biramitrapur','Orissa');
	$INDIA[1037]=array('Brahmapur','Orissa');
	$INDIA[1038]=array('Brajrajnagar','Orissa');
	$INDIA[1039]=array('Burla','Orissa');
	$INDIA[1040]=array('Byasanagar','Orissa');
	$INDIA[1041]=array('Cuttack','Orissa');
	$INDIA[1042]=array('Debagarh','Orissa');
	$INDIA[1043]=array('Dhenkanal','Orissa');
	$INDIA[1044]=array('Ganjam','Orissa');
	$INDIA[1045]=array('Gunupur','Orissa');
	$INDIA[1046]=array('Hinjilicut','Orissa');
	$INDIA[1047]=array('Jagatsinghapur','Orissa');
	$INDIA[1048]=array('Jajapur','Orissa');
	$INDIA[1049]=array('Jaleswar','Orissa');
	$INDIA[1050]=array('Jatani','Orissa');
	$INDIA[1051]=array('Jeypur','Orissa');
	$INDIA[1052]=array('Jharsuguda','Orissa');
	$INDIA[1053]=array('Joda','Orissa');
	$INDIA[1054]=array('Kantabanji','Orissa');
	$INDIA[1055]=array('Karanjia','Orissa');
	$INDIA[1056]=array('Kendrapara','Orissa');
	$INDIA[1057]=array('Kendujhar','Orissa');
	$INDIA[1058]=array('Khordha','Orissa');
	$INDIA[1059]=array('Koraput','Orissa');
	$INDIA[1060]=array('Kuchinda','Orissa');
	$INDIA[1061]=array('Madhyamgram','Orissa');
	$INDIA[1062]=array('Malkangiri','Orissa');
	$INDIA[1063]=array('Nabarangapur','Orissa');
	$INDIA[1064]=array('Paradip','Orissa');
	$INDIA[1065]=array('Parlakhemundi','Orissa');
	$INDIA[1066]=array('Pattamundai','Orissa');
	$INDIA[1067]=array('Phulabani','Orissa');
	$INDIA[1068]=array('Puri','Orissa');
	$INDIA[1069]=array('Rairangpur','Orissa');
	$INDIA[1070]=array('Rajagangapur','Orissa');
	$INDIA[1071]=array('Raurkela','Orissa');
	$INDIA[1072]=array('Rayagada','Orissa');
	$INDIA[1073]=array('Sambalpur','Orissa');
	$INDIA[1074]=array('Soro','Orissa');
	$INDIA[1075]=array('Sunabeda','Orissa');
	$INDIA[1076]=array('Sundargarh','Orissa');
	$INDIA[1077]=array('Talcher','Orissa');
	$INDIA[1078]=array('Titlagarh','Orissa');
	$INDIA[1079]=array('Umarkote','Orissa');
	$INDIA[1080]=array('Karaikal','Pondicherry');
	$INDIA[1081]=array('Mahe','Pondicherry');
	$INDIA[1082]=array('Pondicherry','Pondicherry');
	$INDIA[1083]=array('Yanam','Pondicherry');
	$INDIA[1084]=array('Ahmedgarh','Punjab');
	$INDIA[1085]=array('Amritsar','Punjab');
	$INDIA[1086]=array('Barnala','Punjab');
	$INDIA[1087]=array('Batala','Punjab');
	$INDIA[1088]=array('Bathinda','Punjab');
	$INDIA[1089]=array('Bhagha Purana','Punjab');
	$INDIA[1090]=array('Budhlada','Punjab');
	$INDIA[1091]=array('Dasua','Punjab');
	$INDIA[1092]=array('Dhuri','Punjab');
	$INDIA[1093]=array('Dinanagar','Punjab');
	$INDIA[1094]=array('Faridkot','Punjab');
	$INDIA[1095]=array('Fazilka','Punjab');
	$INDIA[1096]=array('Firozpur','Punjab');
	$INDIA[1097]=array('Firozpur Cantt.','Punjab');
	$INDIA[1098]=array('Giddarbaha','Punjab');
	$INDIA[1099]=array('Gobindgarh','Punjab');
	$INDIA[1100]=array('Gurdaspur','Punjab');
	$INDIA[1101]=array('Hoshiarpur','Punjab');
	$INDIA[1102]=array('Jagraon','Punjab');
	$INDIA[1103]=array('Jaitu','Punjab');
	$INDIA[1104]=array('Jalalabad','Punjab');
	$INDIA[1105]=array('Jalandhar Cantt.','Punjab');
	$INDIA[1106]=array('Jalandhar','Punjab');
	$INDIA[1107]=array('Jandiala','Punjab');
	$INDIA[1108]=array('Kapurthala','Punjab');
	$INDIA[1109]=array('Karoran','Punjab');
	$INDIA[1110]=array('Kartarpur','Punjab');
	$INDIA[1111]=array('Khanna','Punjab');
	$INDIA[1112]=array('Kharar','Punjab');
	$INDIA[1113]=array('Kot Kapura','Punjab');
	$INDIA[1114]=array('Kurali','Punjab');
	$INDIA[1115]=array('Kamahi Devi','Punjab');
	$INDIA[1116]=array('Longowal','Punjab');
	$INDIA[1117]=array('Ludhiana','Punjab');
	$INDIA[1118]=array('Malerkotla','Punjab');
	$INDIA[1119]=array('Malout','Punjab');
	$INDIA[1120]=array('Mansa','Punjab');
	$INDIA[1121]=array('Maur','Punjab');
	$INDIA[1122]=array('Moga','Punjab');
	$INDIA[1123]=array('Mohali','Punjab');
	$INDIA[1124]=array('Morinda','Punjab');
	$INDIA[1125]=array('Mukatsar','Punjab');
	$INDIA[1126]=array('Mukerian','Punjab');
	$INDIA[1127]=array('Muktsar','Punjab');
	$INDIA[1128]=array('Nabha','Punjab');
	$INDIA[1129]=array('Nakodar','Punjab');
	$INDIA[1130]=array('Nangal','Punjab');
	$INDIA[1131]=array('Nawanshahr','Punjab');
	$INDIA[1132]=array('Pathankot','Punjab');
	$INDIA[1133]=array('Patiala','Punjab');
	$INDIA[1134]=array('Patran','Punjab');
	$INDIA[1135]=array('Patti','Punjab');
	$INDIA[1136]=array('Phagwara','Punjab');
	$INDIA[1137]=array('Phillaur','Punjab');
	$INDIA[1138]=array('Qadian','Punjab');
	$INDIA[1139]=array('Raikot','Punjab');
	$INDIA[1140]=array('Rajpura','Punjab');
	$INDIA[1141]=array('Rampura Phul','Punjab');
	$INDIA[1142]=array('Rupnagar','Punjab');
	$INDIA[1143]=array('Samana','Punjab');
	$INDIA[1144]=array('Sangrur','Punjab');
	$INDIA[1145]=array('Sirhind Fatehgarh Sahib','Punjab');
	$INDIA[1146]=array('Sujanpur','Punjab');
	$INDIA[1147]=array('Sunam','Punjab');
	$INDIA[1148]=array('Talwara','Punjab');
	$INDIA[1149]=array('Tarn Taran','Punjab');
	$INDIA[1150]=array('Urmar Tanda','Punjab');
	$INDIA[1151]=array('Zira','Punjab');
	$INDIA[1152]=array('Zirakpur','Punjab');
	$INDIA[1153]=array('Ajmer','Rajasthan');
	$INDIA[1154]=array('Alwar','Rajasthan');
	$INDIA[1155]=array('Bali','Rajasthan');
	$INDIA[1156]=array('Bandikui','Rajasthan');
	$INDIA[1157]=array('Banswara','Rajasthan');
	$INDIA[1158]=array('Baran','Rajasthan');
	$INDIA[1159]=array('Barmer','Rajasthan');
	$INDIA[1160]=array('Beawar','Rajasthan');
	$INDIA[1161]=array('Bharatpur','Rajasthan');
	$INDIA[1162]=array('Bhilwara','Rajasthan');
	$INDIA[1163]=array('Bhinmal','Rajasthan');
	$INDIA[1164]=array('Bikaner','Rajasthan');
	$INDIA[1165]=array('Bilara','Rajasthan');
	$INDIA[1166]=array('Churu','Rajasthan');
	$INDIA[1167]=array('Devgarh','Rajasthan');
	$INDIA[1168]=array('Falna','Rajasthan');
	$INDIA[1169]=array('Fatehpur','Rajasthan');
	$INDIA[1170]=array('Hanumangarh','Rajasthan');
	$INDIA[1171]=array('Harsawa','Rajasthan');
	$INDIA[1172]=array('Jaipur','Rajasthan');
	$INDIA[1173]=array('Jaisalmer','Rajasthan');
	$INDIA[1174]=array('Jaitaran','Rajasthan');
	$INDIA[1175]=array('Jalore','Rajasthan');
	$INDIA[1176]=array('Jhalawar','Rajasthan');
	$INDIA[1177]=array('Jhunjhunu','Rajasthan');
	$INDIA[1178]=array('Jodhpur','Rajasthan');
	$INDIA[1179]=array('Kota','Rajasthan');
	$INDIA[1180]=array('Lachhmangarh','Rajasthan');
	$INDIA[1181]=array('Ladnu','Rajasthan');
	$INDIA[1182]=array('Lakheri','Rajasthan');
	$INDIA[1183]=array('Lalsot','Rajasthan');
	$INDIA[1184]=array('Losal','Rajasthan');
	$INDIA[1185]=array('Mahwa','Rajasthan');
	$INDIA[1186]=array('Makrana','Rajasthan');
	$INDIA[1187]=array('Malpura','Rajasthan');
	$INDIA[1188]=array('Mandalgarh','Rajasthan');
	$INDIA[1189]=array('Mandawa','Rajasthan');
	$INDIA[1190]=array('Mangrol','Rajasthan');
	$INDIA[1191]=array('Merta City','Rajasthan');
	$INDIA[1192]=array('Mount Abu','Rajasthan');
	$INDIA[1193]=array('Nadbai','Rajasthan');
	$INDIA[1194]=array('Nagar','Rajasthan');
	$INDIA[1195]=array('Nagaur','Rajasthan');
	$INDIA[1196]=array('Nargund','Rajasthan');
	$INDIA[1197]=array('Nasirabad','Rajasthan');
	$INDIA[1198]=array('Nathdwara','Rajasthan');
	$INDIA[1199]=array('Navalgund','Rajasthan');
	$INDIA[1200]=array('Nawalgarh','Rajasthan');
	$INDIA[1201]=array('Neem-Ka-Thana','Rajasthan');
	$INDIA[1202]=array('Nelamangala','Rajasthan');
	$INDIA[1203]=array('Nimbahera','Rajasthan');
	$INDIA[1204]=array('Niwai','Rajasthan');
	$INDIA[1205]=array('Nohar','Rajasthan');
	$INDIA[1206]=array('Nokha','Rajasthan');
	$INDIA[1207]=array('Pali','Rajasthan');
	$INDIA[1208]=array('Phalodi','Rajasthan');
	$INDIA[1209]=array('Phulera','Rajasthan');
	$INDIA[1210]=array('Pilani','Rajasthan');
	$INDIA[1211]=array('Pilibanga','Rajasthan');
	$INDIA[1212]=array('Pindwara','Rajasthan');
	$INDIA[1213]=array('Pipar City','Rajasthan');
	$INDIA[1214]=array('Prantij','Rajasthan');
	$INDIA[1215]=array('Pratapgarh','Rajasthan');
	$INDIA[1216]=array('Raisinghnagar','Rajasthan');
	$INDIA[1217]=array('Rajakhera','Rajasthan');
	$INDIA[1218]=array('Rajaldesar','Rajasthan');
	$INDIA[1219]=array('Rajgarh (Alwar)','Rajasthan');
	$INDIA[1220]=array('Rajgarh (Churu)','Rajasthan');
	$INDIA[1221]=array('Rajsamand','Rajasthan');
	$INDIA[1222]=array('Ramganj Mandi','Rajasthan');
	$INDIA[1223]=array('Ramngarh','Rajasthan');
	$INDIA[1224]=array('Ratangarh','Rajasthan');
	$INDIA[1225]=array('Rawatbhata','Rajasthan');
	$INDIA[1226]=array('Rawatsar','Rajasthan');
	$INDIA[1227]=array('Reengus','Rajasthan');
	$INDIA[1228]=array('Sadri','Rajasthan');
	$INDIA[1229]=array('Sadulshahar','Rajasthan');
	$INDIA[1230]=array('Sagwara','Rajasthan');
	$INDIA[1231]=array('Sambhar','Rajasthan');
	$INDIA[1232]=array('Sanchore','Rajasthan');
	$INDIA[1233]=array('Sangaria','Rajasthan');
	$INDIA[1234]=array('Sardarshahar','Rajasthan');
	$INDIA[1235]=array('Sawai Madhopur','Rajasthan');
	$INDIA[1236]=array('Shahpura','Rajasthan');
	$INDIA[1237]=array('Shahpura','Rajasthan');
	$INDIA[1238]=array('Sheoganj','Rajasthan');
	$INDIA[1239]=array('Sikar','Rajasthan');
	$INDIA[1240]=array('Sirohi','Rajasthan');
	$INDIA[1241]=array('Sojat','Rajasthan');
	$INDIA[1242]=array('Sri Madhopur','Rajasthan');
	$INDIA[1243]=array('Sujangarh','Rajasthan');
	$INDIA[1244]=array('Sumerpur','Rajasthan');
	$INDIA[1245]=array('Suratgarh','Rajasthan');
	$INDIA[1246]=array('Taranagar','Rajasthan');
	$INDIA[1247]=array('Todabhim','Rajasthan');
	$INDIA[1248]=array('Todaraisingh','Rajasthan');
	$INDIA[1249]=array('Tonk','Rajasthan');
	$INDIA[1250]=array('Udaipur','Rajasthan');
	$INDIA[1251]=array('Udaipurwati','Rajasthan');
	$INDIA[1252]=array('Vijainagar','Rajasthan');
	$INDIA[1253]=array('Gangtok','Sikkim');
	$INDIA[1254]=array('Arakkonam','Tamil Nadu');
	$INDIA[1255]=array('Arcot','Tamil Nadu');
	$INDIA[1256]=array('Aruppukkottai','Tamil Nadu');
	$INDIA[1257]=array('Bhavani','Tamil Nadu');
	$INDIA[1258]=array('Chengalpattu','Tamil Nadu');
	$INDIA[1259]=array('Chennai','Tamil Nadu');
	$INDIA[1260]=array('Chinna salem','Tamil Nadu');
	$INDIA[1261]=array('Coimbatore','Tamil Nadu');
	$INDIA[1262]=array('Coonoor','Tamil Nadu');
	$INDIA[1263]=array('Cuddalore','Tamil Nadu');
	$INDIA[1264]=array('Dharmapuri','Tamil Nadu');
	$INDIA[1265]=array('Dindigul','Tamil Nadu');
	$INDIA[1266]=array('Erode','Tamil Nadu');
	$INDIA[1267]=array('Gingee','Tamil Nadu');
	$INDIA[1268]=array('Gobichettipalayam','Tamil Nadu');
	$INDIA[1269]=array('Gudalur','Tamil Nadu');
	$INDIA[1270]=array('Gudalur','Tamil Nadu');
	$INDIA[1271]=array('Gudalur','Tamil Nadu');
	$INDIA[1272]=array('Jayankondam','Tamil Nadu');
	$INDIA[1273]=array('Kanchipuram','Tamil Nadu');
	$INDIA[1274]=array('Karaikudi','Tamil Nadu');
	$INDIA[1275]=array('Karur','Tamil Nadu');
	$INDIA[1276]=array('Karungal','Tamil Nadu');
	$INDIA[1277]=array('Kollankodu','Tamil Nadu');
	$INDIA[1278]=array('Lalgudi','Tamil Nadu');
	$INDIA[1279]=array('Madurai','Tamil Nadu');
	$INDIA[1280]=array('Nagapattinam','Tamil Nadu');
	$INDIA[1281]=array('Nagercoil','Tamil Nadu');
	$INDIA[1282]=array('Namagiripettai','Tamil Nadu');
	$INDIA[1283]=array('Namakkal','Tamil Nadu');
	$INDIA[1284]=array('Nandivaram-Guduvancheri','Tamil Nadu');
	$INDIA[1285]=array('Nanjikottai','Tamil Nadu');
	$INDIA[1286]=array('Natham','Tamil Nadu');
	$INDIA[1287]=array('Nellikuppam','Tamil Nadu');
	$INDIA[1288]=array('Neyveli','Tamil Nadu');
	$INDIA[1289]=array('O Valley','Tamil Nadu');
	$INDIA[1290]=array('Oddanchatram','Tamil Nadu');
	$INDIA[1291]=array('P.N.Patti','Tamil Nadu');
	$INDIA[1292]=array('Pacode','Tamil Nadu');
	$INDIA[1293]=array('Padmanabhapuram','Tamil Nadu');
	$INDIA[1294]=array('Palani','Tamil Nadu');
	$INDIA[1295]=array('Palladam','Tamil Nadu');
	$INDIA[1296]=array('Pallapatti','Tamil Nadu');
	$INDIA[1297]=array('Pallikonda','Tamil Nadu');
	$INDIA[1298]=array('Panagudi','Tamil Nadu');
	$INDIA[1299]=array('Panruti','Tamil Nadu');
	$INDIA[1300]=array('Paramakudi','Tamil Nadu');
	$INDIA[1301]=array('Parangipettai','Tamil Nadu');
	$INDIA[1302]=array('Pattukkottai','Tamil Nadu');
	$INDIA[1303]=array('Perambalur','Tamil Nadu');
	$INDIA[1304]=array('Peravurani','Tamil Nadu');
	$INDIA[1305]=array('Periyakulam','Tamil Nadu');
	$INDIA[1306]=array('Periyasemur','Tamil Nadu');
	$INDIA[1307]=array('Pernampattu','Tamil Nadu');
	$INDIA[1308]=array('Pollachi','Tamil Nadu');
	$INDIA[1309]=array('Polur','Tamil Nadu');
	$INDIA[1310]=array('Ponneri','Tamil Nadu');
	$INDIA[1311]=array('Pudukkottai','Tamil Nadu');
	$INDIA[1312]=array('Pudupattinam','Tamil Nadu');
	$INDIA[1313]=array('Puliyankudi','Tamil Nadu');
	$INDIA[1314]=array('Punjaipugalur','Tamil Nadu');
	$INDIA[1315]=array('Rajapalayam','Tamil Nadu');
	$INDIA[1316]=array('Ramanathapuram','Tamil Nadu');
	$INDIA[1317]=array('Rameshwaram','Tamil Nadu');
	$INDIA[1318]=array('Rasipuram','Tamil Nadu');
	$INDIA[1319]=array('Salem','Tamil Nadu');
	$INDIA[1320]=array('Sankarankoil','Tamil Nadu');
	$INDIA[1321]=array('Sankari','Tamil Nadu');
	$INDIA[1322]=array('Sathyamangalam','Tamil Nadu');
	$INDIA[1323]=array('Sattur','Tamil Nadu');
	$INDIA[1324]=array('Shenkottai','Tamil Nadu');
	$INDIA[1325]=array('Sholavandan','Tamil Nadu');
	$INDIA[1326]=array('Sholingur','Tamil Nadu');
	$INDIA[1327]=array('Sirkali','Tamil Nadu');
	$INDIA[1328]=array('Sivaganga','Tamil Nadu');
	$INDIA[1329]=array('Sivagiri','Tamil Nadu');
	$INDIA[1330]=array('Sivakasi','Tamil Nadu');
	$INDIA[1331]=array('Srivilliputhur','Tamil Nadu');
	$INDIA[1332]=array('Surandai','Tamil Nadu');
	$INDIA[1333]=array('Suriyampalayam','Tamil Nadu');
	$INDIA[1334]=array('Tenkasi','Tamil Nadu');
	$INDIA[1335]=array('Thammampatti','Tamil Nadu');
	$INDIA[1336]=array('Thanjavur','Tamil Nadu');
	$INDIA[1337]=array('Tharamangalam','Tamil Nadu');
	$INDIA[1338]=array('Tharangambadi','Tamil Nadu');
	$INDIA[1339]=array('Theni Allinagaram','Tamil Nadu');
	$INDIA[1340]=array('Thirumangalam','Tamil Nadu');
	$INDIA[1341]=array('Thirunindravur','Tamil Nadu');
	$INDIA[1342]=array('Thiruparappu','Tamil Nadu');
	$INDIA[1343]=array('Thirupuvanam','Tamil Nadu');
	$INDIA[1344]=array('Thiruthuraipoondi','Tamil Nadu');
	$INDIA[1345]=array('Thiruvallur','Tamil Nadu');
	$INDIA[1346]=array('Thiruvarur','Tamil Nadu');
	$INDIA[1347]=array('Thoothukudi','Tamil Nadu');
	$INDIA[1348]=array('Thuraiyur','Tamil Nadu');
	$INDIA[1349]=array('Tindivanam','Tamil Nadu');
	$INDIA[1350]=array('Tiruchendur','Tamil Nadu');
	$INDIA[1351]=array('Tiruchengode','Tamil Nadu');
	$INDIA[1352]=array('Tiruchirappalli','Tamil Nadu');
	$INDIA[1353]=array('Tirukalukundram','Tamil Nadu');
	$INDIA[1354]=array('Tirukkoyilur','Tamil Nadu');
	$INDIA[1355]=array('Tirunelveli','Tamil Nadu');
	$INDIA[1356]=array('Tirupathur','Tamil Nadu');
	$INDIA[1357]=array('Tirupathur','Tamil Nadu');
	$INDIA[1358]=array('Tiruppur','Tamil Nadu');
	$INDIA[1359]=array('Tiruttani','Tamil Nadu');
	$INDIA[1360]=array('Tiruvannamalai','Tamil Nadu');
	$INDIA[1361]=array('Tiruvethipuram','Tamil Nadu');
	$INDIA[1362]=array('Tittakudi','Tamil Nadu');
	$INDIA[1363]=array('Udhagamandalam','Tamil Nadu');
	$INDIA[1364]=array('Udumalaipettai','Tamil Nadu');
	$INDIA[1365]=array('Unnamalaikadai','Tamil Nadu');
	$INDIA[1366]=array('Usilampatti','Tamil Nadu');
	$INDIA[1367]=array('Uthamapalayam','Tamil Nadu');
	$INDIA[1368]=array('Uthiramerur','Tamil Nadu');
	$INDIA[1369]=array('Vadakkuvalliyur','Tamil Nadu');
	$INDIA[1370]=array('Vadalur','Tamil Nadu');
	$INDIA[1371]=array('Vadipatti','Tamil Nadu');
	$INDIA[1372]=array('Valparai','Tamil Nadu');
	$INDIA[1373]=array('Vandavasi','Tamil Nadu');
	$INDIA[1374]=array('Vaniyambadi','Tamil Nadu');
	$INDIA[1375]=array('Vedaranyam','Tamil Nadu');
	$INDIA[1376]=array('Vellakoil','Tamil Nadu');
	$INDIA[1377]=array('Vellore','Tamil Nadu');
	$INDIA[1378]=array('Vikramasingapuram','Tamil Nadu');
	$INDIA[1379]=array('Viluppuram','Tamil Nadu');
	$INDIA[1380]=array('Virudhachalam','Tamil Nadu');
	$INDIA[1381]=array('Virudhunagar','Tamil Nadu');
	$INDIA[1382]=array('Viswanatham','Tamil Nadu');
	$INDIA[1383]=array('Agartala','Tripura');
	$INDIA[1384]=array('Badharghat','Tripura');
	$INDIA[1385]=array('Dharmanagar','Tripura');
	$INDIA[1386]=array('Indranagar','Tripura');
	$INDIA[1387]=array('Jogendranagar','Tripura');
	$INDIA[1388]=array('Kailasahar','Tripura');
	$INDIA[1389]=array('Khowai','Tripura');
	$INDIA[1390]=array('Pratapgarh','Tripura');
	$INDIA[1391]=array('Udaipur','Tripura');
	$INDIA[1392]=array('Achhnera','Uttar Pradesh');
	$INDIA[1393]=array('Adari','Uttar Pradesh');
	$INDIA[1394]=array('Agra','Uttar Pradesh');
	$INDIA[1395]=array('Aligarh','Uttar Pradesh');
	$INDIA[1396]=array('Allahabad','Uttar Pradesh');
	$INDIA[1397]=array('Amroha','Uttar Pradesh');
	$INDIA[1398]=array('Azamgarh','Uttar Pradesh');
	$INDIA[1399]=array('Badaun','Uttar Pradesh');
	$INDIA[1400]=array('Bahraich','Uttar Pradesh');
	$INDIA[1401]=array('Ballia','Uttar Pradesh');
	$INDIA[1402]=array('Balrampur','Uttar Pradesh');
	$INDIA[1403]=array('Banda','Uttar Pradesh');
	$INDIA[1404]=array('Bareilly','Uttar Pradesh');
	$INDIA[1405]=array('Bharthana','Uttar Pradesh');
	$INDIA[1406]=array('Bijnaur','Uttar Pradesh');
	$INDIA[1407]=array('Budaun','Uttar Pradesh');
	$INDIA[1408]=array('Bulandshahr','Uttar Pradesh');
	$INDIA[1409]=array('Chakeri','Uttar Pradesh');
	$INDIA[1410]=array('Chandausi','Uttar Pradesh');
	$INDIA[1411]=array('Charkhari','Uttar Pradesh');
	$INDIA[1412]=array('Dadri','Uttar Pradesh');
	$INDIA[1413]=array('Deoria','Uttar Pradesh');
	$INDIA[1414]=array('Dhampur','Uttar Pradesh');
	$INDIA[1415]=array('Etah','Uttar Pradesh');
	$INDIA[1416]=array('Etawah','Uttar Pradesh');
	$INDIA[1417]=array('Faizabad','Uttar Pradesh');
	$INDIA[1418]=array('Farrukhabad','Uttar Pradesh');
	$INDIA[1419]=array('Fatehabad','Uttar Pradesh');
	$INDIA[1420]=array('Fatehgarh','Uttar Pradesh');
	$INDIA[1421]=array('Fatehpur Chaurasi','Uttar Pradesh');
	$INDIA[1422]=array('Fatehpur Sikri','Uttar Pradesh');
	$INDIA[1423]=array('Fatehpur','Uttar Pradesh');
	$INDIA[1424]=array('Fatehpur','Uttar Pradesh');
	$INDIA[1425]=array('Firozabad','Uttar Pradesh');
	$INDIA[1426]=array('Ghatampur','Uttar Pradesh');
	$INDIA[1427]=array('Ghaziabad','Uttar Pradesh');
	$INDIA[1428]=array('Ghazipur','Uttar Pradesh');
	$INDIA[1429]=array('Gorakhpur','Uttar Pradesh');
	$INDIA[1430]=array('Greater Noida','Uttar Pradesh');
	$INDIA[1431]=array('Hamirpur','Uttar Pradesh');
	$INDIA[1432]=array('Hapur','Uttar Pradesh');
	$INDIA[1433]=array('Hardoi','Uttar Pradesh');
	$INDIA[1434]=array('Hastinapur','Uttar Pradesh');
	$INDIA[1435]=array('Hathras','Uttar Pradesh');
	$INDIA[1436]=array('Jais','Uttar Pradesh');
	$INDIA[1437]=array('Jajmau','Uttar Pradesh');
	$INDIA[1438]=array('Jaunpur','Uttar Pradesh');
	$INDIA[1439]=array('Jhansi','Uttar Pradesh');
	$INDIA[1440]=array('Kalpi','Uttar Pradesh');
	$INDIA[1441]=array('Kanpur','Uttar Pradesh');
	$INDIA[1442]=array('Kheri','Uttar Pradesh');
	$INDIA[1443]=array('Kota','Uttar Pradesh');
	$INDIA[1444]=array('Kulpahar','Uttar Pradesh');
	$INDIA[1445]=array('Laharpur','Uttar Pradesh');
	$INDIA[1446]=array('Lakhimpur','Uttar Pradesh');
	$INDIA[1447]=array('Lal Gopalganj Nindaura','Uttar Pradesh');
	$INDIA[1448]=array('Lalitpur','Uttar Pradesh');
	$INDIA[1449]=array('Lalganj','Uttar Pradesh');
	$INDIA[1450]=array('Lar','Uttar Pradesh');
	$INDIA[1451]=array('Loni','Uttar Pradesh');
	$INDIA[1452]=array('Lucknow','Uttar Pradesh');
	$INDIA[1453]=array('Mahoba','Uttar Pradesh');
	$INDIA[1454]=array('Mathura','Uttar Pradesh');
	$INDIA[1455]=array('Meerut','Uttar Pradesh');
	$INDIA[1456]=array('Mirzapur','Uttar Pradesh');
	$INDIA[1457]=array('Modinagar','Uttar Pradesh');
	$INDIA[1458]=array('Moradabad','Uttar Pradesh');
	$INDIA[1459]=array('Muradnagar','Uttar Pradesh');
	$INDIA[1460]=array('Muzaffarnagar','Uttar Pradesh');
	$INDIA[1461]=array('Nagina','Uttar Pradesh');
	$INDIA[1462]=array('Najibabad','Uttar Pradesh');
	$INDIA[1463]=array('Nakur','Uttar Pradesh');
	$INDIA[1464]=array('Nanpara','Uttar Pradesh');
	$INDIA[1465]=array('Naraura','Uttar Pradesh');
	$INDIA[1466]=array('Naugawan Sadat','Uttar Pradesh');
	$INDIA[1467]=array('Nautanwa','Uttar Pradesh');
	$INDIA[1468]=array('Nawabganj','Uttar Pradesh');
	$INDIA[1469]=array('Nehtaur','Uttar Pradesh');
	$INDIA[1470]=array('Noida','Uttar Pradesh');
	$INDIA[1471]=array('Noorpur','Uttar Pradesh');
	$INDIA[1472]=array('Obra','Uttar Pradesh');
	$INDIA[1473]=array('Orai','Uttar Pradesh');
	$INDIA[1474]=array('Padrauna','Uttar Pradesh');
	$INDIA[1475]=array('Palia Kalan','Uttar Pradesh');
	$INDIA[1476]=array('Parasi','Uttar Pradesh');
	$INDIA[1477]=array('Phulpur','Uttar Pradesh');
	$INDIA[1478]=array('Pihani','Uttar Pradesh');
	$INDIA[1479]=array('Pilibhit','Uttar Pradesh');
	$INDIA[1480]=array('Pilkhuwa','Uttar Pradesh');
	$INDIA[1481]=array('Powayan','Uttar Pradesh');
	$INDIA[1482]=array('Pukhrayan','Uttar Pradesh');
	$INDIA[1483]=array('Puranpur','Uttar Pradesh');
	$INDIA[1484]=array('Purquazi','Uttar Pradesh');
	$INDIA[1485]=array('Purwa','Uttar Pradesh');
	$INDIA[1486]=array('Rae Bareli','Uttar Pradesh');
	$INDIA[1487]=array('Rampur','Uttar Pradesh');
	$INDIA[1488]=array('Rampur Maniharan','Uttar Pradesh');
	$INDIA[1489]=array('Rasra','Uttar Pradesh');
	$INDIA[1490]=array('Rath','Uttar Pradesh');
	$INDIA[1491]=array('Renukoot','Uttar Pradesh');
	$INDIA[1492]=array('Reoti','Uttar Pradesh');
	$INDIA[1493]=array('Robertsganj','Uttar Pradesh');
	$INDIA[1494]=array('Rudauli','Uttar Pradesh');
	$INDIA[1495]=array('Rudrapur','Uttar Pradesh');
	$INDIA[1496]=array('Sadabad','Uttar Pradesh');
	$INDIA[1497]=array('Safipur','Uttar Pradesh');
	$INDIA[1498]=array('Saharanpur','Uttar Pradesh');
	$INDIA[1499]=array('Sahaspur','Uttar Pradesh');
	$INDIA[1500]=array('Sahaswan','Uttar Pradesh');
	$INDIA[1501]=array('Sahawar','Uttar Pradesh');
	$INDIA[1502]=array('Sahjanwa','Uttar Pradesh');
	$INDIA[1503]=array('Saidpur','Uttar Pradesh');
	$INDIA[1504]=array('Sambhal','Uttar Pradesh');
	$INDIA[1505]=array('Samdhan','Uttar Pradesh');
	$INDIA[1506]=array('Samthar','Uttar Pradesh');
	$INDIA[1507]=array('Sandi','Uttar Pradesh');
	$INDIA[1508]=array('Sandila','Uttar Pradesh');
	$INDIA[1509]=array('Sardhana','Uttar Pradesh');
	$INDIA[1510]=array('Seohara','Uttar Pradesh');
	$INDIA[1511]=array('Shahabad, Hardoi','Uttar Pradesh');
	$INDIA[1512]=array('Shahabad, Rampur','Uttar Pradesh');
	$INDIA[1513]=array('Shahganj','Uttar Pradesh');
	$INDIA[1514]=array('Shahjahanpur','Uttar Pradesh');
	$INDIA[1515]=array('Shamli','Uttar Pradesh');
	$INDIA[1516]=array('Shamsabad, Agra','Uttar Pradesh');
	$INDIA[1517]=array('Shamsabad, Farrukhabad','Uttar Pradesh');
	$INDIA[1518]=array('Sherkot','Uttar Pradesh');
	$INDIA[1519]=array('Shikarpur, Bulandshahr','Uttar Pradesh');
	$INDIA[1520]=array('Shikohabad','Uttar Pradesh');
	$INDIA[1521]=array('Shishgarh','Uttar Pradesh');
	$INDIA[1522]=array('Siana','Uttar Pradesh');
	$INDIA[1523]=array('Sikanderpur','Uttar Pradesh');
	$INDIA[1524]=array('Sikandra Rao','Uttar Pradesh');
	$INDIA[1525]=array('Sikandrabad','Uttar Pradesh');
	$INDIA[1526]=array('Sirsaganj','Uttar Pradesh');
	$INDIA[1527]=array('Sirsi','Uttar Pradesh');
	$INDIA[1528]=array('Sitapur','Uttar Pradesh');
	$INDIA[1529]=array('Soron','Uttar Pradesh');
	$INDIA[1530]=array('Suar','Uttar Pradesh');
	$INDIA[1531]=array('Sultanpur','Uttar Pradesh');
	$INDIA[1532]=array('Sumerpur','Uttar Pradesh');
	$INDIA[1533]=array('Tanda','Uttar Pradesh');
	$INDIA[1534]=array('Tanda','Uttar Pradesh');
	$INDIA[1535]=array('Tetri Bazar','Uttar Pradesh');
	$INDIA[1536]=array('Thakurdwara','Uttar Pradesh');
	$INDIA[1537]=array('Thana Bhawan','Uttar Pradesh');
	$INDIA[1538]=array('Tilhar','Uttar Pradesh');
	$INDIA[1539]=array('Tirwaganj','Uttar Pradesh');
	$INDIA[1540]=array('Tulsipur','Uttar Pradesh');
	$INDIA[1541]=array('Tundla','Uttar Pradesh');
	$INDIA[1542]=array('Unnao','Uttar Pradesh');
	$INDIA[1543]=array('Utraula','Uttar Pradesh');
	$INDIA[1544]=array('Varanasi','Uttar Pradesh');
	$INDIA[1545]=array('Vrindavan','Uttar Pradesh');
	$INDIA[1546]=array('Warhapur','Uttar Pradesh');
	$INDIA[1547]=array('Zaidpur','Uttar Pradesh');
	$INDIA[1548]=array('Zamania','Uttar Pradesh');
	$INDIA[1549]=array('Almora','Uttarakhand');
	$INDIA[1550]=array('Bageshwar','Uttarakhand');
	$INDIA[1551]=array('Bazpur','Uttarakhand');
	$INDIA[1552]=array('Berinag','Uttarakhand');
	$INDIA[1553]=array('Chamba','Uttarakhand');
	$INDIA[1554]=array('Champawat','Uttarakhand');
	$INDIA[1555]=array('Chaukori','Uttarakhand');
	$INDIA[1556]=array('Dehradun','Uttarakhand');
	$INDIA[1557]=array('Haldwani','Uttarakhand');
	$INDIA[1558]=array('Haridwar','Uttarakhand');
	$INDIA[1559]=array('Jaspur','Uttarakhand');
	$INDIA[1560]=array('Kanda','Uttarakhand');
	$INDIA[1561]=array('Kashipur','Uttarakhand');
	$INDIA[1562]=array('kichha','Uttarakhand');
	$INDIA[1563]=array('Kotdwara','Uttarakhand');
	$INDIA[1564]=array('Manglaur','Uttarakhand');
	$INDIA[1565]=array('Mussoorie','Uttarakhand');
	$INDIA[1566]=array('Nagla','Uttarakhand');
	$INDIA[1567]=array('Nainital','Uttarakhand');
	$INDIA[1568]=array('Pauri','Uttarakhand');
	$INDIA[1569]=array('Pithoragarh','Uttarakhand');
	$INDIA[1570]=array('Ramnagar','Uttarakhand');
	$INDIA[1571]=array('Rishikesh','Uttarakhand');
	$INDIA[1572]=array('Roorkee','Uttarakhand');
	$INDIA[1573]=array('Rudrapur','Uttarakhand');
	$INDIA[1574]=array('Sitarganj','Uttarakhand');
	$INDIA[1575]=array('Tehri','Uttarakhand');
	$INDIA[1576]=array('Vijaypur','Uttarakhand');
	$INDIA[1577]=array('Adra','West Bengal');
	$INDIA[1578]=array('Alipurduar','West Bengal');
	$INDIA[1579]=array('Arambagh','West Bengal');
	$INDIA[1580]=array('Asansol','West Bengal');
	$INDIA[1581]=array('Baharampur','West Bengal');
	$INDIA[1582]=array('Bally','West Bengal');
	$INDIA[1583]=array('Balurghat','West Bengal');
	$INDIA[1584]=array('Bankura','West Bengal');
	$INDIA[1585]=array('Barakar','West Bengal');
	$INDIA[1586]=array('Barasat','West Bengal');
	$INDIA[1587]=array('Bardhaman','West Bengal');
	$INDIA[1588]=array('Barrackpur','West Bengal');
	$INDIA[1589]=array('Bidhan Nagar','West Bengal');
	$INDIA[1590]=array('Chinsura','West Bengal');
	$INDIA[1591]=array('Contai','West Bengal');
	$INDIA[1592]=array('Cooch Behar','West Bengal');
	$INDIA[1593]=array('Dalkhola','West Bengal');
	$INDIA[1594]=array('Darjeeling','West Bengal');
	$INDIA[1595]=array('Dhulian','West Bengal');
	$INDIA[1596]=array('Dumdum','West Bengal');
	$INDIA[1597]=array('Durgapur','West Bengal');
	$INDIA[1598]=array('Haldia','West Bengal');
	$INDIA[1599]=array('Howrah','West Bengal');
	$INDIA[1600]=array('Hugli-Chuchura','West Bengal');
	$INDIA[1601]=array('Habra','West Bengal');
	$INDIA[1602]=array('Islampur','West Bengal');
	$INDIA[1603]=array('Jalpaiguri','West Bengal');
	$INDIA[1604]=array('Jhargram','West Bengal');
	$INDIA[1605]=array('Kalimpong','West Bengal');
	$INDIA[1606]=array('Kharagpur','West Bengal');
	$INDIA[1607]=array('Kolkata','West Bengal');
	$INDIA[1608]=array('Konnagar','West Bengal');
	$INDIA[1609]=array('Krishnanagar','West Bengal');
	$INDIA[1610]=array('Mainaguri','West Bengal');
	$INDIA[1611]=array('Mal','West Bengal');
	$INDIA[1612]=array('Mathabhanga','West Bengal');
	$INDIA[1613]=array('Medinipur','West Bengal');
	$INDIA[1614]=array('Memari','West Bengal');
	$INDIA[1615]=array('Monoharpur','West Bengal');
	$INDIA[1616]=array('Murshidabad','West Bengal');
	$INDIA[1617]=array('Nabadwip','West Bengal');
	$INDIA[1618]=array('Naihati','West Bengal');
	$INDIA[1619]=array('Panchla','West Bengal');
	$INDIA[1620]=array('Pandua','West Bengal');
	$INDIA[1621]=array('Paschim Punropara','West Bengal');
	$INDIA[1622]=array('Purulia','West Bengal');
	$INDIA[1623]=array('Raghunathpur','West Bengal');
	$INDIA[1624]=array('Raghunathganj','West Bengal');
	$INDIA[1625]=array('Raiganj','West Bengal');
	$INDIA[1626]=array('Rampurhat','West Bengal');
	$INDIA[1627]=array('Ranaghat','West Bengal');
	$INDIA[1628]=array('Sainthia','West Bengal');
	$INDIA[1629]=array('Santipur','West Bengal');
	$INDIA[1630]=array('Siliguri','West Bengal');
	$INDIA[1631]=array('Sonamukhi','West Bengal');
	$INDIA[1632]=array('Srirampore','West Bengal');
	$INDIA[1633]=array('Suri','West Bengal');
	$INDIA[1634]=array('Taki','West Bengal');
	$INDIA[1635]=array('Tamluk','West Bengal');
	$INDIA[1636]=array('Tarakeswar','West Bengal');


	/* Delete all existing details
	$resp=execSQL("DELETE FROM tState",array(),true);
	if ( $resp['STATUS'] != 'OK' ) {
		echo "\nError Deleting rows from tState\n".print_arr($resp);
		exit;
	}
	echo "Deleted all States; \n"; */

	echo "Optimising tState...\n";
        $resp=execSQL("ALTER TABLE tState ENGINE = InnoDB;",array(),true);
        if ($resp['STATUS'] != 'OK') {
              echo "ERROR: tState alter"; print_arr($resp);
        }
	echo "Optimising tCity...\n";
        $resp=execSQL("ALTER TABLE tCity ENGINE = InnoDB;",array(),true);
        if ($resp['STATUS'] != 'OK') {
              echo "ERROR: tCity alter"; print_arr($resp);
        }

	$SHOP_ROW=execSQL("SELECT shop_id from tShop where shop_id != -1;",array(),false);
	if ( $SHOP_ROW[0]['STATUS'] != 'OK' ) {
		echo "\nError fetching shop_ids \n".print_arr($SHOP_ROW);
		exit;
	}
	echo "Got [".$SHOP_ROW[0]['NROWS']."] shops... \n";

	for ($idx=0;$idx<$SHOP_ROW[0]['NROWS'];$idx++) {

		$MY_SHOP_ID=$SHOP_ROW[$idx]['shop_id'];
		echo "============= INSERTING for SHOP [$MY_SHOP_ID]=========\n";
		//wait();

		// Insert for that shop
		$prev_state='';
		$j=1;
		for ( $i=0; $i<count($INDIA);$i++ ) {
			if (!isset($INDIA[$i])) continue;
			$city=$INDIA[$i][0];
			$state=$INDIA[$i][1];
			LOG_MSG('INFO',"STATE CITY START state=[$state] city=[$city]");
			//echo "Inserting state=[$state] city=[$city] for shop[$MY_SHOP_ID]...";
			if ( $prev_state != $state ) {
				LOG_MSG('INFO',"prev_state=[$prev_state] state=[$state] city=[$city]");
				$resp=execSQL("	INSERT INTO 
									tState 
								VALUES
									('','$state',1,$MY_SHOP_ID)
								ON DUPLICATE KEY UPDATE 
									state_id=LAST_INSERT_ID(state_id)"
								,array(), 
								true);
				if ( $resp['STATUS'] != 'OK' ) {
					echo "\nError inserting state [$state]".print_arr($resp);
					exit;
				}
				$state_id=$resp['INSERT_ID'];
			}

			$resp=execSQL("	INSERT INTO 
									tCity
								VALUES
									('','$city',$state_id,1,$MY_SHOP_ID)
								ON DUPLICATE KEY UPDATE 
									city_id=LAST_INSERT_ID(city_id)"
								,array(), 
								true);
				if ( $resp['STATUS'] != 'OK' ) {
					echo "\nError inserting city [$city] for state_id [$state_id] ".print_arr($resp);
					exit;
				}

			$prev_state=$state;
			LOG_MSG('INFO',"STATE CITY END state=[$state] city=[$city]");
			//echo " DONE\n";
			//wait();
		}	// end state-city array loop
	}	// end shop loop
}



function do_populate_tag_clean_value() {
        // Get Tags
        $TAG_ROW=execSQL("SELECT tag_id,tag,value,shop_id FROM tTag",array(),false);
        if ( $TAG_ROW[0]['STATUS'] != "OK" ) {
                echo "Error fetching tags\n";
                exit;
        }
        echo "Got [".$TAG_ROW[0]['NROWS']."] Tags from tTag\n";

       // update the row with the clean url
        for ($i=0;$i<$TAG_ROW[0]['NROWS'];$i++) {
                                $clean_value=make_clean_url($TAG_ROW[$i]['value']);
                                $tag_id=$TAG_ROW[$i]['tag_id'];
                echo "Processing shop_id=[".$TAG_ROW[$i]['shop_id']."] tag_id=[$tag_id] tag=[".$TAG_ROW[$i]['tag']."] value=[".$TAG_ROW[$i]['value']."]: clean_value=[$clean_value]\n";

                // Insert Tag for the category
                $resp=execSQL("UPDATE tTag SET clean_value='$clean_value' WHERE tag_id = $tag_id",array(),true);
                if ( $resp['STATUS'] != "OK" ) {
                        echo "     ERROR UPDATING TAGS!!\n";
                        exit;
                }
                //echo "Updated Category with tag_id [$tag_id]\n";
        }
}


function do_type_tags_update() {

	echo "----------TYPES---------\n";


	// delete Type tags from tTag 
	$resp=execSQL("DELETE FROM tTag WHERE tag='Type'",array(),true);
	if ( $resp['STATUS'] != "OK" ) {
		echo "ERROR DELETING Type tags FROM tTag\n";
		exit;
	}
	echo "Deleted All Type tags from tTag\n";


	// Get types
	$TYPE_ROW=execSQL("SELECT type_id,'Type' AS tag,type AS value,shop_id FROM tType",array(),false);
	if ( $TYPE_ROW[0]['STATUS'] != "OK" ) {
		echo "Error fetching types\n";
		exit;
	}
	echo "Got [".$TYPE_ROW[0]['NROWS']."] Types from tType\n";

	// Insert tags for each type
	for ($i=0;$i<$TYPE_ROW[0]['NROWS'];$i++) {
		echo "Processing Type[".$TYPE_ROW[$i]['type_id']."][".$TYPE_ROW[$i]['value']."]:       ";

		// Insert Tag for the type
		$resp=execSQL("INSERT INTO tTag (tag,value,shop_id) VALUES ('Type','".$TYPE_ROW[$i]['value']."',".$TYPE_ROW[$i]['shop_id'].")",array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR INSERTING TAGS FOR TYPE [".$TYPE_ROW[$i]['value']."]\n";
			exit;
		}
		$tag_id=$resp['INSERT_ID'];
		echo "Inserted into Tag [$tag_id]       ";

		// Update the tag_id in tType table
		$resp=execSQL("UPDATE tType set tag_id=$tag_id where type_id=".$TYPE_ROW[$i]['type_id'],array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR UPDATING tag_id [$tag_id] for type [".$TYPE_ROW[$i]['value']."]\n";
			exit;
		}
		echo "Updated Type with tag_id [$tag_id]\n";

	}

}


function do_category_tags_update2() {

	echo "----------CATEGORYS---------\n";
	
	//For each category in tCategory
		//select tag_id from tTag where shop_id = THIS_SHOP_ID and tag_id NOT in (select tag_id from tCategory)
		//if not found
		//	insert tag into tTag
		//update tCategory set tag_id = $tag_id

	// Get categorys
	$CATEGORY_ROW=execSQL("SELECT category_id,category,shop_id FROM tCategory",array(),false);
	if ( $CATEGORY_ROW[0]['STATUS'] != "OK" ) {
		echo "Error fetching categorys\n";
		exit;
	}
	echo "Got [".$CATEGORY_ROW[0]['NROWS']."] Categorys from tCategory\n";


	// foreach category
	for ($i=0;$i<$CATEGORY_ROW[0]['NROWS'];$i++) {
		$category_id=$CATEGORY_ROW[$i]['category_id'];
		$category=$CATEGORY_ROW[$i]['category'];
		$shop_id=$CATEGORY_ROW[$i]['shop_id'];

		// See if we have a free tag id in tTag
		$resp=execSQL("SELECT tag_id FROM tTag where shop_id = $shop_id and tag_id not in (select tag_id from tCategory)",array(),false);
		if ( $resp[0]['STATUS'] != "OK" ) {
			echo "Error fetching tags for category [$category]\n";
			exit;
		}

		// if no tag id free - creating one
		if ($resp[0]['STATUS'] !== 0 ) {
			$tag_id=$resp[0]['tag_id'];
			echo "Got free Tag [$tag_id]\n";
		} else {
			$resp=execSQL("INSERT INTO tTag (tag,value,shop_id) VALUES ('Category','$category',$shop_id)",array(),true);
			if ( $resp['STATUS'] != "OK" ) {
				echo "ERROR INSERTING TAGS FOR CATEGORY [$category]\n";
				exit;
			}
			$tag_id=$resp['INSERT_ID'];
			echo "Inserted new tag [$tag_id]\n";
		}

		// Update the tag_id in tCategory table
		$resp=execSQL("UPDATE tCategory set tag_id=$tag_id where category_id=$category_id",array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR UPDATING tag_id [$tag_id] for category [$category_id]\n";
			exit;
		}
		echo "Updated Category [$category] with tag_id [$tag_id]\n";
	}

}



















function do_category_tags_update() {

	echo "----------CATEGORYS---------\n";

	// delete Category tags from tTag 
	$resp=execSQL("DELETE FROM tTag WHERE tag='Category'",array(),true);
	if ( $resp['STATUS'] != "OK" ) {
		echo "ERROR DELETING Category tags FROM tTag\n";
		exit;
	}
	echo "Deleted All Category tags from tTag\n";


	// Get categorys
	$CATEGORY_ROW=execSQL("SELECT category_id,'Category' AS tag,category AS value,shop_id FROM tCategory",array(),false);
	if ( $CATEGORY_ROW[0]['STATUS'] != "OK" ) {
		echo "Error fetching categorys\n";
		exit;
	}
	echo "Got [".$CATEGORY_ROW[0]['NROWS']."] Categorys from tCategory\n";

	// Insert tags for each category
	for ($i=0;$i<$CATEGORY_ROW[0]['NROWS'];$i++) {
		echo "Processing Category[".$CATEGORY_ROW[$i]['category_id']."][".$CATEGORY_ROW[$i]['value']."]:       ";

		// Insert Tag for the category
		$resp=execSQL("INSERT INTO tTag (tag,value,shop_id) VALUES ('Category','".$CATEGORY_ROW[$i]['value']."',".$CATEGORY_ROW[$i]['shop_id'].")",array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR INSERTING TAGS FOR CATEGORY [".$CATEGORY_ROW[$i]['value']."]\n";
			exit;
		}
		$tag_id=$resp['INSERT_ID'];
		echo "Inserted into Tag [$tag_id]       ";

		// Update the tag_id in tCategory table
		$resp=execSQL("UPDATE tCategory set tag_id=$tag_id where category_id=".$CATEGORY_ROW[$i]['category_id'],array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR UPDATING tag_id [$tag_id] for category [".$CATEGORY_ROW[$i]['value']."]\n";
			exit;
		}
		echo "Updated Category with tag_id [$tag_id]\n";

	}

}


function do_subcategory_tags_update() {

	echo "----------SUB CATEGORYS---------\n";

	// delete SubCategory tags from tTag 
	$resp=execSQL("DELETE FROM tTag WHERE tag='SubCategory'",array(),true);
	if ( $resp['STATUS'] != "OK" ) {
		echo "ERROR DELETING SubCategory tags FROM tTag\n";
		exit;
	}
	echo "Deleted All SubCategory tags from tTag\n";


	// Get subcategorys
	$CATEGORY_ROW=execSQL("SELECT subcategory_id,'SubCategory' AS tag,subcategory AS value,shop_id FROM tSubCategory",array(),false);
	if ( $CATEGORY_ROW[0]['STATUS'] != "OK" ) {
		echo "Error fetching subcategorys\n";
		exit;
	}
	echo "Got [".$CATEGORY_ROW[0]['NROWS']."] SubCategorys from tSubCategory\n";

	// Insert tags for each subcategory
	for ($i=0;$i<$CATEGORY_ROW[0]['NROWS'];$i++) {
		echo "Processing SubCategory[".$CATEGORY_ROW[$i]['subcategory_id']."][".$CATEGORY_ROW[$i]['value']."]:       ";

		// Insert Tag for the subcategory
		$resp=execSQL("INSERT INTO tTag (tag,value,shop_id) VALUES ('SubCategory','".$CATEGORY_ROW[$i]['value']."',".$CATEGORY_ROW[$i]['shop_id'].")",array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR INSERTING TAGS FOR CATEGORY [".$CATEGORY_ROW[$i]['value']."]\n";
			exit;
		}
		$tag_id=$resp['INSERT_ID'];
		echo "Inserted into Tag [$tag_id]       ";

		// Update the tag_id in tSubCategory table
		$resp=execSQL("UPDATE tSubCategory set tag_id=$tag_id where subcategory_id=".$CATEGORY_ROW[$i]['subcategory_id'],array(),true);
		if ( $resp['STATUS'] != "OK" ) {
			echo "ERROR UPDATING tag_id [$tag_id] for subcategory [".$CATEGORY_ROW[$i]['value']."]\n";
			exit;
		}
		echo "Updated SubCategory with tag_id [$tag_id]\n";

	}

}












?>
