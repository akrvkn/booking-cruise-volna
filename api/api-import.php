<?php
$executionStartTime = microtime(true);

$sdir = dirname(__FILE__);

//system ( 'cd '.$sdir.'; git pull;' );

$RESTAPI_TOKEN = '407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$ships = $API_BASE.'Ships/';
$cabins = $API_BASE.'Cabins/';
$cabins_photo = $API_BASE.'CabinsPhoto/';
$shipsimages = $API_BASE.'ShipsImages/';
$desc = $API_BASE.'ShipsDescription/';
$tours_base = $API_BASE.'Tours/';
$schemes = $API_BASE.'ShipsSchemes/';
$excursions = $API_BASE.'Excursions/';
$now = date('Y-m-d');
$cruises_base = 'https://restapi.infoflot.com/cruises?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b&dateStartFrom='.$now.'&ship=';
$ship_single = 'https://restapi.infoflot.com/ships?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$cruise_single = 'https://restapi.infoflot.com/cruises/';

//Vodohod.com API v2
//https://api.vodohod.com/json/v2/cruises.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7
$VDH_BASE = 'https://api.vodohod.com/json/v2/';
$pauth = 'v2-ba9fab12d2c4b8d005645d04492a7af7';
$vcruises = $VDH_BASE.'cruises.php?pauth='.$pauth;
$vcruise_prices = $VDH_BASE.'cruise-prices.php?pauth='.$pauth.'&cruise=';
$vcruise_days = $VDH_BASE.'cruise-days.php?pauth='.$pauth.'&cruise=';
$motorships = $VDH_BASE.'motorships.php?pauth='.$pauth;
$motorship_rooms = $VDH_BASE.'motorship-rooms.php?pauth='.$pauth.'&motorship=';
$vports = $VDH_BASE.'ports.php?pauth='.$pauth;
$vdecks = $VDH_BASE.'decks.php?pauth='.$pauth;
$vroom_types = $VDH_BASE.'room-types.php?pauth='.$pauth;

//MOSTURFLOT
$mtf_ships_url = 'https://api.mosturflot.ru/v3/rivercruises/ships?filter[is-own]=1';
$mtf_images_base = 'https://api.mosturflot.ru/v3/rivercruises/ships/';


$opts = array(
'http' => array('ignore_errors' => true),
    'ssl' => array(
'verify_peer' => false,
'verify_peer_name' => false
)
);

$context = stream_context_create($opts);


$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

$dir = 'data';
if(is_dir('data')){
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
}
rmdir($dir);
}

$base_dir = 'data';
if(!is_dir($base_dir)){
		mkdir($base_dir);
	}


$mtf_ships_list = json_decode(file_get_contents($mtf_ships_url));
$mtf_images = [];
$mtf_names = [];
$mtf_titles = [];

foreach($mtf_ships_list->data as $key=>$mtf_ship){
	$img_url = $mtf_images_base.$mtf_ship->id.'/images';
	$ship_images = json_decode(file_get_contents($img_url), true);
	$mtf_images[$mtf_ship->id] = $ship_images['data'][0]['links']['preview-url'];
	$mtf_names[$mtf_ship->id] = $mtf_ship->attributes->name;
	$mtf_titles[] = str_replace(' ', '', mb_strtolower($mtf_ship->attributes->name));
}

file_put_contents('mtfimages.json', json_encode($mtf_images));
file_put_contents('mtfships.json', json_encode($mtf_names));

$vdh_ships_list = json_decode(file_get_contents($motorships, false, $context), true);
$vdh_arr = [];
foreach($vdh_ships_list as $v_ship) {
	$vdh_arr[] = $v_ship['name'];
}



//Infoflot ships
$ships_list = json_decode(file_get_contents($ships), true);
$filtered_ships = array();
$filtered_id = [];
if($ships_list){
	foreach($ships_list as $ship_id=>$ship_name){
		if(!in_array(str_replace(' ', '', mb_strtolower($ship_name)), $mtf_titles)&& preg_match($pattern, $ship_name) && !in_array($ship_name, $vdh_arr)){
		//echo mb_strtolower($ship_name)."\n";
			$filtered_ships[$ship_id]=$ship_name;
			$filtered_id[] = $ship_id;
		}
	}
}

if(count($filtered_ships)>0){
	file_put_contents('ships.json', json_encode($filtered_ships));
}

$ships_img = @file_get_contents($shipsimages);
if($ships_img){
	file_put_contents('shipsimages.json', $ships_img);
}
$schemes_list = @file_get_contents($schemes);
if($schemes_list){
	file_put_contents('schemes.json', $schemes_list);
}

