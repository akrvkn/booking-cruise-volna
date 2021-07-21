<?php
//https://restapi.infoflot.com/cruises/351913?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b&dateStartFrom=2021-07-20
//$iff_prices_url = 'https://restapi.infoflot.com/cruises/351913/cabins?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$key = '407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$now = date('Y-m-d');
$iff_boards = [478, 498, 83, 4, 38, 571, 7, 1];
$cruises_base = 'https://restapi.infoflot.com/cruises?key='.$key.'&dateStartFrom='.$now.'&type=river&startCountry=1&limit=100&ship='.implode(',', $iff_boards);

$page = 1;
$pages = 0;
$counter = 0;
$table = [];
$ships = [];
$total = 0;
//$vdh_names = [];

$today = date("Y-m-d");

function getContents($url){
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    $contents = curl_exec($ch);
    if (curl_errno($ch)) {
       echo curl_error($ch);
       echo "\n<br />";
    $contents = '';
    } else {
    curl_close($ch);
    }
    return $contents;
}


$mtf_ships_url = 'https://api.mosturflot.ru/v3/rivercruises/ships?filter[is-own]=1';
$mtf_ships_list = json_decode(getContents($mtf_ships_url));
$mtf_images = [];
$mtf_names = [];
$mtf_titles = [];

foreach($mtf_ships_list->data as $key=>$mtf_ship){
    $mtf_names[$mtf_ship->id] = $mtf_ship->attributes->name;
}
$filterMtf = '&filter[ship-id][in][]=5&filter[ship-id][in][]=14&filter[ship-id][in][]=19&filter[ship-id][in][]=36&filter[ship-id][in][]=72&filter[ship-id][in][]=91&filter[ship-id][in][]=92&filter[ship-id][in][]=139&filter[ship-id][in][]=150&filter[ship-id][in][]=198&filter[ship-id][in][]=200&filter[ship-id][in][]=206&filter[ship-id][in][]=207&filter[ship-id][in][]=247';

//https://api.mosturflot.ru/v3/rivercruises/tours?filter[start][gte]=2021-07-20T00:00:00Z

$mtf_cruises = json_decode(getContents('https://api.mosturflot.ru/v3/rivercruises/tours?filter[start][gte]='.$now.'T00:00:00Z'.$filterMtf.'&per-page=1000'), true);

$mtf_prices = 'https://api.mosturflot.ru/v3/rivercruises/tours/5943/tour-rates';

foreach($mtf_cruises['data'] as $key=>$val){
    $table[$counter]['company'] = 'mosturflot';
    $table[$counter]['shipid'] = $val['attributes']['ship-id'];
    $table[$counter]['shipname'] = $mtf_names[$val['attributes']['ship-id']];
    $table[$counter]['tourid'] = (int)$val['id'];
    $table[$counter]['tourstart'] = substr($val['attributes']['start'], 0, 10);
    $table[$counter]['tourfinish'] = substr($val['attributes']['finish'], 0, 10);
    $table[$counter]['tourroute'] = $val['attributes']['route'];
    $table[$counter]['tourdays'] = $val['attributes']['days'];
    $table[$counter]['tourminprice'] = (int)$val['attributes']['price-from'];
    //$table[$counter]['tourcabinsfree'] = '';

    $prices = [];
    $price_list = json_decode(getContents('https://api.mosturflot.ru/v3/rivercruises/tours/'.$val['id'].'/tour-rates'), true);
    foreach($price_list['data'] as $price){
        $cat_link = 'https://api.mosturflot.ru/v3/rivercruises/cabin-categories/'.$price['attributes']['category-id'];

        $cat = json_decode(getContents($cat_link), true);

        if(isset($cat['data']) && isset($price['attributes']['category-id']) && isset($price['attributes']['price-main']) && $price['attributes']['rate-id'] == 'adult'){
            $prices[$cat['data']['attributes']['name']] = $price['attributes']['price-main'];
        }
    }
    $table[$counter]['pricelist'] = $prices;
    $counter++;
}



//12055
$vdh_prices = 'https://api.vodohod.com/json/v2/cruise-prices.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&cruise=';

$vdh_days = 'https://api.vodohod.com/json/v2/cruise-days.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&cruise=';

