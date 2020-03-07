<?php

$executionStartTime = microtime(true);

require('./config/config.php');

ini_set("date.timezone", "Europe/Moscow");

$base_dir = 'data';
if(!is_dir($base_dir)){
		mkdir($base_dir);
	}

$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

//$exclude = [31, 34, 294, 95];
$exclude = [];
$mtf = ['Анастасия', 'Виктория', 'Грин', 'Есениин', 'Булгаков', 'Карамзин', 'Крылов', 'Образцов', 'Репин', 'Россия', 'Рублев', 'Суриков'];


$ships_list = json_decode(file_get_contents($ships), true);

//mtf images
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

file_put_contents('data/mtfimages.json', json_encode($mtf_images));
file_put_contents('data/mtfships.json', json_encode($mtf_names));
//end mtf images

$filtered_ships = array();
if($ships_list){
	foreach($ships_list as $ship_id=>$ship_name){	
		if(preg_match($pattern, $ship_name)&& !in_array($ship_id, $exclude)){		
			$filtered_ships[$ship_id]=$ship_name;				
		}
	}
}

if(count($filtered_ships)>0){
	file_put_contents($base_dir.'/ships.json', json_encode($filtered_ships));
}
//var_dump($filtered_ships);


$ships_img = @file_get_contents($shipsimages);
if($ships_img){
	file_put_contents($base_dir.'/shipsimages.json', $ships_img);
}
$schemes_list = @file_get_contents($schemes);
if($schemes_list){
	file_put_contents($base_dir.'/schemes.json', $schemes_list);
}
	
$executionEndTime1 = microtime(true);
$seconds1 = $executionEndTime1 - $executionStartTime;
file_put_contents('logs/ships.log', "Выполнено ".date('Y-m-d H:i:s')." за $seconds1 секунд.");

require('./tours.php');