$counter = 0;
foreach($filtered_ships as $ship_id=>$ship_name){
			if(!is_dir($base_dir.'/'.$ship_id)){
				mkdir($base_dir.'/'.$ship_id);
			}
		    $counter++;
		    if($counter == 90){
						    $counter = 0;
						    sleep(60);
						}
			$ship_desc = file_get_contents($ship_single.$ship_id.'?key='.$RESTAPI_TOKEN, false, $context);
			file_put_contents($base_dir.'/'.$ship_id.'/ship.json', $ship_desc);
		    $ship_tours = json_decode(file_get_contents($cruises_base.$ship_id, false, $context));
		    if(isset($ship_tours->data)){
			    file_put_contents($base_dir.'/'.$ship_id.'/tours.json', json_encode($ship_tours->data));
		    }
}

file_put_contents('ships.json', json_encode($filtered_ships));

$counter = 0;
$table = array();

$iff_url = $cruises_base.implode(',' , $filtered_id);
$tours = json_decode(file_get_contents($iff_url), true);
$pages = $tours['pagination']['pages']['total'];

if($tours){
	if($counter == 90){
		sleep(60);
	}
	foreach($tours['data'] as $tour_id=>$tour){
		$table[$counter]['company'] = 'iff';
		$table[$counter]['shipid'] = $tour['ship']['id'];
		$table[$counter]['shipname'] = $tour['ship']['name'];
		$table[$counter]['tourid'] = $tour['id'];
		$table[$counter]['tourstart'] = $tour['dateStart'];
		$table[$counter]['tourfinish'] = $tour['dateEnd'];
		$table[$counter]['tourroute'] = trim(preg_replace('|\(.*\)|sU', '', $tour['route']));
		$table[$counter]['tourdays'] = $tour['days'];
		$table[$counter]['tourminprice'] = $tour['min_price'];
		$table[$counter]['tourcabinsfree'] = $tour['freeCabins'];

		$counter++;
	}
}

$count = 0;

for($i = 2; $i < $pages; $i++){
	$iff_url_page = $iff_url.'&page='.$i;
	$page_tours = json_decode(file_get_contents($iff_url_page), true);
	$count++;
	if($count == 90){
		$count = 0;
		sleep(60);
	}

	foreach($page_tours['data'] as $tour){
		$table[$counter]['company'] = 'iff';
		$table[$counter]['shipid'] = $tour['ship']['id'];
		$table[$counter]['shipname'] = $tour['ship']['name'];
		$table[$counter]['tourid'] = $tour['id'];
		$table[$counter]['tourstart'] = $tour['dateStart'];
		$table[$counter]['tourfinish'] = $tour['dateEnd'];
		$table[$counter]['tourroute'] = trim(preg_replace('|\(.*\)|sU', '', $tour['route']));
		$table[$counter]['tourdays'] = $tour['days'];
		$table[$counter]['tourminprice'] = $tour['min_price'];
		$table[$counter]['tourcabinsfree'] = $tour['freeCabins'];

		$counter++;
	}
}


/**$filterMtf = '&filter[ship-id][in][]=5&filter[ship-id][in][]=14&filter[ship-id][in][]=19&filter[ship-id][in][]=36&filter[ship-id][in][]=72&filter[ship-id][in][]=91&filter[ship-id][in][]=92&filter[ship-id][in][]=139&filter[ship-id][in][]=150&filter[ship-id][in][]=198&filter[ship-id][in][]=200&filter[ship-id][in][]=206&filter[ship-id][in][]=207&filter[ship-id][in][]=247';

$mtf_cruises = json_decode(file_get_contents('https://api.mosturflot.ru/v3/rivercruises/tours?filter[start][gte]='.date("Y-m-d").'T00:00:00Z'.$filterMtf.'&per-page=1000'), true);

foreach($mtf_cruises['data'] as $key=>$val){
	$table[$counter]['company'] = 'mtf';
	$table[$counter]['shipid'] = $val['attributes']['ship-id'];
	$table[$counter]['shipname'] = $mtf_names[$val['attributes']['ship-id']];
	$table[$counter]['tourid'] = $val['id'];
	$table[$counter]['tourstart'] = $val['attributes']['start'];
	$table[$counter]['tourfinish'] = $val['attributes']['finish'];
	$table[$counter]['tourroute'] = $val['attributes']['route'];
	$table[$counter]['tourdays'] = $val['attributes']['days'];
	$table[$counter]['tourminprice'] = $val['attributes']['price-from'];
	$table[$counter]['tourcabinsfree'] = '';

	$counter++;
}*/

