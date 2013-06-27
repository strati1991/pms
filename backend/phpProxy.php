<?php

ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
$handle = fopen("https://graph.facebook.com" . $_GET['api'], "r");

if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
?>
