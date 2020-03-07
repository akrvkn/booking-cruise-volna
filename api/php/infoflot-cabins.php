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

$ships_list = json_decode(file_get_contents($base_dir.'/ships.json'), true);

$count = 0;
$count_desc = 0;
$count_cab = 0;
$count_photos = 0;

foreach($ships_list as $ship_id=>$ship_name){
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

?>
