asdasdsd
<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
$header = 'From: "christoph_heidelmann@gmx.de' . "\r\n" .
                'To: christoph.heidelmann@facebook.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion() . "\r\n" .
                'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                'MIME-Version: 1.0' . "\r\n";
print_r(mail("christoph.heidelmann.1@facebook.com", "test", "test", $header)) ;
 
?>
