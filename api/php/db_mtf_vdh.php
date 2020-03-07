<?php
ini_set("date.timezone", "Europe/Moscow");

$base_dir = 'data';
if(!is_dir($base_dir)){
		mkdir($base_dir);
	}

$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

//$exclude = [31, 34, 294, 95];
$exclude = [];
$mtf = ['анастасия', 'виктория', 'грин', 'есениин', 'булгаков', 'карамзин', 'крылов', 'образцов', 'репин', 'россия', 'рублев', 'суриков'];


//$ships_list = json_decode(file_get_contents($ships), true);

//mtf images
$mtf_ships_url = 'https://api.mosturflot.ru/v3/rivercruises/ships?filter[is-own]=1';
$mtf_images_base = 'https://api.mosturflot.ru/v3/rivercruises/ships/';

$mtf_ships_list = json_decode(file_get_contents($mtf_ships_url));

$mtf_images = [];
$mtf_names = [];

foreach($mtf_ships_list->data as $key=>$mtf_ship){
    $img_url = $mtf_images_base.$mtf_ship->id.'/images';
    $ship_images = json_decode(file_get_contents($img_url), true);
    $mtf_images[$mtf_ship->id] = $ship_images['data'][0]['links']['preview-url'];
    $mtf_names[$mtf_ship->id] = $mtf_ship->attributes->name;
}

file_put_contents('data/mtfimages.json', json_encode($mtf_images));
file_put_contents('data/mtfships.json', json_encode($mtf_names));
//end mtf images

$counter = 0;
$table = array();

$mtf_cruises = json_decode(file_get_contents('https://www.mosturflot.ru/api/ajax/?request=tours&loading=true'));

foreach($mtf_cruises->answer as $key=>$val){
    //echo $val->shipname."\n";
    $table[$counter]['company'] = 'mtf';
    $table[$counter]['shipid'] = $val->shipid;
	$table[$counter]['shipname'] = $val->shipname;
	$table[$counter]['tourid'] = $val->tourid;
	$table[$counter]['tourstart'] = $val->tourstart;
	$table[$counter]['tourfinish'] = $val->tourfinish;
	$table[$counter]['tourroute'] = $val->tourroute;
	$table[$counter]['tourdays'] = $val->tourdays;
	$table[$counter]['tourminprice'] = $val->tourminprice;
	$table[$counter]['tourcabinsfree'] = $val->tourcabinsfree;

	$counter++;
    
}

$vdh_cruises = json_decode(file_get_contents('https://www.rech-agent.ru/api/v1/cruises'));

$vdh = ['Пушкин', 'Горький', 'Симонов', 'Соболев', 'Радищев', 'Дзержинский', 'Жуков', 'Ростропович', 'Чичерин', 'Шашков', 'Федин', 'Толстой', 'Ленин', 'Чернышевский', 'Русь', 'Белинский', 'Петербург', 'Андропов', 'Кронштадт', 'Коротков', 'Новгород', 'Фрунзе', 'Будённый', 'Суворов', 'Кучкин'];

$pattern = '/'.implode('|', $mtf).'/siU';
$img = [];
foreach($vdh_cruises as $cruiseid=>$cruise){
//echo mb_strtolower($cruise->ship).PHP_EOL;
    
    if(preg_match($pattern, mb_strtolower($cruise->ship))!= 1){
    
    
    $img[$cruise->ship] = $cruise->ship_photo_main;
    
        $table[$counter]['company'] = 'vdh';
        $table[$counter]['shipid'] = $cruise->ship_id;
	    $table[$counter]['shipname'] = $cruise->ship;
	    $table[$counter]['tourid'] = $cruiseid;
	    $table[$counter]['tourstart'] = date('c', $cruise->date_start);
	    $table[$counter]['tourfinish'] = date('c', $cruise->date_stop);
	    $table[$counter]['tourroute'] = $cruise->route;
	    $table[$counter]['tourdays'] = (($cruise->date_stop - $cruise->date_start)/3600)/24;
	    $table[$counter]['tourminprice'] = '';
	    $table[$counter]['tourcabinsfree'] = '';
        //echo $cruise->ship.PHP_EOL;
	    $counter++;
        }
}

file_put_contents('data/vdhimages.json', json_encode($img));
usort($table, function($a, $b){
    return $a['tourstart'] <=> $b['tourstart'];
});

file_put_contents($base_dir.'/cruises.json', json_encode($table));

?>

