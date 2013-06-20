window.fbAsyncInit = function() {
    // init the FB JS SDK
    FB.init({
        appId: '192351964261671', // App ID from the app dashboard
        channelUrl: '//pms.social-media-hosting.com/channel.html', // Channel file for x-domain comms
        status: true, // Check Facebook Login status
        xfbml: true, // Look for social plugins on the page
        cookie: true
    });
    FB.Event.subscribe('auth.authResponseChange', function(response) {
        if (response.status === 'connected') {
        } else if (response.status === 'not_authorized') {
            FB.login();
        } else {
            FB.login();
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