view.init = function() {
    $('.dropdown input, .dropdown label').click(function(event) {
        event.stopPropagation();
    });
    $('#postlist').dataTable({
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": true,
        "fnInitComplete": function() {
            $('#postlist').fadeIn();
        }
    });
    $('#publish-date').datepicker();
};
var posts = {
    popoverPagesToggle: function(id) {
        if ($("#popover-pages-" + id).hasClass("has-popover")) {
            $("#popover-pages-" + id).removeClass("has-popover");
            $("#popover-pages-" + id).popover('destroy');
        } else {
            $(".popover").hide();
            helper.loading();
            $("#popover-pages-" + id).addClass("has-popover");
            $.ajax("backend/ajax_posts.php?action=getPostOnPages&id=" + id).done(function(response) {
                response = $.parseJSON(response);
                var popover = "<ul style='width:150px'>"
                $.each(response.pages, function(index, value) {
                    popover += "<li>" + value.pageName + "</li>";
                });
                popover += "</ul>";
                helper.finished();
                $("#popover-pages-" + id).attr("data-content", popover);
                $("#popover-pages-" + id).popover({
                    html: true,
                    placement: "left",
                    comntent: popover
                });
                $("#popover-pages-" + id).popover('show');

            });
        }

    },
    addComment: function(id, username) {
        if (!$("#modal-dialog #add-comment-button").hasClass("has-comment")) {
            $("#modal-dialog #add-comment-button").addClass("has-comment");
            $("#modal-dialog #add-comment-button").html("Speichern");
            $("#modal-dialog #comment").show();
        } else {
            helper.loading();
            $("#modal-dialog #add-comment-button").removeClass("has-comment");
            $("#modal-dialog #add-comment-button").html("Kommentar hinzufügen");
            $("#modal-dialog #comment").hide();
            $.ajax("backend/ajax_posts.php?action=addComment&postID=" + id + "&comment=" + escape($("#modal-dialog #comment").val())).done(function(response) {
                $("#modal-dialog #comments").append("<div class='comment' id='comment-" + id + "'>" +
                        "<img class='image_<?= $_SESSON['username'] ?>' src='img/ajax-loader.gif'>" +
                        "<p>" + username + "</p>" +
                        "<span></span>" +
                        "<a class='delete' href='#' onclick='deleteComment(" + id + ")'>x</a>" +
                        "</div>");
                $("#modal-dialog #comment-" + id + " span").text($("#modal-dialog #comment").val());
                ajaxFAPI("/" + username.replace(new RegExp('[_]', 'g'), ".") + "?fields=picture.height(24).width(24),name&access_token=" + accessToken, true, function(response) {
                    response = $.parseJSON(response);
                    $(".image_<?= $_SESSON['username'] ?>").attr("src", response.picture.data.url);
                });
                $("#modal-dialog #comments .comment").mouseover(function() {
                    $(this).find(".delete").show();
                });
                $("#modal-dialog #comments .comment").mouseout(function() {
                    $(this).find(".delete").hide();
                });
                helper.finished();
            });
        }

    },
    deleteComment: function(id) {
        $("#loading-screen").show();
        $.ajax("backend/ajax_posts.php?action=deleteComment&id=" + id).done(function() {
            $("#modal-dialog #comment-" + id).remove();
            helper.finished();
        });
    },
    view: function(id) {
        $("#loading-screen").show();
        $("#add-comment-button").attr("onclick", "addComment(" + id + ",'<?= $_SESSION['username'] ?>');");
        $.ajax("backend/ajax_posts.php?action=getPost&id=" + id).done(function(response) {
            _response = $.parseJSON(response);
            $.ajax("backend/ajax_posts.php?action=getPostOnPages&id=" + id).done(function(response) {
                response = $.parseJSON(response);
                var pages = "<ul style='width:150px'>"
                $.each(response.pages, function(index, value) {
                    pages += "<li>" + value.pageName + "</li>";
                });
                pages += "</ul>";
                $("#pages").html(pages);
            });
            $.ajax("backend/ajax_posts.php?action=getComments&id=" + id).done(function(response) {
                response = $.parseJSON(response);
                $("#comments").html("");
                var users = {};
                $.each(response.comments, function(index, value) {
                    var image_id = value.username.replace(new RegExp('[.]', 'g'), "_");
                    if (!(image_id in users)) {
                        users[image_id] = image_id;
                    }
                    ;
                    $("#comments").append("<div class='comment' id='comment-" + value.ID + "'>" +
                            "<img class='image_" + image_id + "' src='img/ajax-loader.gif'>" +
                            "<p>" + value.username.replace(new RegExp('[.]', 'g'), " ") + "</p>" +
                            "<span></span>" +
                            (("<?= $_SESSION['username'] ?>" === value.username) ? "<a class='delete' href='#' onclick='deleteComment(" + value.ID + ")'>x</a>" : "") +
                            "</div>");
                    $("#comment-" + value.ID + " span").text(value.text);
                });
                $.each(users, function(index, value) {
                    ajaxFAPI("/" + value.replace(new RegExp('[_]', 'g'), ".") + "?fields=picture.height(24).width(24),name&access_token=" + accessToken, true, function(response) {
                        response = $.parseJSON(response);
                        $(".image_" + value).attr("src", response.picture.data.url);
                    });
                });

                showModal({
                    content: $("#preview-dialog").html(),
                    title: "Post kommentieren",
                    saveLabel: "OK",
                    hideCloseButton: true,
                    preShowFunction: function() {
                        $("#modal-dialog #message").html(unescape(_response.message));
                        $("#modal-dialog #link").attr("href", unescape(_response.link));
                        $("#modal-dialog #link").html(unescape(_response.link));
                        $("#modal-dialog #picture").attr("src", unescape(_response.picture));
                        $("#modal-dialog #comments .comment").mouseover(function() {
                            $(this).find(".delete").show();
                        });
                        $("#modal-dialog #comments .comment").mouseout(function() {
                            $(this).find(".delete").hide();
                        });
                        helper.finished()
                    }
                });
            })
        });
    },
    addPost: function() {
        showModal({
            content: $("#add-dialog").html(),
            saveLabel: "hinzufügen",
            title: "Post hinzufügen",
            preShowFunction: function() {
                $("#modal-dialog #picture").bind('change', function() {
                    if ($("#modal-dialog #picture").val() != "") {
                        $("#modal-dialog #picture-preview").attr("src", $("#modal-dialog #picture").val());
                        $("#modal-dialog #picture-preview").show();
                    } else {
                        $("#modal-dialog #picture-preview").hide();
                    }

                });
                this.uploadImage();
                this.uploadYoutube();

            },
            saveFunction: function() {
                if ($("#modal-dialog #message").val() === "" && $("#modal-dialog #link").val() === "" && $("#modal-dialog #picture").val() === "") {
                    $("#modal-dialog #alert-not-filled-dialog").show();
                    return;
                }
                var pages = "";
                $.each($("#modal-dialog #multiselect option:selected"), function() {
                    pages = pages + $(this).attr("value") + ",";
                });
                pages = pages.substr(0, pages.length - 1);
                $.ajax({
                    type: "GET",
                    url: "backend/ajax_posts.php?action=addPost",
                    data: {
                        message: $("#modal-dialog #message").val(),
                        link: $("#modal-dialog #link").val(),
                        picture: $("#modal-dialog #picture-preview").attr("src"),
                        publishdate: $("#modal-dialog #publish-date").val(),
                        pages: escape(pages),
                    },
                    success: function(data) {
                        if (data == "OK") {
                            $('#modal-dialog').modal('hide');
                            helper.load("posts");
                        } else {
                            helper.handleError(data);
                        }
                    }
                });
            }
        }, function() {
            $("#modal-dialog #publish-date").datepicker({format: "yyyy-mm-dd"});
        });


    },
    delPost: function(id) {
        showModal({
            content: $("#delete-dialog").html(),
            saveLabel: "löschen",
            title: "Post löschen",
            saveFunction: function() {
                $.ajax({
                    url: "backend/ajax_posts.php?action=deletePost&id=" + id,
                    success: function(data) {
                        if (data == "OK") {
                            $('#modal-dialog').modal('hide');
                            helper.load("posts");
                        } else {
                            helper.handleError(data);
                        }
                    }
                });
            }
        });
    },
    changeStatus: function(id) {

        showModal({
            content: $("#change-status-dialog").html(),
            saveLabel: "ok",
            title: "Status ändern",
            saveFunction: function() {
                helper.loading();
                $.ajax({
                    url: "backend/ajax_posts.php?action=statusPost&id=" + id + "&status=" + $("#modal-dialog #select-status-change .active").val(),
                    success: function(data) {
                        helper.finished();
                        if (data === "OK") {
                            if ($("#modal-dialog #select-status-change .active").val() === "2") {
                                helper.loading();
                                $.ajax("backend/ajax_posts.php?action=getPostOnPages&id=" + id).done(function(response) {
                                    response = $.parseJSON(response);
                                    var pages = "Wollen sie den Post wirklich auf:</br><ul>";
                                    $.each(response.pages, function(index, value) {
                                        pages += "<li>" + value.pageName + "</li>";
                                    });
                                    pages += "</ul></br>freigeben?";
                                    helper.finished();
                                    $("#modal-content").html(pages);
                                    $("#modal-header").html("Freigeben");
                                    $("#modal-save-changes").html("Freigeben");
                                    $("#modal-save-changes").unbind();
                                    $("#modal-save-changes").bind('click', function() {
                                        $("#loading-screen").show();
                                        $.ajax({
                                            url: "backend/ajax_posts.php?action=releasePost&id=" + id,
                                            success: function(response) {
                                                if (response == "OK") {
                                                    $("#modal-dialog").modal("hide");
                                                    $("#loading-screen").hide();
                                                } else {
                                                    var warning = '<div class="alert">'
                                                            + response +
                                                            '</div>';
                                                    $("#modal-header").html("Warnung");
                                                    $("#modal-content").html(warning);
                                                    $("#modal-close").hide();
                                                    $("#modal-save-changes").unbind();
                                                    $("#modal-save-changes").bind('click', function() {
                                                        $("#modal-dialog").modal("hide");
                                                    });
                                                    $("#modal-save-changes").html("Schließen");
                                                }
                                            }
                                        });
                                    });
                                });
                            } else {
                                $("#modal-dialog").modal("hide");
                                helper.load("posts");
                            }

                        } else {
                            helper.handleError(data);
                        }
                    }
                });
            },
            preShowFunction: function() {
                $("#modal-dialog #select-status-change .active").removeClass();
                $("#modal-dialog #select-status-change").find("[value=" + $("#status_" + id).attr("data-status") + "]").addClass("active");
            }
        });
    },
    editPost: function(id) {
        helper.loading();
        $.ajax("backend/ajax_posts.php?action=getPost&id=" + id).done(function(response) {
            response = $.parseJSON(response);
            showModal({
                content: $("#add-dialog").html(),
                saveLabel: "speichern",
                title: "Post bearbeiten",
                preShowFunction: function() {
                    $("#modal-dialog #message").html(unescape(response.message));
                    $("#modal-dialog #link").val(unescape(response.link));
                    $("#modal-dialog #picture-preview").attr("src", unescape(response.picture));
                    $("#modal-dialog #picture-preview").show();
                    $("#modal-dialog #picture").val(unescape(response.picture));
                    $("#modal-dialog #publish-date").val(response.startTime);
                    $("#modal-dialog #publish-date").attr("value", response.startTime);
                    if ($("#modal-dialog #picture").val() != "") {
                        $("#modal-dialog #link").attr('disabled', '');
                    }
                    $("#modal-dialog #picture").bind('change', function() {
                        if ($("#modal-dialog #picture").val() != "") {
                            $("#modal-dialog #picture-preview").attr("src", $("#modal-dialog #picture").val());
                            $("#modal-dialog #picture-preview").show();
                        } else {
                            $("#modal-dialog #picture-preview").hide();
                        }

                    });
                    $.ajax("backend/ajax_posts.php?action=getPostOnPages&id=" + id).done(function(response) {
                        response = $.parseJSON(response);
                        $.each(response.pages, function(index, value) {
                            $('#modal-dialog #multiselect option[value=' + value.ID + ']').attr('selected', 1);
                        });
                        $('#modal-dialog #multiselect').focus();
                        $('#modal-dialog #multiselect').blur();
                         helper.finished();
                    });
                    helper.uploadImage();
                    helper.uploadYoutube();
                },
                saveFunction: function() {
                    if ($("#modal-dialog #message").val() === "" && $("#modal-dialog #link").val() === "" && $("#modal-dialog #picture").val() === "") {
                        $("#modal-dialog #alert-not-filled-dialog").show();
                        return;
                    }
                    var pages = "";
                    $.each($("#modal-dialog #multiselect option:selected"), function() {
                        pages = pages + $(this).attr("value") + ",";
                    });
                    pages = pages.substr(0, pages.length - 1);
                    $.ajax({
                        type: "GET",
                        url: "backend/ajax_posts.php?action=updatePost",
                        data: {
                            id: id,
                            message: $("#modal-dialog #message").val(),
                            link: $("#modal-dialog #link").val(),
                            picture: $("#modal-dialog #picture-preview").attr("src"),
                            publishdate: $("#modal-dialog #publish-date").val(),
                            pages: escape(pages)
                        },
                        success: function(data) {
                            if (data == "OK") {
                                $('#modal-dialog').modal('hide');
                                helper.load("posts");
                            } else {
                                helper.handleError(data);
                            }
                        }
                    })
                }
            }, function() {
                $("#modal-dialog #publish-date").datepicker({format: "yyyy-mm-dd"});
            });
        });
    },
    uploadImage: function() {
        $('#modal-dialog #fileupload').fileupload({
            url: "backend/ajax_requests.php?action=uploadImage",
            dataType: 'json',
            done: function(e, data) {
                $('#modal-dialog #picture-preview').attr("src", data.result.files[0].url);
                $("#modal-dialog #link").val("");
                $("#modal-dialog #link").attr('disabled', '');
            },
            progressall: function(e, data) {
                $('#modal-dialog #picture-preview').attr("src", "img/ajax-loader.gif");
            }
        });
    },
    uploadYoutube: function() {
        $('#modal-dialog #youtube-link').fileupload({
            url: "backend/ajax_requests.php?action=uploadVideo",
            dataType: 'json',
            done: function(e, data) {
                $('#modal-dialog #picture-preview').attr("src", "img/no-image.gif");
                $("#modal-dialog #link").attr('disabled', '');
            },
            progressall: function(e, data) {
                $('#modal-dialog #picture-preview').attr("src", "img/no-image.gif");
            }
        });
    },
    enableLink: function() {
        $("#modal-dialog #link").removeAttr('disabled');
        $('#modal-dialog #picture-preview').attr("src", "img/no-image.gif");
    }
}