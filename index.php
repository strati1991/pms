<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
session_start();
require_once("backend/database_functions.php");
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html xmlns="http://www.w3.org/1999/xhtml" prefix="fb: http://www.facebook.com/2008/fbml" class="no-js" lang="de"> <!--<![endif]-->
    <head>
        <title>PMS</title>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="css/bootstrap-responsive.min.css" />
        <link rel="stylesheet" href="css/jquery.fileupload-ui.css"/>
        <link rel="stylesheet" type="text/css" href="css/flick/jquery-ui.min.css"/>
        <link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"/>
        <link rel="stylesheet" type="text/css" href="css/stylesheet.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css" />
        <link rel="stylesheet" type="text/css" href="css/bootstrap-datetimepicker.min.css" />
        <link rel="stylesheet" type="text/css" href="css/bootstrap-multiselect.css"  />
        <link rel="stylesheet" type="text/css" href="css/jquery.pnotify.default.css" />
        <link rel="stylesheet" type="text/css" href="css/fullcalendar.css" />
        <link rel="stylesheet" type="text/css" href="css/bootstrap-tagmanager.css" />
    </head>
    <body data-twttr-rendered="true">
        <!--[if lt IE 9]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner" id="navbar">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div id="user-info" class="brand">
                        <img class="user-icon" src="img/no-image.gif" alt="img/no-image.gif"/>
                        <span class="username"></span>
                    </div>
                    <div class="nav-collapse collapse">
                        <ul class="nav pull-left">
                            <li><a id="admin-users-button" style="display:none" href="#" onclick="helper.collapseMenu();
                                    helper.load('adminUsers')" >Administer Users</a></li>
                            <li><a id="posts-button" href="#" style="display:none"  onclick="helper.collapseMenu();
                                    helper.load('posts')" >Posts</a></li>
                            <li><a id="calendar-button" href="#" style="display:none"  onclick="helper.collapseMenu();
                                    helper.load('calendar')" >Kalender</a></li>
                        </ul>

                        <ul class="nav pull-right">
                            <li><a id="login-button"  href="#" onclick="helper.collapseMenu();
                                    login();" style="display:none">Login</a></li>
                            <li><a id="logout-button"  href="#" onclick="helper.collapseMenu();
                                    logout()" style="display:none">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" id="inner-content">
            <div class="row">
                <div class="span12">
                    <div style="display:none" id="alert-message-send-dialog" class="alert alert-block alert-error">
                        <button type="button" class="close" data-dismiss="alert">x</button>
                        Es gab einen Fehler beim versenden der Nachricht!
                    </div>
                    <div style="display:none" id="alert-auth-dialog" class="alert alert-block alert-error">
                        <button type="button" class="close" data-dismiss="alert">x</button>
                        Sie sind nicht authorisiert!
                    </div>
                    <div style="display:none" id="alert-database-dialog" class="alert alert-block alert-error">
                        <button type="button" class="close" data-dismiss="alert">x</button>
                        Es gab einen internen Fehler!
                    </div>
                </div>

                <div class="span12" id="content" style="display:none;min-height: 500px;">

                </div>
            </div>
        </div>
        <div id="notifications" style="display:none">

        </div>
        <div id="loading-screen">
            <div id="loading-icon">
            </div>
        </div>
        <div id="modal-dialog" class="modal hide fade">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" onclick="$('.modal-body').css('overflow-y', 'scroll');" aria-hidden="true">x</button>
                <h3 id="modal-header"></h3>
            </div>
            <div id="modal-content" class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="modal-close" data-dismiss="modal" class="btn">Abbrechen</a>
                <a href="#" id="modal-save-changes" style="display:none" class="btn btn-primary"></a>
            </div>
        </div>
        <script type="text/javascript" src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>');</script>
        <script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script type="text/javascript" src="js/vendor/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/vendor/jquery.pnotify.min.js"></script>
        <?php
        require_once("js/helper.php");
        require_once("js/facebook.php");
        ?>
        <script>
            //init
            $(function() {
                $.ajaxSetup({
                    cache: true
                });
                $("#close").bind("click", function() {
                    $('#modal-dialog').modal('hide');
                });
            });
            
        </script>
    </body>
</html>