$vodohodApi = json_decode(getContents('https://api.vodohod.com/json/v2/cruises.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7'), true);
foreach( $vodohodApi as $vdh_ship_cruise ){

    if( $today < $vdh_ship_cruise['dateStart']) {
        $cruise_days = json_decode(getContents($vdh_days.$vdh_ship_cruise['id']), true);
        $route = '';
        if(count($cruise_days) > 0){
            foreach($cruise_days as $day){
                $route .= $day['portName'].' - ';
            }
            $table[$counter]['company'] = 'vodohod';
            $table[$counter]['shipid'] = $vdh_ship_cruise['motorshipId'];
            $table[$counter]['shipname'] = $vdh_ship_cruise['motorshipName'];
            $table[$counter]['tourid'] = $vdh_ship_cruise['id'];
            $table[$counter]['tourstart'] = $vdh_ship_cruise['dateStart'];
            $table[$counter]['tourfinish'] = $vdh_ship_cruise['dateStop'];
            $table[$counter]['tourroute'] = substr($route, 0, -3);
            $table[$counter]['tourdays'] = $vdh_ship_cruise['days'];
            $table[$counter]['tourminprice'] = intval((float)$vdh_ship_cruise['priceMin']);


            $prices = [];
            $price_list = json_decode(getContents($vdh_prices.$vdh_ship_cruise['id']), true);
            foreach($price_list['tariffs'][0]['prices'] as $price){
                    $patterns = array ('/\s+\*+/', '/\s+каюта/', '/\s+одноярусная/');
                    $replace = array ('', '', '');
                    $cat_name = preg_replace($patterns, $replace, $price['rt_name']);
                    $prices[$cat_name] = $price['price_value'];
            }
            $table[$counter]['pricelist'] = $prices;

            $counter++;
        }
    }
}


//Infoflot

$firstPage = json_decode(getContents($cruises_base), true);
$total = $firstPage['pagination']['records']['total'];
$page = $firstPage['pagination']['pages']['next']['number'];
$pages = $firstPage['pagination']['pages']['total'];

foreach( $firstPage['data'] as $pageData) {
    //if(!in_array($pageData['ship']['name'], $vdh_names)){
        $table[$counter]['company'] = 'infoflot';
        $table[$counter]['shipid'] = $pageData['ship']['id'];
        $table[$counter]['shipname'] = $pageData['ship']['name'];
        $table[$counter]['tourid'] = $pageData['id'];
        $table[$counter]['tourstart'] = substr($pageData['dateStart'], 0, 10);
        $table[$counter]['tourfinish'] = substr($pageData['dateEnd'], 0, 10);
        $table[$counter]['tourroute'] = $pageData['route'];
        $table[$counter]['tourdays'] = $pageData['days'];
        $table[$counter]['tourminprice'] = (int)$pageData['min_price'];
        //$table[$counter]['tourcabinsfree'] = $pageData['freeCabins'];

        $iff_prices_url = 'https://restapi.infoflot.com/cruises/'.$pageData['id'].'/cabins?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
        $prices = [];
        $price_list = json_decode(getContents($iff_prices_url), true);
        foreach($price_list['prices'] as $price){
            $prices[$price['type_name']] = $price['prices']['main_bottom']['adult'];
        }
        $table[$counter]['pricelist'] = $prices;
        
        $counter++;
    //}
}

for($i=0; $i < $pages; $i++){
    //echo $page."\n";
    if($page < $pages){
        $nextPage = json_decode(@getContents($cruises_base.'&page='.$page), true);
        $page = $nextPage['pagination']['pages']['next']['number'];

        if($nextPage){
            foreach( $nextPage['data'] as $pageData) {
                    $table[$counter]['company'] = 'infoflot';
                    $table[$counter]['shipid'] = $pageData['ship']['id'];
                    $table[$counter]['shipname'] = $pageData['ship']['name'];
                    $table[$counter]['tourid'] = $pageData['id'];
                    $table[$counter]['tourstart'] = substr($pageData['dateStart'], 0, 10);
                    $table[$counter]['tourfinish'] = substr($pageData['dateEnd'], 0, 10);
                    $table[$counter]['tourroute'] = $pageData['route'];
                    $table[$counter]['tourdays'] = $pageData['days'];
                    $table[$counter]['tourminprice'] = (int)$pageData['min_price'];
                    //$table[$counter]['tourcabinsfree'] = $pageData['freeCabins'];

                    $iff_prices_url = 'https://restapi.infoflot.com/cruises/'.$pageData['id'].'/cabins?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
                    $prices = [];
                    $price_list = json_decode(getContents($iff_prices_url), true);
                    foreach($price_list['prices'] as $price){
                        $prices[$price['type_name']] = $price['prices']['main_bottom']['adult'];
                    }
                    $table[$counter]['pricelist'] = $prices;

                    $counter++;
            }
        }
    }
}


usort($table, function($a, $b){
    return $a['tourstart'] <=> $b['tourstart'];
});

file_put_contents('ajax/cruises-mobile.json', json_encode($table));

?>
