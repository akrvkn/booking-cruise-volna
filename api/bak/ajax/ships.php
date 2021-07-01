<?php
ini_set('display_errors', '1');
$url = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&own=false&request=ships';
$ships = json_decode(file_get_contents($url));

$html = '<option value="">Выберите теплоход</option>';
foreach($ships->answer as $ship){
$html .= '<option value="'.$ship->shipname.'">'.$ship->shipname.'</option>';
}
echo $html;
?>
