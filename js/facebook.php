<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once("backend/config.php");
?>
<div id="fb-root"></div>
<script>
    var accessToken = null;
    window.fbAsyncInit = function() {
        FB.init({
            appId: '<?= $config['appId'] ?>',
            channelUrl: '/channel.html',
            status: true, // check login status
            cookie: true, // enable cookies to allow the server to access the session
            oauth: true
        });
        helper.loading();
        FB.getLoginStatus(function(response) {
            console.log(response);
            if (response.status == 'connected') {
                accessToken = response.authResponse.accessToken;
                updateUserAndMenu();
                $('#login-button').hide()
                $('#logout-button').show();
            } else if (response.status == 'not_authorized') {
                login();
                $('#login-button').show()
                $('#logout-button').hide();
            } else {
                login();
                $('#login-button').show()
                $('#logout-button').hide();
            }
        });
    };

    (function() {
        var e = document.createElement('script');
        e.src = document.location.protocol + '//connect.facebook.net/de_DE/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
    }());

    function login() {
        helper.loading();
        FB.login(function(response) {
            if (response.authResponse) {
                var uid = response.authResponse.userID;
                accessToken = response.authResponse.accessToken;
                helper.loading();
                $.ajax("/backend/ajax_users.php?action=getUserRole&id=" + uid).done(function(response) {
                    console.log(response);
                    if (response != "0") {
                        FB.login(function(response) {
                            if (response.authResponse) {
                                accessToken = response.authResponse.accessToken;
                                updateUserAndMenu();
                            } else {
                                error('User cancelled login or did not fully authorize.');
                                helper.load("welcome");
                            }
                        }, {scope: "<?= $config['scope_manager'] ?>"});
                    } else {
                        updateUserAndMenu();
                    }
                });
            } else {
                error('User cancelled login or did not fully authorize.');
                helper.load("welcome");
            }
        }, {scope: "<?= $config['scope'] ?>"});
    }
    function updateUserAndMenu() {
        helper.loading();
        helper.ajaxFAPI("/me?fields=picture.height(24).width(24),name&access_token=" + accessToken, true, function(response) {
            var _response = $.parseJSON(response);
            helper.createSession(function(response) {
                helper.handleError(response, function() {
                    response = $.parseJSON(response);
                    if (parseInt(response.role) > 0) {
                        $("#admin-users-button").show();
                    }
                    $("#login-button").hide();
                    $("#logout-button").show();
                    $("#posts-button").show();
                    $("#user-info img").attr("src", _response.picture.data.url);
                    $(".username").html(_response.name);
                    $("#notification-button").show();
                    $("#nav").fadeIn();
                    $("#user-info").fadeIn();
                    $("#calendar-button").fadeIn();
                    helper.getNotifications();
<?php if (!$_GET['showpost']) {
    ?>
                        helper.load("welcome");
<?php }
?>
                    helper.finished();
                });
            });
        });
    }
    function logout() {
        FB.logout(function(response) {
            helper.deleteCookie();
            helper.destroySession(helper.reloadPage());
        });
    }
</script>