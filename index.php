<?php
session_start();
require_once("backend/database_functions.php");
require_once("backend/facebook.php");
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
$action = $_GET['action'];
if($action == "logout"){
    $facebook->destroySession();
    session_destroy();
    header('Location: http://pms.social-media-hosting.com/');
    exit();
}
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
        <link rel="stylesheet"  href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>
        
        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>');</script>
        <script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
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
                if (isset($loginUrl)) {
                    ?>
                    <a id="login_button" href="<?= $loginUrl ?>">Login</a>
                    <?php
                } else {
                    if (!isset($_SESSION['id'])) {
                        $_SESSION['id'] = $facebook->getUser();
                        $_SESSION['role'] = userRole($_SESSION['id']);
                        $data = $facebook->api('/me?fields=picture.height(16).width(16),name', 'GET');
                        $_SESSION['image'] = $data['picture']['data']['url'];
                        $_SESSION['name'] = $data['name'];
                    }
                    ?>
                    <div id="user_info">
                        <img src='<?= $_SESSION['image'] ?>'/>
                        <span class="username"><?= $_SESSION['name'] ?></span>
                    </div>
                    <?php
                    if ($_SESSION['logoutUrl'] ) {
                        ?>
                        <a id="login_button" href="<?= $_SESSION['logoutUrl'] ?>">Logout</a>
                        <?php
                    }
                }
                if ($_SESSION['role'] == 1) {
                    ?>
                    <div id="admin_panel">
                        <a href="?page=adminUsers">Administer Users</a>
                    </div>
                    <?php
                }
                if (isset($_SESSION['role'])) {
                    ?>
                    <a href="?page=posts">Posts</a>
                    <?php
                }
                ?>
                    
            </div>
        </div>
        <div id="content">
            <?php
            $page = $_GET['page'];
            if (empty($page)) {
                include("views/welcome.html");
            } else if ($page == "adminUsers") {
                if ($_SESSION['role'] > 0) {
                    include("views/adminUsers.php");
                } else {
                    include("views/notAuthorized.html");
                }
            } else if ($page == "posts") {
                include("views/posts.php");
            }
            ?>
        </div>

    </body>
</html>