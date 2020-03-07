<?php

$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$ships = $API_BASE.'Ships/';
$cabins = $API_BASE.'Cabins/';
$cabins_photo = $API_BASE.'CabinsPhoto/';
$shipsimages = $API_BASE.'ShipsImages/';
$desc = $API_BASE.'ShipsDescription/';
$tours_base = $API_BASE.'Tours/';
$schemes = $API_BASE.'ShipsSchemes/';
$excursions = $API_BASE.'Excursions/';

$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

$base_dir = 'data';
if(!is_dir($base_dir)){
		mkdir($base_dir);
	}

$ships_list = json_decode(file_get_contents($ships), true);

$filtered_ships = array();


$counter = 0;
foreach($ships_list as $ship_id=>$ship_name){	
		if(preg_match($pattern, $ship_name)){
		    $filtered_ships[$ship_id] = $ship_name;
		    if(!is_dir($base_dir.'/'.$ship_id)){
			        mkdir($base_dir.'/'.$ship_id);
		        }
		    $counter++;
		    if($counter == 90){
						    //echo $counter.'-->'.$ship_id.PHP_EOL;
						    $counter = 0;
						    sleep(60);
						}		 
		    $ship_tours = file_get_contents($tours_base.$ship_id);
		    if($ship_tours&&count(json_decode($ship_tours, true))>0){
		        //echo $ship_name.'->'.$ship_id.PHP_EOL;
		       
			    file_put_contents($base_dir.'/'.$ship_id.'/tours.json', $ship_tours);
		    }
		}
}

file_put_contents($base_dir.'/ships.json', json_encode($filtered_ships));

?>
