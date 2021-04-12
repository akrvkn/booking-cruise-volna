<?php

$VDH_BASE = 'https://api.vodohod.com/json/v2/';
$pauth = 'v2-ba9fab12d2c4b8d005645d04492a7af7';
$vcruises = $VDH_BASE.'cruises.php?pauth='.$pauth;
$vcruise_prices = $VDH_BASE.'cruise-prices.php?pauth='.$pauth.'&cruise=';
$vcruise_days = $VDH_BASE.'cruise-days.php?pauth='.$pauth.'&cruise=';
$motorships = $VDH_BASE.'motorships.php?pauth='.$pauth;
$motorship_rooms = $VDH_BASE.'motorship-rooms.php?pauth='.$pauth.'&motorship=';
$vports = $VDH_BASE.'ports.php?pauth='.$pauth;
$vdecks = $VDH_BASE.'decks.php?pauth='.$pauth;
$vroom_types = $VDH_BASE.'room-types.php?pauth='.$pauth;

$opts = array(
'http' => array('ignore_errors' => true),
'ssl' => array(
'verify_peer' => false,
'verify_peer_name' => false
)
);

$context = stream_context_create($opts);

$base_dir = 'data';

if(!is_dir($base_dir)){
		mkdir($base_dir);
	}
$vdh_dir = 'data/vodohod';
if(!is_dir($vdh_dir)){
		mkdir($vdh_dir);
	}	
	
	
$pattern = '|<div class="ship-cabin__items">(.*)<span>Команда теплохода</span>|siU';
$item_pattern = '|<div class="ship-cabin-i" data-cabin-type-content="(\d+)".*ship-cabin-i__text|siU';
$img_pattern = '|<a href="(.*)\.jpg"|siU';	
	
$ships_data = [];

//https://api.vodohod.com/json/v2/ports.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7

$vdh_ships = json_decode(file_get_contents($motorships, false, $context), true);
foreach( $vdh_ships as $board){
  //$data = [];
  //$data['id'] = $board['id'];
  //$data['latLng'] = ['55.850939', '37.466287'];
  //$data['name'] = $board['name'];
  //$data['company'] = 'Водоход';
  //$data['category'] = $board['type'];
  //$data['description'] = strip_tags($board['infoShort']);
  //$data['cabins'] = [];
  //$cabin_data = [];
  if(!is_dir($vdh_dir.'/'.$board['id'])){
		mkdir($vdh_dir.'/'.$board['id']);
		mkdir($vdh_dir.'/'.$board['id'].'/cabins');
	}	
	
	file_put_contents($vdh_dir.'/'.$board['id'].'/'.$board['id'].'.jpg', file_get_contents('../assets/img/vdh/'.$board['code'].'.jpg'));
	
	file_put_contents($vdh_dir.'/'.$board['id'].'/dekplan.gif', file_get_contents($board['decks']));
	
	$ship_html = file_get_contents('https://vodohod.com/ships/'.$board['code']);    
  preg_match($pattern, $ship_html, $match);
  preg_match_all($item_pattern, $match[0], $matches);
	
	$k = 0;
	$src = [];
  for($i=0; $i < count($matches[0]); $i++){ 
    if(!is_file($vdh_dir.'/'.$board['id'].'/cabins/'.$k.'.jpg') ){
          preg_match($img_pattern, $matches[0][$i], $result);
         if(isset($result[1])){
            $headers = get_headers('https://vodohod.com'.$result[1].'.jpg', 1);
            if(!in_array($headers['Content-Length'], $src)){
              $src[] = $headers['Content-Length'];
              file_put_contents($vdh_dir.'/'.$board['id'].'/cabins/'.$k.'.jpg', file_get_contents('https://vodohod.com'.$result[1].'.jpg'));
              $k++;
            }
         }
    }
    
  }	

}
/**$ship_html = file_get_contents('https://vodohod.com/ships/sankt-peterburg/');

$pattern = '|<div class="ship-cabin__items">(.*)<span>Команда теплохода</span>|siU';
$item_pattern = '|<div class="ship-cabin-i" data-cabin-type-content="(\d+)".*Посмотреть|siU';
$img_pattern = '|<a href="(.*)\.jpg"|siU';

preg_match($pattern, $ship_html, $match);

preg_match_all($item_pattern, $match[0], $matches);

for($i=0; $i < count($matches[0]); $i++){
  preg_match($img_pattern, $matches[0][$i], $result);
  echo $matches[1][$i].' -- '.$result[1].PHP_EOL;
}*/

//var_dump($result);

?>
