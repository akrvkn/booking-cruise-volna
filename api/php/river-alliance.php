<?php
/*ini_set('display_errors', '1');
ini_set("date.timezone", "Europe/Moscow");
$executionStartTime = microtime(true);*/

$base_dir = 'data';
$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

$ships_list = json_decode(file_get_contents($base_dir.'/ships.json'), true);

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

$pattern = '/'.implode('|', $vdh).'/siU';
$img = [];
foreach($vdh_cruises as $cruiseid=>$cruise){
    if(preg_match($pattern, $cruise->ship)){
    
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

    $counter++;
   }
}

file_put_contents('data/vdhimages.json', json_encode($img));

if ($handle = opendir($base_dir)) {
    while (false !== ($entry = readdir($handle))) {
   
        if ($entry != "." && $entry != ".." && is_dir($base_dir.'/'.$entry) && is_file($base_dir.'/'.$entry.'/tours.json') && isset($ships_list[$entry])) {
            	$tours = json_decode(file_get_contents($base_dir.'/'.$entry.'/tours.json'), true);
            	if($tours){
            		foreach($tours as $tour_id=>$tour){
            		        if(isset($tour['date_start'])){
                            $table[$counter]['company'] = 'iff';
							$table[$counter]['shipid'] = $entry;
							$table[$counter]['shipname'] = $ships_list[$entry];
							$table[$counter]['tourid'] = $tour_id;
							$date_start = date_create_from_format('d.m.Y H:i', $tour['date_start'].' '.$tour['time_start']);
							$datetime_start = date_format($date_start, 'c');
							$table[$counter]['tourstart'] = $datetime_start;
							$date_end = date_create_from_format('d.m.Y H:i', $tour['date_end'].' '.$tour['time_end']);
							$datetime_end = date_format($date_end, 'c');
							$table[$counter]['tourfinish'] = $datetime_end;
							$table[$counter]['tourroute'] = trim(preg_replace('|\(.*\)|sU', '', $tour['cities']));
							$table[$counter]['tourdays'] = $tour['days'];
							$minprice = 100000000000000;
							$places = 0;
							foreach($tour['prices'] as $price){
								if($price['price']< $minprice&&$price['price']>0) $minprice = $price['price'];
								$places += $price['places_free'];
							}
							$table[$counter]['tourminprice'] = $minprice;
							$table[$counter]['tourcabinsfree'] = $places;
							
						
							$counter++;
							}							
		
					}
			
            	}           	
            
        }
    }
    closedir($handle);
}

usort($table, function($a, $b){
    return $a['tourstart'] <=> $b['tourstart'];
});

file_put_contents($base_dir.'/cruises.json', json_encode($table));

?>
