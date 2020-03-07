<?php
$executionStartTime = microtime(true);

$sdir = dirname(__FILE__);
system ( 'cd '.$sdir.'; git pull;' );

$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$ships = $API_BASE.'Ships/';
$cabins = $API_BASE.'Cabins/';
$cabins_photo = $API_BASE.'CabinsPhoto/';
$shipsimages = $API_BASE.'ShipsImages/';
$desc = $API_BASE.'ShipsDescription/';
$tours_base = $API_BASE.'Tours/';
$schemes = $API_BASE.'ShipsSchemes/';
$excursions = $API_BASE.'Excursions/';
//https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/Tours/5
$pattern = '~(*UTF8)[\p{Cyrillic}]+~i';

$dir = 'data';
if(is_dir('data')){
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
    if ($file->isDir()){
        rmdir($file->getRealPath());
    } else {
        unlink($file->getRealPath());
    }
}
rmdir($dir);
}

$base_dir = 'data';
if(!is_dir($base_dir)){
		mkdir($base_dir);
	}

//images
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

file_put_contents('mtfimages.json', json_encode($mtf_images));
file_put_contents('mtfships.json', json_encode($mtf_names));


$ships_list = json_decode(file_get_contents($ships), true);
$filtered_ships = array();
if($ships_list){
	foreach($ships_list as $ship_id=>$ship_name){
		foreach($mtf_names as $mtfID=>$mtfName){
			if(mb_strtolower($mtfName) != mb_strtolower($ship_name)&& preg_match($pattern, $ship_name)){			
				$filtered_ships[$ship_id]=$ship_name;
				}				
		
			}
		
		}
}

if(count($filtered_ships)>0){
	file_put_contents('ships.json', json_encode($filtered_ships));
}


$counter = 0;
foreach($filtered_ships as $ship_id=>$ship_name){	
		//if(preg_match($pattern, $ship_name)){
		    //$filtered_ships[$ship_id] = $ship_name;
		    if(!is_dir($base_dir.'/'.$ship_id)){
			        mkdir($base_dir.'/'.$ship_id);
		        }
		    $counter++;
		    if($counter == 90){
						    //echo $counter.'-->'.$ship_id.PHP_EOL;
						    $counter = 0;
						    sleep(60);
						}		 
		    $ship_tours = file_get_contents($tours_base.$ship_id);
		    if($ship_tours){		       
			    file_put_contents($base_dir.'/'.$ship_id.'/tours.json', $ship_tours);
		    }
		//}
}

file_put_contents('ships.json', json_encode($filtered_ships));



$ships_img = @file_get_contents($shipsimages);
if($ships_img){
	file_put_contents('shipsimages.json', $ships_img);
}
$schemes_list = @file_get_contents($schemes);
if($schemes_list){
	file_put_contents('schemes.json', $schemes_list);
}
//end images

$counter = 0;
if ($handle = opendir($base_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_dir($base_dir.'/'.$entry) && is_file($base_dir.'/'.$entry.'/tours.json')) {
            	
            	$tours = json_decode(file_get_contents($base_dir.'/'.$entry.'/tours.json'), true);
            	if($tours){
            		foreach($tours as $tour_id=>$tour){	
						$counter++;
						if($counter == 90){
						    //echo $counter.PHP_EOL;
						    $counter = 0;
						    sleep(60);
						}
						$schedule = @file_get_contents($excursions.$entry.'/'.$tour_id);
							if($schedule){
								file_put_contents($base_dir.'/'.$entry.'/'.$tour_id.'.json', $schedule);
							}		
					}
			
            	}            
        }
    }
    closedir($handle);
}

$local_ships_list = json_decode(file_get_contents('ships.json'), true);

$count = 0;
$count_desc = 0;
$count_cab = 0;
$count_photos = 0;

foreach($local_ships_list as $ship_id=>$ship_name){
        //echo $ship_id.'--'.$ship_name.'--'.$count.PHP_EOL;
        if($count > 70){		    
			    $count = 0;
			    sleep(60);
			}
		$ship_desc = file_get_contents($desc.$ship_id);
		$count++;
		if($ship_desc){		 		   
			file_put_contents($base_dir.'/'.$ship_id.'/description.json', $ship_desc);
		}
		
		$ship_cabins = json_decode(file_get_contents($cabins.$ship_id), true);
		$ship_details = array();
        $categories = array();
		
		if(count($ship_cabins)>0){
		    foreach($ship_cabins as $cabin){
			    if(!in_array($cabin['type'], $categories)) $categories[$cabin['type']] = $cabin['name'];
		    }
		    foreach($categories as $type=>$name){		    
			    $cabins_details = json_decode(file_get_contents($cabins_photo.$ship_id.'/'.$name), true);
			    $ship_details[$count]['type'] = $type;
			    $ship_details[$count]['desc'] = strip_tags($cabins_details['description']);
			    if(isset($cabins_details['photos'][0])){
				    $ship_details[$count]['img'] = $cabins_details['photos'][0];
			    }
			    $count++;
		    }
		}
		$data_cabins = array('data'=>$ship_details);
		file_put_contents($base_dir.'/'.$ship_id.'/cabins.json', json_encode($data_cabins));
}

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

/**$vdh_cruises = json_decode(file_get_contents('https://www.rech-tour.ru/api/v1/cruises'));

$vdh = ['Пушкин', 'Горький', 'Симонов', 'Соболев', 'Радищев', 'Дзержинский', 'Жуков', 'Ростропович', 'Чичерин', 'Шашков', 'Федин', 'Толстой', 'Ленин', 'Чернышевский', 'Русь', 'Белинский', 'Петербург', 'Андропов', 'Кронштадт', 'Коротков', 'Новгород', 'Фрунзе', 'Будённый', 'Суворов', 'Кучкин'];

$pattern = '/'.implode('|', $vdh).'/siU';
$img = [];
foreach($vdh_cruises as $cruiseid=>$cruise){
    if(preg_match($pattern, $cruise->ship)&&$cruise->date_start > time()){
    
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

file_put_contents('vdhimages.json', json_encode($img));*/

//Vodohod.com API v2

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

//$counter = 0;
//$table = array();

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


//End Vodohod.com API v2


if ($handle = opendir($base_dir)) {
    while (false !== ($entry = readdir($handle))) {
   
        if ($entry != "." && $entry != ".." && is_dir($base_dir.'/'.$entry) && is_file($base_dir.'/'.$entry.'/tours.json') && isset($local_ships_list[$entry])) {
            	$tours = json_decode(file_get_contents($base_dir.'/'.$entry.'/tours.json'), true);
            	if($tours){
            		foreach($tours as $tour_id=>$tour){
            		        if(isset($tour['date_start'])){
                            $table[$counter]['company'] = 'iff';
							$table[$counter]['shipid'] = $entry;
							$table[$counter]['shipname'] = $local_ships_list[$entry];
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

file_put_contents('cruises.json', json_encode($table));


$executionEndTime = microtime(true);
$seconds = ($executionEndTime - $executionStartTime)/60;
//echo 'Выполнено за '.$seconds.' сек.'.PHP_EOL;
file_put_contents('api.log', 'Выполнено за '.$seconds.' min.');

$sdate = date('Y-m-d H:i');

system ( 'cd '.$sdir.'; git add -A;' );
system ( 'cd '.$sdir.'; git commit -a -m "Updated db '.$sdate.'";' );
system ( 'cd '.$sdir.'; git push origin master ;' );



?>
