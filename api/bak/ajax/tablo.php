<?php
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET');
//header('Access-Control-Allow-Headers: Content-Type');
$rasp = file_get_contents('http://tablo.superuser.su/monitor/screen.php');
file_put_contents('tablo.html', $rasp);

?>