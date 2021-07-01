<?php
$tours = $mtftours = $skidki = '';

$tours = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&dateformat=msk&request=tours&own=false&loading=true';
$mtftours = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&dateformat=msk&request=tours&own=true&loading=true';
$spec = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&dateformat=msk&request=tours&specialonly=true&loading=true';

$mtfships = array(
         "207" => "Александр Грин",
         "206" => "Княжна Виктория",
         "200" => "Андрей Рублёв",
         "247" => "Россия",
         "14" => "Василий Суриков",
         "36" => "Илья Репин",
         "19" => "Леонид Красин",
         "198" => "Михаил Булгаков",
         "150" => "Сергей Есенин",
         "139" => "И.А.Крылов",
         "92" => "Николай Карамзин",
         "5" => "Сергей Образцов",
         "72" => "Княжна Анастасия"
         );

$skidki =  @file_get_contents($spec);
$alltours =  @file_get_contents($tours);
$mtf = @file_get_contents($mtftours);

$tours = json_decode($alltours);
$ships = array();
foreach($tours->answer as $tour){
	if(!in_array($tour->shipname, $ships)&& !isset($mtfships[$tour->shipid])){
		$ships[] = $tour->shipname;
	}
}
sort($ships);
$options = '';
foreach($ships as $ship){
	$vals = explode(' ', $ship);
	$val = end($vals);
	$options .= '<option value="'.$val.'">'.$ship.'</option>';
}


file_put_contents('ships-active.html', $options);

if($alltours !== ''){
	file_put_contents('tours.txt', $alltours);
	file_put_contents('/var/www/mosturflot.ru/api/ajax/tours.txt', $alltours);
}
if($mtf !== ''){
	file_put_contents('mtftours.txt', $mtf);
	file_put_contents('mtf-jsonp.json', 'jsonCallback('.$mtf.');');
	file_put_contents('/var/www/mosturflot.ru/api/ajax/mtftours.txt', $mtf);
}
if($skidki !== ''){
	file_put_contents('skidki.txt', $skidki);
	file_put_contents('/var/www/mosturflot.ru/api/ajax/skidki.txt', $skidki);
}

?>
