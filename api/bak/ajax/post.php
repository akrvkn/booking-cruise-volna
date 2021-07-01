<?php
$id = 1;
$url = '';
if(isset($_GET['id'])){
	$id = $_GET['id'];
	$url = 'https://www.mosturflot.ru/wp-json/wp/v2/posts/'.$id;
}elseif(isset($_GET['slug'])){
	$slug = $_GET['slug'];
	$url = 'https://www.mosturflot.ru/wp-json/wp/v2/pages?slug='.$slug;
}else{
	return;
}

$post = @file_get_contents($url);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

echo $post;

?>
