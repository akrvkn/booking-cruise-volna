<?php
//https://restapi.infoflot.com/ships/478?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b
$infoflot_api = 'https://restapi.infoflot.com/ships/';
$iff_key = '?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$iff_ships = [478, 498, 83, 4, 38, 571, 7, 1];

//https://api.vodohod.com/json/v2/motorship-rooms.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&motorship=37
//https://api.vodohod.com/json/v2/motorships.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7
$vodohod_api = 'https://api.vodohod.com/json/v2/motorship-rooms.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&motorship=';

$vdh_ships_data = json_decode(file_get_contents('https://api.vodohod.com/json/v2/motorships.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7'), true);
$vdh_ships = [];
foreach($vdh_ships_data as $vdh_board){
    $vdh_ships[$vdh_board['id']] = $vdh_board['name'];
}
//$vdh_ships = [37, 8, 29, 16, 14, 7, 11, 3, 4, 15, 10, 42, 32, 50, 1, 69, 90];

//https://api.mosturflot.ru/v3/rivercruises/ships/207?include=cabins
$mosturflot_api = 'https://api.mosturflot.ru/v3/rivercruises/ships/';
$mtf_include = '?include=cabins';
$mtf_ships = [5, 14, 19, 36, 72, 91, 92, 139, 150, 198, 200, 206, 207, 247];


$iff_decks = [];
foreach( $iff_ships as $iff_ship){
    $iff_ship_data = json_decode(file_get_contents($infoflot_api.$iff_ship.$iff_key), true);
    $deck_names = [];
    foreach($iff_ship_data['decks'] as $iff_deck){
        if(!isset($iff_decks[$iff_ship][$iff_deck['name']])){
            $deck_names[$iff_deck['id']] = $iff_deck['name'];
        }
    }
    
    foreach($iff_ship_data['cabins'] as $iff_cabin){
        $room = [];
        if(is_numeric($iff_cabin['name'])){
            $room['id'] = (int)$iff_cabin['name'];
            $room['name'] = $iff_cabin['name'];
            $room['deck'] = $deck_names[$iff_cabin['deckId']];
            $room['category'] = $iff_cabin['typeName'];
            $iff_decks[$iff_ship][] = $room;
        }
    }
}

foreach($iff_decks as $key => $val){
  file_put_contents('infoflot/'.$key.'.json', json_encode($val));
}

//file_put_contents('cabins-infoflot.json', json_encode($iff_decks));

/**
$vdh_decks = [];

foreach( $vdh_ships as $key => $vdh_ship){
    $vdh_ship_data = json_decode(file_get_contents($vodohod_api.$key), true);

    foreach($vdh_ship_data as $vdh_cabin){      
        if(is_numeric($vdh_cabin['number'])){
            $vdh_decks[$vdh_ship][$vdh_cabin['deckName']][$vdh_cabin['number']] = $vdh_cabin['roomTypeName'];
        }
    }
}

file_put_contents('cabins-vodohod.json', json_encode($vdh_decks));
*/




?>
