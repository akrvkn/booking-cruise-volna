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
	
$ships_data = [];

//https://api.vodohod.com/json/v2/ports.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7

$vdh_ships = json_decode(file_get_contents($motorships, false, $context), true);
foreach( $vdh_ships as $board){
  $data = [];
  $data['id'] = $board['id'];
  $data['latLng'] = ['55.850939', '37.466287'];
  $data['name'] = $board['name'];
  $data['company'] = 'Водоход';
  $data['category'] = $board['type'];
  $data['description'] = strip_tags($board['infoShort']);
  $data['cabins'] = [];
  
  if(!is_dir($vdh_dir.'/'.$board['id'])){
		mkdir($vdh_dir.'/'.$board['id']);
		mkdir($vdh_dir.'/'.$board['id'].'/cabins');
	}	
	$cabin_data = [];
  for($i=0; $i < count($board['rooms']); $i++){      
    $cabin_data[$i]['id'] = $i;
    $cabin_data[$i]['category'] = $board['rooms'][$i]['roomTypeName'];
    $cabin_data[$i]['description'] = $board['rooms'][$i]['roomDescription'];    
  }	
  $data['cabins'] = $cabin_data;
  $ships_data[] = $data;
}
file_put_contents('ships-vodohod.json', json_encode($ships_data, JSON_UNESCAPED_UNICODE));

?>
