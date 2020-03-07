<?php
/**$executionStartTime = microtime(true);
ini_set("date.timezone", "Europe/Moscow");

$base_dir = 'data';


//$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';
$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$tours_base = $API_BASE.'Tours/';



$ships_list = json_decode(file_get_contents($base_dir.'/ships.json'), true);*/


foreach($filtered_ships as $ship_id=>$ship_name){	
	//if(preg_match($pattern, $ship_name)){
		if(!is_dir($base_dir.'/'.$ship_id)){
			mkdir($base_dir.'/'.$ship_id);
		}
		
		$ship_tours = file_get_contents($tours_base.$ship_id);
		if($ship_tours&&count(json_decode($ship_tours, true))>0){
			file_put_contents($base_dir.'/'.$ship_id.'/tours.json', $ship_tours);
		}
	
	//}
}

$executionEndTime2 = microtime(true);
$seconds2 = $executionEndTime2 - $executionStartTime;
file_put_contents('logs/tours.log', "Выполнено ".date('Y-m-d H:i:s')." за $seconds2 секунд.");

require('./routes.php');