<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once('../backend/database_functions.php');
require_once("../facebook-sdk/facebook.php");
require_once("../backend/config.php");
session_start();
if ($_SESSION['role'] > 0) {
    $posts = listPosts();
} else {
    $posts = listPosts($_SESSION['ID']);
}
?>
<div class="page-header">
    <button class="btn" style="float:right" onclick="addPost()">Post hinzufügen</button>
    <h1>Posts</h1>
</div>
<table id="postlist" class="table table-hover table-bordered" style="display:none;">
    <thead>
        <tr>
            <th>Edit</th>
            <th>User</th>
            <th>Message</th>
            <th>Changed</th>
            <th>Status</th>
            <th>Start</th>
            <th>End</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($row = mysql_fetch_array($posts)) {
            ?>
            <tr>
                <td style="text-align: center;padding-top:0">
                    <div class="btn-toolbar">
                        <div class="btn-group">
                            <button class="btn" onclick="delPost('<?= $row['postID'] ?>');">Löschen</button>
                            <button class="btn" onclick="editPost('<?= $row['postID'] ?>');">Bearbeiten</button>
                            <?php
                            if ($_SESSION['ID'] > 0) {
                                ?><button class="btn" onclick="view('<?= $row['postID'] ?>');">Anschauen</button><?php
                            }
                            ?>
                            <?php
                            if ($_SESSION['role'] > 0) {
                                ?><button class="btn" onclick="changeStatus('<?= $row['postID'] ?>');">Status &auml;ndern</button><?php
                            }
                            ?>
                        </div>

                    </div>

                </td>
                <td><?= $row['username'] ?></td>
                <td id="message_<?= $row['postID'] ?>"><?= substr($row['message'], 0, 20) . "..." ?></td>
                <td id="date_<?= $row['postID'] ?>"><?= $row['lastChanged'] ?></td>
                <td id="status_<?= $row['postID'] ?>" data-status="<?= $row['status'] ?>">
                    <?php
                    $status = "";
                    if ($row['status'] == 0) {
                        $status = "not reviewed";
                    } else if ($row['status'] == 1) {
                        $status = "rejected";
                    } else if ($row['status'] == 2) {
                        $status = "released";
                    }
                    echo $status;
                    ?></td>
                <td id="start_<?= $row['postID'] ?>"><?= $row['startTime'] ?></td>
                <td><?= $row['endTime'] ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<div id="add-dialog" style="display:none" >
    <div style="display:none" id="alert-not-filled-dialog" class="alert alert-block alert-error">
        <button type="button" class="close" data-dismiss="alert">x</button>
        Es muss mindestens eins der drei Felder mit Inhalt gefüllt sein: Nachricht,Link oder Bild
    </div>
    <label> Gib hier deinen Nachricht ein:</label>
    <textarea id="message" rows="5" cols="20"></textarea>
    <label>Link hinzufügen:</label>
    <input type="text" id="link" />
    <label>Bild hinzufügen:</label>
    <input type="text" id="picture" placeholder="Link zu dem Bild" />
    <img src="img/no-image.gif" alt="kein Bild" id="picture-preview" style="display:none;height: 100px;float: right;margin-right: 100px;"/>
    <label>Veröffentlichungsdatum:</label>
    <input type="text" data-format="yyyy-MM-dd" id="publish-date" />
    <label>Seiten auf denen der Post veröffentlicht werden soll:</label>
    <select id="multiselect" class="multiselect" multiple="multiple">
        <?php
        $result = query("SELECT * FROM pages WHERE userID='" . $_SESSION['ID'] . "'");
        while ($row = mysql_fetch_assoc($result)) {
            ?> <option value="<?= $row['pageID'] ?>"><?= $row['pageName'] ?></option>
            <?php
        }
        ?>
    </select>


</div>
<div id="preview-dialog" style="display:none" >
    <p id="message"></p>
    <a href="#" id="link"></a></br>
    <img src="img/no-image.gif" alt="img/no-image.gif" id="picture"/>
    <div id="comments"></div>
    <textarea id="comment" style="display:none" rows="5" cols="20"></textarea>
    <button class="btn" id="add-comment-button">Kommentar hinzufügen</button>
</div>
<div id="choose-page-dialog" style="display:none" >
    <label>Auf welchen Siten soll der Post veröffentlich werden:</label>
    <ul class="nav nav-list" id="pages-list">
    </ul>
</div>
<div id="change-status-dialog" style="display:none" >
    <label> Satus ändern:</label>
    <div id="select-status-change" class="btn-group" data-toggle="buttons-radio">
        <button type="button" class="btn btn-primary" value="0">Not reviewed</button>
        <button type="button" class="btn btn-primary" value="1">Rejected</button>
        <button type="button" class="btn btn-primary" value="2">Released</button>
    </div>   
</div>
<div id="delete-dialog" style="display:none">
    <label><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
        Wollen sie den Post wirklich l&ouml;schen?
    </label>
</div>
<script type="text/javascript">
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
        function addComment(id, username) {
            if (!$("#modal-dialog #add-comment-button").hasClass("has-comment")) {
                $("#modal-dialog #add-comment-button").addClass("has-comment");
                $("#modal-dialog #add-comment-button").html("Speichern");
                $("#modal-dialog #comment").show();
            } else {
                $("#loading-screen").show();
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
                    $("#loading-screen").hide();
                });
            }

        }
        function deleteComment(id) {
            $("#loading-screen").show();
            $.ajax("backend/ajax_posts.php?action=deleteComment&id=" + id).done(function() {
                $("#modal-dialog #comment-" + id).remove();
                $("#loading-screen").hide();
            });
        }
        function view(id) {
            $("#loading-screen").show();
            $("#add-comment-button").attr("onclick", "addComment(" + id + ",'<?= $_SESSION['username'] ?>');");
            $.ajax("backend/ajax_posts.php?action=getPost&id=" + id).done(function(response) {
                _response = $.parseJSON(response);
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
                            $("#loading-screen").hide();
                        }
                    });
                })
            });
        }
        function addPost() {
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
                            link: escape($("#modal-dialog #link").val()),
                            picture: escape($("#modal-dialog #picture").val()),
                            publishdate: $("#modal-dialog #publish-date").val(),
                            pages: escape(pages)
                        },
                        success: function(data) {
                            if (data == "OK") {
                                $('#modal-dialog').modal('hide');
                                load("posts");
                            } else {
                                handleError(data);
                            }
                        }
                    })
                }
            }, function() {
                $("#modal-dialog #publish-date").datepicker({format: "yyyy-mm-dd"});
            });


        }
        function delPost(id) {
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
                                load("posts");
                            } else {
                                handleError(data);
                            }
                        }
                    });
                }
            });
        }
        function changeStatus(id) {

            showModal({
                content: $("#change-status-dialog").html(),
                saveLabel: "ok",
                title: "Status ändern",
                saveFunction: function() {
                    $.ajax({
                        url: "backend/ajax_posts.php?action=statusPost&id=" + id + "&status=" + $("#modal-dialog #select-status-change .active").val(),
                        success: function(data) {
                            if (data == "OK") {
                                $('#modal-dialog').modal('hide');
                                load("posts");
                            } else {
                                handleError(data);
                            }
                        }
                    });
                },
                preShowFunction: function() {
                    $("#modal-dialog #select-status-change .active").removeClass();
                    $("#modal-dialog #select-status-change").find("[value=" + $("#status_" + id).attr("data-status") + "]").addClass("active");
                }
            });
        }
        function editPost(id) {
            $("#loading-screen").show();
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
                            $("#loading-screen").hide();
                        });
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
                                message: escape($("#modal-dialog #message").val()),
                                link: escape($("#modal-dialog #link").val()),
                                picture: escape($("#modal-dialog #picture").val()),
                                publishdate: $("#modal-dialog #publish-date").val(),
                                pages: escape(pages)
                            },
                            success: function(data) {
                                if (data == "OK") {
                                    $('#modal-dialog').modal('hide');
                                    load("posts");
                                } else {
                                    handleError(data);
                                }
                            }
                        })
                    }
                }, function() {
                    $("#modal-dialog #publish-date").datepicker({format: "yyyy-mm-dd"});
                });
            });
        }

</script>