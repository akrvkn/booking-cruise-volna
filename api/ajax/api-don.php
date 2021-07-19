<?php
        define('URL_API', 'https://doninturflot.com/local/modules/codencode.api.v1/entry_point.php');
        define('TOKEN', 'e0a97b55-9c82-40c6-a946-b5acca183999');
        $arParams = [
            'token'   => TOKEN,
'controller' => 'ship',
'action' => 'getCollection'
];
$ch = curl_init(URL_API);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$json = curl_exec($ch);
curl_close($ch);
file_put_contents('don-ships.json', $json);





        //define('URL_API', 'https://doninturflot.com/local/modules/codencode.api.v1/entry_point.php');
        //define('TOKEN', 'UF_CODENCODE_API_VERSION_ONE_TOKEN');
        $arParams = [
            'token'   => TOKEN,
'controller' => 'type_cabin',
'action' => 'getCollection'
];
$ch = curl_init(URL_API);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$json = curl_exec($ch);
curl_close($ch);
//$arFields = json_decode($json, true);
file_put_contents('don-cabins.json', $json);

?>
