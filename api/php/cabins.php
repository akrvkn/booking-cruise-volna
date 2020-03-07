<?php
/*$executionStartTime = microtime(true);
ini_set("date.timezone", "Europe/Moscow");

$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$desc = $API_BASE.'ShipsDescription/';
$cabins = $API_BASE.'Cabins/';
$cabins_photo = $API_BASE.'CabinsPhoto/';
//$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

$base_dir = 'data';


$ships_list = json_decode(file_get_contents($base_dir.'/ships.json'), true);*/

foreach($filtered_ships as $ship_id=>$ship_name){
	//if(preg_match($pattern, $ship_name)){
		$ship_desc = file_get_contents($desc.$ship_id);
		if($ship_desc){
			file_put_contents($base_dir.'/'.$ship_id.'/description.json', $ship_desc);
		}
		$ship_cabins = json_decode(file_get_contents($cabins.$ship_id), true);
		$categories = array();
		foreach($ship_cabins as $cabin){
			if(!in_array($cabin['type'], $categories)) $categories[$cabin['type']] = $cabin['name'];
		}
		$ship_details = array();
		//$ship_details['description'] = $ship_desc;
		$count = 0;
		foreach($categories as $type=>$name){
			$cabins_details = json_decode(file_get_contents($cabins_photo.$ship_id.'/'.$name), true);
			$ship_details[$count]['type'] = $type;
			$ship_details[$count]['desc'] = strip_tags($cabins_details['description']);
			if(isset($cabins_details['photos'][0])){
				$ship_details[$count]['img'] = $cabins_details['photos'][0];
			}
			$count++;
		}
		$data_cabins = array('data'=>$ship_details);
		file_put_contents($base_dir.'/'.$ship_id.'/cabins.json', json_encode($data_cabins));
	//}
}

$executionEndTime4 = microtime(true);
$seconds4 = $executionEndTime4 - $executionStartTime;
file_put_contents('logs/cabins.log', "Выполнено ".date('Y-m-d H:i:s')." за $seconds4 секунд.");

require('./table.php');