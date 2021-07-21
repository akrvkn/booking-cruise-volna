<?php
$str = '2-местная одноярусная каюта ***';
$patterns = array ('/\s+\*+/', '/\s+каюта/', '/\s+одноярусная/');
                    $replace = array ('', '', '');
                    echo  preg_replace($patterns, $replace, $str);
?>
