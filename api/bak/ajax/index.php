<?php

$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parts = parse_url($actual_link);
$params_str = $parts['query'];
$base = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&dateformat=msk&';
$url = $base.$params_str;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
echo file_get_contents($url);
?>
