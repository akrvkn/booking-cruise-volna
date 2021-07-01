<?php
$mysqli = new mysqli('localhost', 'glonass', 'kornet', 'glonass');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
			}
			
$q = 'SELECT * FROM online';
$res = $mysqli->query($q);
$arr = array();
while($row=$res->fetch_assoc()){
	$arr[]=$row;
}

//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET');
//header('Access-Control-Allow-Headers: Content-Type');
$ships =  json_encode($arr);
file_put_contents('ships.txt', $ships)
