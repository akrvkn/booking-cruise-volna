<?php
$url = 'https://booking.mosturflot.ru/api?userhash=be2d50f5a7ad5daa6df0163c77b4ee59a2b3dbfc&format=json&section=rivercruises&request=ship&images=true&cabins=true&shipid='.$_GET['shipid'];

$ship = json_decode(file_get_contents($url));

$full = '<div class="photo-gallery style1" id="photo-gallery2" data-animation="slide" data-sync="#image-carousel2"><ul class="slides">';
$thumb = '<div class="image-carousel style1" id="image-carousel2" data-animation="slide" data-item-width="70" data-item-margin="10" data-sync="#photo-gallery2"><ul class="slides">';

$html = '';
$count = 0;


foreach($ship->answer->shipimages as $v){
	if($count < 17&&$v->height < 600&&$v->height > 400){
	$full .= '<li><img src="https://'. $v->image. '" alt="" height="533" /></li>';
    $thumb .= '<li><img src="https://'. $v->thumb. '" alt="" /></li>';
    }
    $count++;
}

$html = $full.'</ul></div>'.$thumb.'</ul></div>';
echo $html;
