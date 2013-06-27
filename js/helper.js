
function load(page) {
    $("#content").hide()
    $("#loading_screen").show();
    $.ajax("/views/" + page + ".php").done(function(response) {
        $("#content").html(response);
        $("#loading_screen").fadeOut();
        $("#content").fadeIn();
    });
}
function ajaxAPI(facebook, callback) {
    $.getJSON(
            "/backend/phpProxy.php?api=" + escape(facebook),
            callback
            );
}
function createSession(callback) {
    $.ajax("/backend/ajax_requests.php?action=createSession").done(callback);
}
function destroySession(callback) {
    $.ajax("/backend/ajax_requests.php?action=destroySession").done(callback);
}
function reloadPage(){
    window.location.href = "http://pms.social-media-hosting.com/";
}
function error(message) {
    $("#error_dialog").html(message);
    $("#error_dialog").dialog({
        height: 'auto',
        modal: true
    });
}
function isSetProperSubset(subset, superset) {
    // first check lengths
    if (subset.length > superset.length) {
        return false;
    }

    var lookup = {};

    for (var j in superset) {
        lookup[superset[j]] = superset[j];
    }

    for (var i in subset) {
        if (typeof lookup[subset[i]] == 'undefined') {
            return false;
        }
    }
    return true;
}


