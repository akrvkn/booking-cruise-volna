<?php
$headers = get_headers("https://vodohod.com/upload/iblock/678/6781949a76e8b837acd6dd66c503df03.jpg", 1);
//$redirectUrl = $headers['Location'];

// get the filesize
//$headers = get_headers($redirectUrl, 1);
//$filesize = $headers["Content-Length"];
//echo $filesize;
echo $headers['Content-Length'];
?>
