<?php
//https://restapi.infoflot.com/ships/478?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b
$infoflot_api = 'https://restapi.infoflot.com/ships/';
$iff_key = '?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$iff_ships = [478, 498, 83, 4, 38, 571, 7, 1];

//https://api.vodohod.com/json/v2/motorship-rooms.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&motorship=37
//https://api.vodohod.com/json/v2/motorships.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7
$vodohod_api = 'https://api.vodohod.com/json/v2/motorship-rooms.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&motorship=';
$vdh_room_types = 'https://api.vodohod.com/json/v2/room-types.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7';
$vdh_ships_data = json_decode(file_get_contents('https://api.vodohod.com/json/v2/motorships.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7'), true);
$vdh_ships = [37, 8, 29, 16, 14, 7, 11, 3, 4, 6, 15, 42, 32, 50, 1, 69, 90];

//https://api.mosturflot.ru/v3/rivercruises/ships/19?include=cabins,cabin-categories
$mosturflot_api = 'https://api.mosturflot.ru/v3/rivercruises/ships/';
$mtf_include = '?include=cabins,cabin-categories';
$mtf_ships = [5, 14, 19, 36, 72, 91, 92, 139, 150, 198, 200, 206, 207, 247];

/***************************
$iff_deckplan = [];
foreach( $iff_ships as $iff_ship){
    $iff_ship_data = json_decode(file_get_contents($infoflot_api.$iff_ship.$iff_key), true);
    foreach($iff_ship_data['decks'] as $deck){
        if(!isset($iff_deckplan[$iff_ship_data['name']] )){
            $iff_deckplan[$iff_ship_data['name']] = [];
        }
        $iff_deckplan[$iff_ship_data['name']][$deck['position']] = $deck['name'];
    }
    ksort($iff_deckplan[$iff_ship_data['name']]);
    $arr = [];
    foreach($iff_deckplan[$iff_ship_data['name']] as $k=>$v){
        $arr[] = $v;
    }
    $iff_deckplan[$iff_ship_data['name']] = $arr;
}
//file_put_contents('infoflot/decks.json', json_encode($iff_deckplan, JSON_UNESCAPED_UNICODE));

*************************/


$vdh_deckplan = [];
foreach( $vdh_ships_data as $vdh_ship){
    $ship_data = json_decode(file_get_contents($vodohod_api.$vdh_ship['id']), true);
    $vdh_deck = [];
    foreach($ship_data as $vdh_cabin){
        $vdh_deck[$vdh_cabin['deckId']] = $vdh_cabin['deckName'];
    }
    krsort($vdh_deck);
    $arr = [];
    foreach($vdh_deck as $k=>$v){
        $arr[] = $v;
    }

    $vdh_deckplan[$vdh_ship['name']] = $arr;
}

file_put_contents('vodohod/decks.json', json_encode($vdh_deckplan, JSON_UNESCAPED_UNICODE));



/**************************************************
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
  file_put_contents('infoflot/'.$key.'.json', json_encode($val, JSON_UNESCAPED_UNICODE));
}


$vdh_decks = [];

foreach( $vdh_ships as $vdh_ship){
    $vdh_ship_data = json_decode(file_get_contents($vodohod_api.$vdh_ship), true);

    foreach($vdh_ship_data as $vdh_cabin){
        $room = [];
        if(is_numeric($vdh_cabin['number'])){
            $room['id'] = (int)$vdh_cabin['number'];
            $room['name'] = $vdh_cabin['number'];
            $room['deck'] = $vdh_cabin['deckName'];
            $room['category'] = $vdh_cabin['roomTypeName'];
            $vdh_decks[$vdh_ship][] = $room;
        }
    }
}

foreach($vdh_decks as $key => $val){
    file_put_contents('vodohod/'.$key.'.json', json_encode($val, JSON_UNESCAPED_UNICODE));
}


$mtf_decks = [];

foreach( $mtf_ships as $mtf_ship){
    $mtf_ship_data = json_decode(file_get_contents($mosturflot_api.$mtf_ship.$mtf_include), true);
    $cat = [];
    foreach($mtf_ship_data['included'] as $mtf_cat){
        if($mtf_cat['type'] == 'cabin-categories'){
            $cat[$mtf_cat['id']] = $mtf_cat['attributes']['name'];
        }
    }
    foreach($mtf_ship_data['included'] as $mtf_cabin){
        $room = [];
        if($mtf_cabin['type'] == 'cabins' && $mtf_cabin['attributes']['deck-name'] != null && (String)$mtf_cabin['attributes']['category-id'] != ''){
            $room['id'] = (int)$mtf_cabin['attributes']['number'];
            $room['name'] = (String)$mtf_cabin['attributes']['number'];
            $room['deck'] = $mtf_cabin['attributes']['deck-name'];
            $room['category'] = $cat[$mtf_cabin['attributes']['category-id']];
            $mtf_decks[$mtf_ship][] = $room;
        }
    }
}

foreach($mtf_decks as $key => $val){
    file_put_contents('mosturflot/'.$key.'.json', json_encode($val, JSON_UNESCAPED_UNICODE));
}
*************************/
?>
