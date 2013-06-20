<?php
require_once("facebook-sdk/facebook.php");
$config = array();
$config['appId'] = '192351964261671';
$config['secret'] = '2c0ce846356ab46e072b68aae2bcc3db';
$facebook = new Facebook($config);

if (!isset($_SESSION['id'])) {
    try{
        $user = $facebook->getUser();
    }catch (Exception $e){
        
    }
    if ($user) {
        $params = array(
            'next' => 'http://pms.social-media-hosting.com?action=logout'
        );
        $_SESSION['logoutUrl'] = $facebook->getLogoutUrl($params);
    } else {
        $params = array(
            'scope' => 'email,manage_pages',
            'redirect_uri' => 'http://pms.social-media-hosting.com/'
        );
        $loginUrl = $facebook->getLoginUrl($params);
    }
}


?>
