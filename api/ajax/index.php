<?php

$url=$_GET['vdh'];

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
echo file_get_contents('https://www.rech-agent.ru/api/v1/'.$url);
?>
