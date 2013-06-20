<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
session_start();
require_once("backend/facebook.php");
require_once("backend/database.php");
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml" class="no-js"> <!--<![endif]-->
    <head>
        <title>PMS</title>
        <link rel="stylesheet" href="css/normalize.min.css"/>
        <link rel="stylesheet" href="css/main.css"/>
        <link rel="stylesheet" href="css/stylesheet.css"/>

        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>');</script>
        <script src="js/vendor/jquery.dataTables.min.js"></script>
        <script src="js/facebook.js"></script>
        <script src="js/navigation.js"></script>
        <script src="js/main.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->        
        <div id="fb-root"></div>
        <div id="top_header">
            <div id="menu">
                <?php
                $me = null;
                try {
                    $me = $facebook->api('/me');
                    $_SESSION['id'] = $facebook->getUser();
                    $_SESSION['role'] = userRole($_SESSION['id']);
                } catch (FacebookApiException $e) {
                    $_SESSION['role'] = 0;
                }
                if ($me == null) {
                    ?>
                    <fb:login-button id="login_button" size="large" show-faces="false" width="200" max-rows="1" onlogin="window.location.reload(true);"></fb:login-button>
                    <?php
                } else {
                    $image = $facebook->api('/me?fields=picture.height(16).width(16)', 'GET');
                    $name = $facebook->api('/me?fields=name', 'GET');
                    ?>
                    <div id="user_info">

                        <img src='<?= $image['picture']['data']['url'] ?>'/>
                        <span class="username"><?= $name['name'] ?></span>
                    </div>
                    <?php
                }
                if ($_SESSION['role'] == 1) {
                    ?>
                    <div id="admin_panel">
                        <a href="javascript:adminUsers()">Administer Users</a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div id="content">

        </div>

    </body>
</html>