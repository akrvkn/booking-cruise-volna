<?php

$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parts = parse_url($actual_link);
$params_str = $parts['query'];
$base = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&dateformat=msk&request=ship&cabins=true&images=true&';
$url = $base.$params_str;

$ship_json = json_decode(file_get_contents($url));
foreach($ship_json->answer->shipcabins as $cabin){
	$count = 1;
	foreach($cabin->cabinimages as $image){
		$image->id = $count;
		$count++;
	}
}
//var_dump($ship_json);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
echo json_encode($ship_json);
?>
