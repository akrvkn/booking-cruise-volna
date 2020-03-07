<?php
$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$ships = $API_BASE.'Ships/';
$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';
$mtf_ships_url = 'https://api.mosturflot.ru/v3/rivercruises/ships?filter[is-own]=1';
$mtf_images_base = 'https://api.mosturflot.ru/v3/rivercruises/ships/';

$mtf_ships_list = json_decode(file_get_contents($mtf_ships_url));

$mtf_images = [];
$mtf_names = [];

foreach($mtf_ships_list->data as $key=>$mtf_ship){
    $img_url = $mtf_images_base.$mtf_ship->id.'/images';
    $ship_images = json_decode(file_get_contents($img_url), true);
    $mtf_images[$mtf_ship->id] = $ship_images['data'][0]['links']['preview-url'];
    $mtf_names[$mtf_ship->id] = $mtf_ship->attributes->name;
}

file_put_contents('mtfimages.json', json_encode($mtf_images));
file_put_contents('mtfships.json', json_encode($mtf_names));


$ships_list = json_decode(file_get_contents($ships), true);
$filtered_ships = array();
if($ships_list){
	foreach($ships_list as $ship_id=>$ship_name){
		foreach($mtf_names as $mtfID=>$mtfName){
			if(mb_strtolower($mtfName) != mb_strtolower($ship_name)&& preg_match($pattern, $ship_name)){			
				$filtered_ships[$ship_id]=$ship_name;
				}				
		
			}
		
		}
}

if(count($filtered_ships)>0){
	file_put_contents('ships.json', json_encode($filtered_ships));
}
