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
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false
    )
);

$context = stream_context_create($opts);


$vdh_dir = './data/vdh/';
if(!is_dir($vdh_dir)){
		mkdir($vdh_dir, 0777, true);
	}


$vdh_ships_list = file_get_contents($motorships, false, $context);
file_put_contents($vdh_dir.'/motorships.json', $vdh_ships_list);

$vdh_cruises = file_get_contents($vcruises, false, $context);
file_put_contents($vdh_dir.'/cruises.json', $vdh_cruises);

$vdh_ships = json_decode($vdh_ships_list, true);

$counter = 0;
$table = array();

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
        
        $table[$counter]['company'] = 'vdh';
        $table[$counter]['shipid'] = $vdh_ship_cruise['motorshipId'];
	    $table[$counter]['shipname'] = $vdh_ship_cruise['motorshipName'];
	    $table[$counter]['tourid'] = $cruise_id;
	    $table[$counter]['tourstart'] = $vdh_ship_cruise['dateStart'];
	    $table[$counter]['tourfinish'] = $vdh_ship_cruise['dateStop'];
	    $table[$counter]['tourroute'] = $vdh_ship_cruise['name'];
	    $table[$counter]['tourdays'] = $vdh_ship_cruise['days'];
	    $table[$counter]['tourminprice'] = $vdh_ship_cruise['priceMin'];
	    $table[$counter]['tourcabinsfree'] = $vdh_ship_cruise['availabilityCount'];

	    $counter++;
    }
    
    
    file_put_contents($vdh_ship_dir.'/cruises.json', json_encode($vdh_ship_cruises));
}

file_put_contents($vdh_dir.'/cruises.json', json_encode($table));







