<?php
if (isset($_GET['id'])) {
header('Content-Type: image/jpeg');
$image = file_get_contents('https://booking.mosturflot.ru/rivercruises/' . $_GET['id'].'/deckplan');
echo  $image;
die();

} else {

 header('HTTP/1.1 404 Not Found');
 die('404 Image not found');

}

?>