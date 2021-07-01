<?php
if (isset($_GET['src'])) {
header('Content-Type: image/jpeg');
$image = file_get_contents('ftp://91.221.70.156/MTF/' . $_GET['src'].'/online/online.jpg');
echo  $image;
die();

} else {

 header('HTTP/1.1 404 Not Found');
 die('404 Image not found');

}





?>