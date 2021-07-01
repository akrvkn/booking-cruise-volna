<?php
$page = 1;
$per_page = 10;
$cat = 43;

	$cat_obj = json_decode(@file_get_contents('https://www.mosturflot.ru/wp-json/wp/v2/categories/'.$cat));
	$spec = json_decode(@file_get_contents('https://www.mosturflot.ru/wp-json/wp/v2/posts?categories='.$cat.'&page='.$page.'&per_page='.$per_page));
	$out = array();
	$count = 0;
	$img_src = '/assets/images/logo_mtf.png';
	foreach($spec as $val){	
		if((int)$val->featured_media > 0){
			$resource = @file_get_contents('https://www.mosturflot.ru/wp-json/wp/v2/media/'.$val->featured_media);
			if($resource!==FALSE){
				$media = json_decode($resource);
				$img_src = $media->media_details->sizes->full->source_url;
			}
		}
		$out[$count]['id'] = $val->id;
		$out[$count]['date'] = $val->date;
		$out[$count]['title'] = $val->title->rendered;
		$out[$count]['excerpt'] = $val->excerpt->rendered;
		$out[$count]['image'] = $img_src;
		$out[$count]['category'] = $cat_obj->name;
		$out[$count]['link'] = $val->link;
		$count++;	
	}
	usort($out, function ($item1, $item2) {
	    return $item2['date'] <=> $item1['date'];
	});
	
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET');
	header('Access-Control-Allow-Headers: Content-Type');
	$output = json_encode($out);
	echo $output;

?>