/**$vdh_cruises = file_get_contents($vcruises, false, $context);
file_put_contents('vodohod.json', $vdh_cruises);

foreach($vdh_ships_list as $id=>$vdh_ship){
	$vdh_ship_cruises = json_decode(file_get_contents($vcruises.'&motorships='.$id, false, $context), true);

	foreach($vdh_ship_cruises as $cruise_id=>$vdh_ship_cruise){
		$table[$counter]['company'] = 'vdh';
		$table[$counter]['shipid'] = $vdh_ship_cruise['motorshipId'];
		$table[$counter]['shipname'] = $vdh_ship_cruise['motorshipName'];
		$table[$counter]['tourid'] = $cruise_id;
		$table[$counter]['tourstart'] = $vdh_ship_cruise['dateStart'];
		$table[$counter]['tourfinish'] = $vdh_ship_cruise['dateStop'];
		$table[$counter]['tourroute'] = $vdh_ship_cruise['name'];
		$table[$counter]['tourdays'] = $vdh_ship_cruise['days'];
		$table[$counter]['tourminprice'] = $vdh_ship_cruise['priceMin'];
		$table[$counter]['tourcabinsfree'] = $vdh_ship_cruise['availabilityCount'];

		$counter++;
	}
}*/

$vdh_dir = './data/vdh/';
if(!is_dir($vdh_dir)){
	mkdir($vdh_dir, 0777, true);
}

$vdh_ships_list = file_get_contents($motorships, false, $context);
file_put_contents($vdh_dir.'/motorships.json', $vdh_ships_list);

$vdh_cruises = file_get_contents($vcruises, false, $context);
file_put_contents($vdh_dir.'/cruises.json', $vdh_cruises);

$vdh_ships = json_decode($vdh_ships_list, true);

foreach($vdh_ships as $id=>$vdh_ship){
	$vdh_ship_dir = $vdh_dir.$id;
	if(!is_dir($vdh_ship_dir)){
		mkdir($vdh_ship_dir);
	}
	$vdh_ship_cruises = json_decode(file_get_contents($vcruises.'&motorships='.$id, false, $context), true);

	foreach($vdh_ship_cruises as $cruise_id=>$vdh_ship_cruise){
		$vdh_cruise_prices = json_decode(file_get_contents($vcruise_prices.$cruise_id, false, $context), true);
		$vdh_cruise_days = json_decode(file_get_contents($vcruise_days.$cruise_id, false, $context), true);


		foreach($vdh_cruise_prices['room_availability'] as $r_key=>$room_free){
			$vdh_cruise_prices['tariffs'][0]['prices'][$r_key]['available'] = count($room_free);
		}

		$vdh_ship_cruise['tariffs'] = $vdh_cruise_prices['tariffs'];
		$vdh_ship_cruise['routeDays'] = $vdh_cruise_days;

		file_put_contents($vdh_ship_dir.'/'.$cruise_id.'.json', json_encode($vdh_ship_cruise));

		$table[$counter]['company'] = 'vdh';
		$table[$counter]['shipid'] = $vdh_ship_cruise['motorshipId'];
		$table[$counter]['shipname'] = $vdh_ship_cruise['motorshipName'];
		$table[$counter]['tourid'] = $cruise_id;
		$table[$counter]['tourstart'] = $vdh_ship_cruise['dateStart'];
		$table[$counter]['tourfinish'] = $vdh_ship_cruise['dateStop'];
		$table[$counter]['tourroute'] = $vdh_ship_cruise['name'];
		$table[$counter]['tourdays'] = $vdh_ship_cruise['days'];
		$table[$counter]['tourminprice'] = $vdh_ship_cruise['priceMin'];
		$table[$counter]['tourcabinsfree'] = $vdh_ship_cruise['availabilityCount'];

	$counter++;
}


file_put_contents($vdh_ship_dir.'/cruises.json', json_encode($vdh_ship_cruises));
}

file_put_contents($vdh_dir.'/cruises.json', json_encode($table));

//End Vodohod.com API v2


usort($table, function($a, $b){
    return $a['tourstart'] <=> $b['tourstart'];
});

file_put_contents('cruises.json', json_encode($table));


$executionEndTime = microtime(true);
$seconds = ($executionEndTime - $executionStartTime)/60;
//echo 'Выполнено за '.$seconds.' сек.'.PHP_EOL;
file_put_contents('api.log', 'Выполнено за '.$seconds.' min.');

$sdate = date('Y-m-d H:i');

?>
