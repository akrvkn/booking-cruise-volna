<?php

// массив параметров
$params = array(
'v' => '5.126', // версия вк апи
'scope' => 'wall,photos,video', // доступ к стене,фото,видео
'access_token' => 'e5816e104951a9c82238d28db42335193ba3bacf49ce55855100abb114f3daf4d6cee9ff643c60164c45e', // тут ваш access_token
'owner_id' => '-202152261', // ID ГРУППЫ С ОБЯЗАТЕЛЬНЫМ ЗНАКОМ "-" ПЕРЕД ЧИСЛОМ!
'attachments' => 'https://www.cruise-volna.ru/baikal8', // ссылка на вашу страницу, которая будет расшарена на вашей стене группы
'message' => 'Вокруг Байкала за 7 дней' // тут текст который будет в карточке расшаривания, если оставить пустым то будет дублировать title со страницы в параметре attachments
);
	
	
$url = 'https://api.vk.com/method/wall.post?' . http_build_query($params);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);
	
print_r($response);

?>
