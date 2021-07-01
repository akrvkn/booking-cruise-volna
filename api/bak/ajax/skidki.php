<?php
$skidki = '';

$spec = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&dateformat=msk&request=tours&specialonly=true&loading=true';

$skidki =  @file_get_contents($spec);
if($skidki !== ''){
	file_put_contents('skidki.txt', $skidki);
}

?>