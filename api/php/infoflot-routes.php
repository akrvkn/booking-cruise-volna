<?php

$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$ships = $API_BASE.'Ships/';
$cabins = $API_BASE.'Cabins/';
$cabins_photo = $API_BASE.'CabinsPhoto/';
$shipsimages = $API_BASE.'ShipsImages/';
$desc = $API_BASE.'ShipsDescription/';
$tours_base = $API_BASE.'Tours/';
$schemes = $API_BASE.'ShipsSchemes/';
$excursions = $API_BASE.'Excursions/';

$base_dir = 'data';

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
