<?php
require_once("backend/config.php");
?>
<script>
    var view = { init: function(){}};
    function load(page,callback) {
        $("#content").hide()
        $("#loading-screen").show();
        $.ajax("/views/" + page + ".php").done(function(response) {
            $("#content").html(response);
            $("#loading-screen").fadeOut();
            view.init();
            $("#content").fadeIn();
            if(callback){
                callback();
            }
            
        });
    }
    function ajaxFAPI(facebook, callback) {
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
    function reloadPage() {
        window.location.href = "http://pms.social-media-hosting.com/";
    }
    function error(message) {
        $("#error_dialog").html(message);
        $("#error_dialog").dialog({
            height: 'auto',
            modal: true
        });
    }
    function deleteCookie() {
        document.cookie = 'PHPSESSID=';
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
    function handleError(error, callback) {
        error = error.toString();
        if (error === "<?= $errors['VALUE'] ?>") {
            $("#alert-auth-dialog").fadeIn();
            return;
        }
        if (error === "<?= $errors['DATABASE_CON'] ?>") {
            $("#alert-database-dialog").fadeIn();
            return;
        }
        if (error === "<?= $errors['NOT_IN_DATABASE'] ?>") {
            $("#login-button").hide();
            $("#logout-button").show();
            load("register");
            return;
        }
        if (error === "<?= $errors['EMAIL'] ?>") {
            $("#alert-message-send-dialog").fadeIn();
            return;
        }
        if (error === "<?= $errors['NOT_A_PAGE'] ?>") {
            $("#modal-dialog #alert-not-a-page-dialog").fadeIn();
            return;
        }
        if (error === "<?= $errors['NOT_A_USER'] ?>") {
            $("#modal-dialog #alert-not-a-user-dialog").fadeIn();
            return;
        }
        if(callback){
            callback();
        }
    }
    function showModal(data,callback) {
        $("#modal-content").html(data.content);
        $("#modal-header").html(data.title);
        $("#modal-save-changes").html(data.saveLabel);
        if (data.saveLabel) {
            $("#modal-save-changes").show();
        }
        if (data.closeLabel) {
            $("#modal-close").html(data.closeLabel);
        } else {
            $("#modal-close").html("Abbrechen");
        }
        $("#modal-save-changes").unbind();
        $("#modal-save-changes").bind("click", data.saveFunction);
        $("#modal-dialog").modal("show");
        if(callback){
            callback();
        }
    }
    function showNotifications(){
        $('#notification-button').popover({
            placement:"bottom",
            html:true,
            content:$("#notifications").html()
        });
    }
</script>
