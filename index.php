<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
session_start();
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml" class="no-js"> <!--<![endif]-->
    <head>
        <title>PMS</title>
        <meta charset="utf-8"/>

        <link rel="stylesheet" href="css/normalize.min.css"/>
        <link rel="stylesheet" href="css/main.css"/>
        <link rel="stylesheet" href="css/stylesheet.css"/>
        <link rel="stylesheet" href="css/flick/jquery-ui.min.css"/>
        <link rel="stylesheet"  href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>

        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>');</script>
        <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script src="js/vendor/jquery.dataTables.min.js"></script>
        <script src="js/vendor/jquery-ui.min.js"></script>
        <script src="js/helper.js"></script>
    </head>
    <body>
        <!--[if lt IE 7]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
        <?php
            require_once("js/facebook.php");
        ?>
        <div id="top_header">
            <div class="menu">
                <div id="user_info">
                    <img src=''/>
                    <span class="username"></span>
                </div>
                <div id="admin_panel" style="display:none">
                    <a href="#" class="page-link" onclick="load('adminUsers')" >Administer Users</a>
                </div>
                <a href="#" class="page-link" onclick="load('posts')" >Posts</a>
                <a id="login_button" class="login" onclick="login()" href="#">Login</a>
                <a id="logout_button" class="login" onclick="logout()" href="#">Logout</a>
            </div>
        </div>
        <div id="content" style="display:none">
        </div>
        <div id="error_dialog">

        </div>
        <div id="loading_screen">
            <div id="loading_icon">
            </div>
        </div>
    </body>
</html>