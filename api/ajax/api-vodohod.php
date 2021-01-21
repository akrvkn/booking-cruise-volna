<?php

$sdir = dirname(__FILE__);
system ( 'cd '.$sdir.'; git pull;' );

//Vodohod.com API v2
//https://api.vodohod.com/json/v2/cruises.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7
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

$vdh_dir = 'vodohod/';
if(!is_dir($vdh_dir)){
	mkdir($vdh_dir, 0777, true);
}

$vdh_ships_list = file_get_contents($motorships, false, $context);
file_put_contents($vdh_dir.'/motorships.json', $vdh_ships_list);

$vdh_cruises = file_get_contents($vcruises, false, $context);
file_put_contents($vdh_dir.'/cruises.json', $vdh_cruises);

$vdh_ships = json_decode($vdh_ships_list, true);
foreach($vdh_ships as $id=>$vdh_ship){
    $vdh_ship_dir = $vdh_dir.$id;
    if(!is_dir($vdh_ship_dir)){
    mkdir($vdh_ship_dir);
}
$vdh_ship_cruises = json_decode(file_get_contents($vcruises.'&motorships='.$id, false, $context), true);

foreach($vdh_ship_cruises as $cruise_id=>$vdh_ship_cruise){
    $vdh_cruise_prices = json_decode(file_get_contents($vcruise_prices.$cruise_id, false, $context), true);
    $vdh_cruise_days = json_decode(file_get_contents($vcruise_days.$cruise_id, false, $context), true);


    foreach($vdh_cruise_prices['room_availability'] as $r_key=>$room_free){
        $vdh_cruise_prices['tariffs'][0]['prices'][$r_key]['available'] = count($room_free);
    }

    $vdh_ship_cruise['tariffs'] = $vdh_cruise_prices['tariffs'];
    $vdh_ship_cruise['routeDays'] = $vdh_cruise_days;

    file_put_contents($vdh_ship_dir.'/'.$cruise_id.'.json', json_encode($vdh_ship_cruise));
}


file_put_contents($vdh_ship_dir.'/cruises.json', json_encode($vdh_ship_cruises));
}


//End Vodohod.com API v2

$sdate = date('Y-m-d H:i');

system ( 'cd '.$sdir.'; git add -A;' );
system ( 'cd '.$sdir.'; git commit -a -m "Updated db '.$sdate.'";' );
system ( 'cd '.$sdir.'; git push origin master ;' );

?>