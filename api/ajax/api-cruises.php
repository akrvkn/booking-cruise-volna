<?php

$sdir = dirname(__FILE__);
system ( 'cd '.$sdir.'; git pull;' );

$key = '407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$now = date('Y-m-d');
$cruises_base = 'https://restapi.infoflot.com/cruises?key='.$key.'&dateStartFrom='.$now.'&type=river&startCountry=1&limit=100';

$page = 1;
$pages = 0;
$counter = 0;
$table = [];
$total = 0;

$firstPage = json_decode(file_get_contents($cruises_base), true);
$total = $firstPage['pagination']['records']['total'];
$page = $firstPage['pagination']['pages']['next']['number'];
$pages = $firstPage['pagination']['pages']['total'];

foreach( $firstPage['data'] as $pageData) {
        $table[$counter]['company'] = 'iff';
        $table[$counter]['shipid'] = $pageData['ship']['id'];
        $table[$counter]['shipname'] = $pageData['ship']['name'];
        $table[$counter]['tourid'] = $pageData['id'];
        $table[$counter]['tourstart'] = $pageData['dateStart'];
        $table[$counter]['tourfinish'] = $pageData['dateEnd'];
        $table[$counter]['tourroute'] = $pageData['route'];
        $table[$counter]['tourdays'] = $pageData['days'];
        $table[$counter]['tourminprice'] = $pageData['min_price'];
        $table[$counter]['tourcabinsfree'] = $pageData['freeCabins'];
        $counter++;
}

for($i=0; $i < $pages; $i++){
    //echo $page."\n";
    if($page < $pages){
        $nextPage = json_decode(@file_get_contents($cruises_base.'&page='.$page), true);
        $page = $nextPage['pagination']['pages']['next']['number'];

        if($nextPage){
            foreach( $nextPage['data'] as $pageData) {
                    $table[$counter]['company'] = 'iff';
                    $table[$counter]['shipid'] = $pageData['ship']['id'];
                    $table[$counter]['shipname'] = $pageData['ship']['name'];
                    $table[$counter]['tourid'] = $pageData['id'];
                    $table[$counter]['tourstart'] = $pageData['dateStart'];
                    $table[$counter]['tourfinish'] = $pageData['dateEnd'];
                    $table[$counter]['tourroute'] = $pageData['route'];
                    $table[$counter]['tourdays'] = $pageData['days'];
                    $table[$counter]['tourminprice'] = $pageData['min_price'];
                    $table[$counter]['tourcabinsfree'] = $pageData['freeCabins'];
                    $counter++;
            }
        }
    }
}

$vodohodApi = json_decode(file_get_contents('https://api.vodohod.com/json/v2/cruises.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7'), true);
foreach( $vodohodApi as $vdh_ship_cruise ){
    $table[$counter]['company'] = 'vdh';
    $table[$counter]['shipid'] = $vdh_ship_cruise['motorshipId'];
    $table[$counter]['shipname'] = $vdh_ship_cruise['motorshipName'];
    $table[$counter]['tourid'] = $vdh_ship_cruise['id'];
    $table[$counter]['tourstart'] = $vdh_ship_cruise['dateStart'];
    $table[$counter]['tourfinish'] = $vdh_ship_cruise['dateStop'];
    $table[$counter]['tourroute'] = $vdh_ship_cruise['name'];
    $table[$counter]['tourdays'] = $vdh_ship_cruise['days'];
    $table[$counter]['tourminprice'] = $vdh_ship_cruise['priceMin'];
    $table[$counter]['tourcabinsfree'] = $vdh_ship_cruise['availabilityCount'];

	$counter++;
}

usort($table, function($a, $b){
    return $a['tourstart'] <=> $b['tourstart'];
});

file_put_contents('cruises.json', json_encode($table));

$sdate = date('Y-m-d H:i');

system ( 'cd '.$sdir.'; git add -A;' );
system ( 'cd '.$sdir.'; git commit -a -m "Updated db '.$sdate.'";' );
system ( 'cd '.$sdir.'; git push origin master ;' );

?>