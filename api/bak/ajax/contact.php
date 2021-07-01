<?php
/**
 * EDIT THE VALUES BELOW THIS LINE TO ADJUST THE CONFIGURATION
 * EACH OPTION HAS A COMMENT ABOVE IT WITH A DESCRIPTION
 */
/**
 * Specify the email address to which all mail messages are sent.
 * The script will try to use PHP's mail() function,
 * so if it is not properly configured it will fail silently (no error).
 */
$mailTo     = 'mosturflot-2020@yandex.ru';
$mailCopy   = 'support@superuser.su';

/**
 * Set the message that will be shown on success
 */
$successMsg = 'Ваш ответ успешно отправлен';

/**
 * Set the message that will be shown if not all fields are filled
 */
$fillMsg    =  'Заполните все поля';

/**
 * Set the message that will be shown on error
 */
$errorMsg   = 'Ошибка, попробуйте ещё раз!';

/**
 * DO NOT EDIT ANYTHING BELOW THIS LINE, UNLESS YOU'RE SURE WHAT YOU'RE DOING
 */

?>
<?php
if(
    !isset($_POST['name']) ||
    !isset($_POST['email']) ||  
	!isset($_POST['phone']) ||
    empty($_POST['name']) ||
    empty($_POST['email'])

) {
	
	if( empty($_POST['name']) && empty($_POST['email']) ) {
		$json_arr = array( "type" => "error", "msg" => $fillMsg );
		echo json_encode( $json_arr );		
	} else {

		$fields = "";
		if( !isset( $_POST['name'] ) || empty( $_POST['name'] ) ) {
			$fields .= "Имя";
		}
		
		if( !isset( $_POST['email'] ) || empty( $_POST['email'] ) ) {
			if( $fields == "" ) {
				$fields .= "Email";
			} else {
				$fields .= ", Email";
			}
		}
		
		$json_arr = array( "type" => "error", "msg" => "Заполните поля ".$fields );
		echo json_encode( $json_arr );		
	
	}

} else {

	// Validate e-mail
	if ( $_POST['phone'] == '') {
		
		$msg = "Имя: ".$_POST['name']."\r\n";
		$msg .= "Email/Tel.: ".$_POST['email']."\r\n";
		if( isset( $_POST['msg'] ) && $_POST['msg'] != '' ) { $msg .= "Ответ: ".$_POST['msg']."\r\n"; }
		
		$success = @mail($mailTo, 'Конкурс на сайте mosturflot.ru', $msg, 'From: Конкурс<info@mosturflot.ru>');
		//$success2 = @mail($mailCopy, 'Конкурс на сайте mosturflot.ru', $msg, 'From: Конкурс<info@mosturflot.ru>');
		if ($success) {
			$json_arr = array( "type" => "success", "msg" => $successMsg );
			echo json_encode( $json_arr );
		} else {
			$json_arr = array( "type" => "error", "msg" => $errorMsg );
			echo json_encode( $json_arr );
		}
		
	} else {
 		$json_arr = array( "type" => "error", "msg" => "Введите правильный адрес E-mail!" );
		echo json_encode( $json_arr );	
	}

}
