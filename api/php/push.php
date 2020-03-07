<?php
$date = date('Y-m-d H:i');
$dir = dirname(__FILE__);
system ( 'cd '.$dir.'; git add -A;' );
system ( 'cd '.$dir.'; git commit -a -m "Updated db '.$date.'";' );
system ( 'cd '.$dir.'; git push origin master ;' );

?>
