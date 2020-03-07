<?php
/*ini_set('display_errors', '1');
ini_set("date.timezone", "Europe/Moscow");
$executionStartTime = microtime(true);

$base_dir = 'data';


$API_BASE = 'https://api.infoflot.com/JSON/f438cedcb449037583a8f84d5f5a3a3ff34139ab/';
$excursions = $API_BASE.'Excursions/';*/
$counter = 0;
$all = 0;
if ($handle = opendir($base_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && is_dir($base_dir.'/'.$entry) && is_file($base_dir.'/'.$entry.'/tours.json')) {
            	
            	$tours = json_decode(file_get_contents($base_dir.'/'.$entry.'/tours.json'), true);
            	if($tours){
            		foreach($tours as $tour_id=>$tour){
            			$all++;
						if(mb_stristr($tour['cities'], 'Москва', false, 'UTF-8')){
						
						$counter++;
						$schedule = @file_get_contents($excursions.$entry.'/'.$tour_id);
							if($schedule){
								//echo $entry.'->'.$tour_id.PHP_EOL;
								file_put_contents($base_dir.'/'.$entry.'/'.$tour_id.'.json', $schedule);
							}
						}
		
					}
			
            	}
            	
            
        }
    }
    closedir($handle);
}

//echo $counter.PHP_EOL;
//echo $all.PHP_EOL;

$executionEndTime3 = microtime(true);

$seconds3 = $executionEndTime3 - $executionStartTime;
 
//Print it out
file_put_contents('logs/routes.log', "Загружено $counter круизов из $all. Выполнено ".date('Y-m-d H:i:s')." за $seconds3 секунд.");

require('./cabins.php');
