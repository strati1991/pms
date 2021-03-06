<?php
require_once("backend/config.php");
?>
<script>
    var view = {init: function() {
        }};
    var helper = {
        loading: function() {
            $("#loading-screen").show();
        },
        finished: function() {
            $("#content").show();
            $("#loading-screen").fadeOut();
        },
        load: function(page, callback) {
            helper.loading()
            $.ajax("/views/" + page + ".php").done(function(response) {
                $("#content").html(response);
                view.init();
                if (callback) {
                    callback();
                }
                $('.view-content .has-tooltip-bottom').tooltip({
                    placement: 'bottom',
                    html: true
                });
                $('.view-content .has-tooltip-left').tooltip({
                    placement: 'left',
                    html: true
                });
                $('.view-content .has-tooltip-top').tooltip({
                    placement: 'top',
                    html: true
                });
                $('.view-content .has-tooltip-right').tooltip({
                    placement: 'right',
                    html: true
                });

            });
        },
        ajaxFAPI: function(facebook, async, callback) {
            $.ajax({datatype: "json",
                url: "/backend/phpProxy.php?api=" + escape(facebook),
                async: async
            }).done(callback);
        },
        createSession: function(callback) {
            $.ajax("/backend/ajax_requests.php?action=createSession").done(callback);
        },
        destroySession: function(callback) {
            $.ajax("/backend/ajax_requests.php?action=destroySession").done(callback);
        },
        reloadPage: function() {
            window.location.href = "http://pms.social-media-hosting.com/";
        },
        error: function(message) {
            $("#error_dialog").html(message);
            $("#error_dialog").dialog({
                height: 'auto',
                modal: true
            });
        },
        deleteCookie: function() {
            document.cookie = 'PHPSESSID=';
        },
        isSetProperSubset: function(subset, superset) {
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
        },
        handleError: function(error, callback) {
            helper.finished()
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
            if (callback) {
                callback();
            }
        },
        showModal: function(data, callback) {
            $("#modal-content").html(data.content);
            $("#modal-header").html(data.title);
            $("#modal-save-changes").html(data.saveLabel);
            if (data.saveLabel) {
                $("#modal-save-changes").show();
            }
            if (!data.hideCloseButton) {
                $("#modal-close").show();
                if (data.closeLabel) {
                    $("#modal-close").html(data.closeLabel);
                } else {
                    $("#modal-close").html("Abbrechen");
                }
            } else {
                $("#modal-close").hide();
            }
            $("#modal-save-changes").unbind();
            if (!data.saveFunction) {
                $("#modal-save-changes").bind("click", function() {
                    $("#modal-dialog").modal("hide");
                });
            } else {
                $("#modal-save-changes").bind("click", data.saveFunction);
            }
            if (data.preShowFunction) {
                data.preShowFunction();
            }
            $('.has-tooltip-bottom').tooltip({
                placement: 'bottom',
                html: true
            });
            $('.has-tooltip-left').tooltip({
                placement: 'left',
                html: true
            });
            $('.has-tooltip-top').tooltip({
                placement: 'top',
                html: true
            });
            $('.has-tooltip-right').tooltip({
                placement: 'right',
                html: true
            });
            $("#modal-dialog").modal("show");
            if (callback) {
                callback();
            }
        },
        collapseMenu : function(){
            $('.btn.btn-navbar').click();
        },
        showPost: function(id) {
            $('#notification-button').popover("hide");
            helper.load("posts", function() {
                helper.finished()
                posts.view(id);
            });

        },
        getNotifications: function() {
            $.ajax({
                type: "GET",
                url: "backend/ajax_requests.php?action=getNotifications",
                success: function(data) {
                    setTimeout(helper.getNotifications, 20000);
                    data = $.parseJSON(data);
                    helper.handleError(data, function() {
                        $.each(data.notifications, function(index, value) {
                            if (value.type == <?= $notifications['comment'] ?>) {
                                $.pnotify({
                                    title: 'Neue Kommentar',
                                    text: "<a href='#' class='notification-link' onclick='posts.showPost(" + value.dataID + ")'>Neuer Kommentar von " + value.dataText + "</a>",
                                });
                            }
                            if (value.type == <?= $notifications['post_added'] ?>) {
                                $.pnotify({
                                    title: 'Neue Post',
                                    text: "<a href='#'  class='notification-link' onclick='posts.showPost(" + value.dataID + ")'>Neuer Post " + value.dataText + "</a>",
                                });
                            }
                            if (value.type == <?= $notifications['post_deletet'] ?>) {
                                $.pnotify({
                                    title: 'Post wurde gelöscht',
                                    text: "Post " + value.dataText + " wurde gelöscht",
                                });
                            }
                            s
                            if (value.type == <?= $notifications['post_updated'] ?>) {
                                $.pnotify({
                                    title: 'Post wurde verändert',
                                    text: "<a href='#'  class='notification-link' onclick='posts.showPost(" + value.dataID + ")'>Post " + value.dataText + " wurde verändert </a>",
                                });

                            }
                        });
                    });
                    $(".notification-link").bind("click", function() {
                        $(this).parent().parent().find(".icon-remove").click();
                    });
                }
            });
        }
    }
</script>
