window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
        appId: '192351964261671', // App ID from the app dashboard
        channelUrl: '//pms.social-media-hosting.com/channel.html', // Channel file for x-domain comms
        status: true, // Check Facebook Login status
        xfbml: true, // Look for social plugins on the page
        cookie: true
    });

    $(document).trigger('fbInit'); // trigger event

    FB.Event.subscribe('auth.authResponseChange', function(response) {
        if (response.status === 'connected') {
            isLoggedIn();
        } else if (response.status === 'not_authorized') {
            FB.login();
        } else {
            FB.login();
        }
    });
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            var uid = response.authResponse.userID;
            var accessToken = response.authResponse.accessToken;
        } else if (response.status === 'not_authorized') {
            $("#login").fadeIn();
        } else {
            $("#login").fadeIn();
        }
    });
};
// Load the SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/de_DE/all.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
function isLoggedIn() {
    FB.api('/me', function(response) {
        var login_info = ""
        FB.api("/me/picture?width=32&height=32", function(response) {
            login_info = "<img src='" + response.data.url + "'/>";
            FB.api("/me?fields=name", function(response) {
                login_info += "<span>Hallo " + response.name + " du bist jetzt über Facebook eingeloggt.</span>";
                $("#login").html(login_info);
                $("#login").animate({
                    width: "100%",
                    height: "50px",
                    top: "-40px",
                    margin: "0",
                    padding: "50px 0 0 15px",
                    "-moz-border-radius": "0",
                    "-webkit-border-radius": "0",
                    "-khtml-border-radius": "0",
                    "border-radius": "0"
                },1000);
                $("#login").fadeIn();
                getUserRole(function(role){
                    if(role === 1){
                        $("#admin_panel").fadeIn();
                    }
                });
            });
        });

    });
}