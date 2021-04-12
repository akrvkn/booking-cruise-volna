<?php
$RESTAPI_TOKEN = '407c8c353a23a14d40479eb4e4290a8a6d32b06b';
$API_BASE = 'https://restapi.infoflot.com/';
$now = date('Y-m-d');
$nums = [478,498,83,4,38,571,7,1,487,3,64,260];
$iff_ships = implode(',',$nums);;

$opts = array(
'http' => array('ignore_errors' => true),
'ssl' => array(
'verify_peer' => false,
'verify_peer_name' => false
)
);

$context = stream_context_create($opts);

$dir = 'data';
/**if(is_dir('data')){
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
}*/

$base_dir = 'data';

if(!is_dir($base_dir)){
		mkdir($base_dir);
	}
$iff_dir = 'data/infoflot';
if(!is_dir($iff_dir)){
		mkdir($iff_dir);
	}	
	
$ships_data = [];
	
foreach($nums as $id){
  $data = [];
  $single = json_decode(file_get_contents($API_BASE.'ships/'.$id.'?key='.$RESTAPI_TOKEN, false, $context), true);
  $data['id'] = $single['id'];
  $data['latLng'] = ['55.850939', '37.466287'];
  $data['name'] = $single['name'];
  $data['company'] = 'Инфофлот';
  $data['category'] = count($single['decks']).'-палубный';
  $data['description'] = strip_tags($single['description']);
  $data['cabins'] = [];
  if(!is_dir($iff_dir.'/'.$single['id'])){
		mkdir($iff_dir.'/'.$single['id']);
		mkdir($iff_dir.'/'.$single['id'].'/cabins');
	}	
	file_put_contents($iff_dir.'/'.$single['id'].'/'.$single['id'].'.jpg', file_get_contents($single['files']['photo']['path'], false, $context));
	$cabin_data = [];
  foreach($single['cabins'] as $cabin){   
    $cabin_data['id'] = $cabin['typeId'];
    $cabin_data['category'] = $cabin['typeName'];
    $cabin_data['description'] = $cabin['typeFriendlyName'];
    //$cabin_data['photo'] = $cabin['photos'][0]['filename'] == null ? '' : $cabin['photos'][0]['filename'];
    $data['cabins'][$cabin['typeId']] = $cabin_data;
    if(!is_file($iff_dir.'/'.$single['id'].'/cabins/'.$cabin['typeId'].'.jpg') && isset($cabin['photos'][0]['filename'])){
      file_put_contents($iff_dir.'/'.$single['id'].'/cabins/'.$cabin['typeId'].'.jpg', file_get_contents($cabin['photos'][0]['filename'], false, $context));
    }
  }	
  
  $ships_data[] = $data;
}

file_put_contents('ships-infoflot.json', json_encode($ships_data));

?>
