<?php

$v = '5.126';
$group_id     = '202152261';
$access_token = 'e5816e104951a9c82238d28db42335193ba3bacf49ce55855100abb114f3daf4d6cee9ff643c60164c45e';
$message      = 'Щедрый январь.';
$image        = 'january.jpg';
 
// Получение сервера vk для загрузки изображения.
$server = file_get_contents('https://api.vk.com/method/photos.getWallUploadServer?group_id=' . $group_id . '&access_token=' . $access_token . '&v=' .$v);
$server = json_decode($server);

if (!empty($server->response->upload_url)) {
	// Отправка изображения на сервер.
	if (function_exists('curl_file_create')) {
		$curl_file = curl_file_create($image);
	} else {
		$curl_file = '@' . $image;
	}
 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $server->response->upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => $curl_file));
	$upload = curl_exec($ch);
	curl_close($ch);
 
	$upload = json_decode($upload);
	if (!empty($upload->server)) {
		// Сохранение фото в группе.
		$save = file_get_contents('https://api.vk.com/method/photos.saveWallPhoto?group_id=' . $group_id . '&server=' . $upload->server . '&photo=' . stripslashes($upload->photo) . '&hash=' . $upload->hash . '&access_token=' . $access_token . '&v=' . $v);
		$save = json_decode($save);

		if (!empty($save->response[0]->id)) {
			// Отправляем сообщение.
			$params = array(
				'v'            => $v,
				'scope' => 'wall,photos,video',
				'access_token' => $access_token,
				'owner_id'     => '-' . $group_id, 
				'from_group'   => '1', 
				'message'      => $message,
				'attachments'  => 'photo' . $save->response[0]->owner_id . '_' . $save->response[0]->id
			);
			
			file_get_contents('https://api.vk.com/method/wall.post?' . http_build_query($params));
		}
	}
}

?>
