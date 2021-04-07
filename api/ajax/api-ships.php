<?php

/**$uri = 'https://restapi.infoflot.com/ships/228?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';*/

$cruises = json_decode(file_get_contents('cruises.json'), true);

$table = [];
foreach( $cruises as $cruise ){
    if($cruise['company'] == 'iff'){
        $ship = json_decode(file_get_contents('https://restapi.infoflot.com/ships/'.$cruise['shipid'].'?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b'), true);
        $cruise['shipPhoto'] = $ship['files']['photo']['path'];
        $cruise['shipPlan'] = $ship['files']['scheme']['path'];
        $table[] = $cruise;
    }
    if($cruise['company'] == 'vdh'){
        $ms = json_decode(file_get_contents('vodohod/motorships.json'), true);
        foreach($ms as $item){
            if($cruise['shipid'] == $item['id']){
                $cruise['shipPhoto'] = 'https://www.cruise-volna.ru/assets/img/vdh/'.$item['code'].'.jpg';
                $table[] = $cruise;
            }
        }
    }
}

file_put_contents('cruises-ships.json', json_encode($table));


?>
