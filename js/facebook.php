<?php
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
            xfbml: true  // parse XFBML
        });
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                var uid = response.authResponse.userID;
                accessToken = response.authResponse.accessToken;
                updateUserAndMenu();
                $('#login_button').hide()
                $('#logout_button').show();
            } else if (response.status === 'not_authorized') {
                load("welcome");
                $("#loading_screen").fadeOut();
                 $('#login_button').show()
                $('#logout_button').hide();
            } else {
                load("welcome");
                $("#loading_screen").fadeOut();
                $('#login_button').show()
                $('#logout_button').hide();
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
        FB.login(function(response) {
            if (response.authResponse) {
                accessToken = response.authResponse.accessToken;
                updateUserAndMenu();
            } else {
                error('User cancelled login or did not fully authorize.');
                load("welcome");
            }
        }, {scope: 'email,manage_pages'});
    }
    function updateUserAndMenu() {
        ajaxAPI("/me?fields=picture.height(16).width(16),name&access_token=" + accessToken, function(response) {
            createSession(function(response) {
                response = $.parseJSON(response);
                if (parseInt(response.role) > 0) {
                    $("#admin_panel").show();
                }
            });
            $(".page-link").fadeIn();
            $("#login_button").hide();
            $("#logout_button").show();
            $("#user_info img").attr("src", response.picture.data.url);
            $("#user_info .username").html(response.name);
            load("welcome");
        });
    }
    function logout(){
        FB.logout(function(response) {
          destroySession(reloadPage());
        });
    }

</script>