<?php

//MOSTURFLOT
$mtf_ships_url = 'https://api.mosturflot.ru/v3/rivercruises/ships?filter[is-own]=1';
$mtf_images_base = 'https://api.mosturflot.ru/v3/rivercruises/ships/';

$opts = array(
'http' => array('ignore_errors' => true),
'ssl' => array(
'verify_peer' => false,
'verify_peer_name' => false
)
);

$context = stream_context_create($opts);

$base_dir = 'data';

if(!is_dir($base_dir)){
		mkdir($base_dir);
	}
$mtf_dir = 'data/mosturflot';
if(!is_dir($mtf_dir)){
		mkdir($mtf_dir);
	}		
	
$ships_data = [];

//https://api.vodohod.com/json/v2/ports.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7

$mtf_ships = json_decode(file_get_contents($mtf_ships_url, false, $context), true);
foreach( $mtf_ships['data'] as $board){
  if(!is_dir($mtf_dir.'/'.$board['attributes']['id'])){
		mkdir($mtf_dir.'/'.$board['attributes']['id']);
		mkdir($mtf_dir.'/'.$board['attributes']['id'].'/cabins');
	}	
  $data = [];
  $data['id'] = $board['attributes']['id'];
  $data['latLng'] = ['55.850939', '37.466287'];
  $data['name'] = $board['attributes']['name'];
  $data['company'] = 'Мостурфлот';
  $data['category'] = $board['attributes']['class-id'];
  $data['description'] = preg_replace('/\s+/', ' ', (strip_tags(preg_replace(array('/<a[^>]+>(.*)<\/a>/siU','/\r/', '/\n/', '/\t/', '/&nbsp;/'), '', html_entity_decode($board['attributes']['description'])))));
  $data['cabins'] = [];
  $images = json_decode(file_get_contents('https://api.mosturflot.ru/v3/rivercruises/ships/'.$board['attributes']['id'].'/images'), true);
  $title_image = $images['data'][0]['links']['image-url'];
  file_put_contents($mtf_dir.'/'.$board['attributes']['id'].'/'.$board['attributes']['id'].'.jpg', file_get_contents($title_image));
  $deckplan = file_get_contents('https://api.mosturflot.ru/v3/rivercruises/ships/'.$board['attributes']['id'].'/deckplan.svg');
  $png_pattern = '/href="(.*)\.(png|jpg)/';
  preg_match($png_pattern, $deckplan, $res);
  $ext = substr($res[2], -3);
  file_put_contents($mtf_dir.'/'.$board['attributes']['id'].'/decks.'.$ext, file_get_contents($res[1].'.'.$ext));
  $deckplan = preg_replace($png_pattern, 'href="decks.'.$ext, $deckplan);
  file_put_contents($mtf_dir.'/'.$board['attributes']['id'].'/deckplan.svg', $deckplan); 
	$cabins = []; 
	
	//cabins
	$ship_cabins = json_decode(file_get_contents('https://api.mosturflot.ru/v3/rivercruises/ships/'.$board['attributes']['id'].'/cabin-categories'), true);
	foreach($ship_cabins['data'] as $cabin){
	  $cabin_data = [];
	  $cabin_data['id'] = $cabin['attributes']['id'];
	  $cabin_data['category'] = $cabin['attributes']['name'];
	  $cabin_data['description'] = preg_replace('/\s+/', ' ', (strip_tags(preg_replace(array('/<a[^>]+>(.*)<\/a>/siU','/\r/', '/\n/', '/\t/', '/&nbsp;/'), '', html_entity_decode($cabin['attributes']['description'])))));
	  $cabins[] = $cabin_data;
	  $title_image = json_decode(file_get_contents('https://api.mosturflot.ru/v3/rivercruises/cabin-categories/'.$cabin['attributes']['id'].'/title-image'), true);
	  if(isset($title_image['data']['links']['image-url'])){
	    file_put_contents($mtf_dir.'/'.$board['attributes']['id'].'/cabins/'.$cabin['attributes']['id'].'.jpg', file_get_contents($title_image['data']['links']['image-url']));
	  }
	}
	
	
  $data['cabins'] = $cabins;
  $ships_data[] = $data;
}
file_put_contents('ships-mosturflot.json', json_encode($ships_data));

?>
