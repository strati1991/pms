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
            <th>Message</th>
            <th>Date</th>
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
                            <button class="btn" onclick="del('<?= $row['postID'] ?>');">Löschen</button>
                            <button class="btn" onclick="editPost('<?= $row['postID'] ?>');">Bearbeiten</button>
                            <?php
                            if ($_SESSION['ID'] > 0) {
                                ?><button class="btn" onclick="view('<?= $row['postID'] ?>');">Anschauen</button><?php
                            }
                            ?>
                        </div>

                    </div>

                </td>
                <td id="message_<?= $row['postID'] ?>"><?= substr($row['message'], 0, 20) . "..." ?></td>
                <td id="date_<?= $row['postID'] ?>"><?= $row['date'] ?></td>
                <td id="status_<?= $row['postID'] ?>" data-="<?= $row['status'] ?>">
                    <?
                    if ($row['status'] == 0) {
                        echo "not reviewed";
                    } else if ($row['status'] == 1) {
                        echo "rejected";
                    } else if ($row['status'] == 2) {
                        echo "released";
                    }
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
    <img src="img/no-image.gif" alt="img/no-image.gif" id="picture-preview" style="display:none;height: 100px;float: right;margin-right: 100px;"/>
    <label>Veröffentlichungsdatum:</label>
    <input type="text" data-format="yyyy-MM-dd" id="publish-date" />

</div>
<div id="preview-dialog" style="display:none" >
    <p id="message"></p>
    <a href="#" id="link"></a>
    <img src="img/no-image.gif" alt="img/no-image.gif" id="picture"/>
    <div id="comments"></div>
    <button class="btn" id="add-comment-button">Kommentar hinzufügen</button>
</div>
<div id="choose-page-dialog" style="display:none" >
    <label>Auf welchen Siten soll der Post veröffentlich werden:</label>
    <ul class="nav nav-list" id="pages-list">
    </ul>
</div>
<script type="text/javascript">
        view.init = function() {
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
        function addComment(id) {
            if(!$("#modal-dialog #add-comment-button").hasClass("has-comment")){
                $("#modal-dialog #add-comment-button").addClass("has-comment");
                $("#modal-dialog #add-comment-button").html("Speichern");
                $("#modal-dialog #comments").append('<textare type="text" id="comment" /></textarea>');
            } else {
                $("#modal-dialog #add-comment-button").removeClass("has-comment");
                $("#modal-dialog #add-comment-button").html("Kommentar hinzufügen");
                $("#modal-dialog #comment").remove();
                $.ajax("backend/ajax_requests.php?action=addComment&postID=" + id + "&comment=" + escape($("#modal-dialog #comment").val())).done(function(response) {
                });
            }
            
        }
        function view(id) {
            $("#add-comment-button").attr("onclick","addComment(" + id + ");");
            $.ajax("backend/ajax_requests.php?action=getPost&id=" + id).done(function(response) {
                _response = $.parseJSON(response);
                console.log(_response);
                $.ajax("backend/ajax_requests.php?action=getComments&id=" + id).done(function(response) {
                     console.log(response);
                    response = $.parseJSON(response);
                   
                    showModal({
                        content: $("#preview-dialog").html(),
                        title: "Post kommentieren",
                        preShowFunction: function() {
                            $("#modal-dialog #message").html(unescape(_response.message));
                            $("#modal-dialog #link").attr("href", unescape(_response.link));
                            $("#modal-dialog #link").html(unescape(_response.link));
                            $("#modal-dialog #picture").attr("src", unescape(_response.picture));
                        },
                        saveFunction: function() {

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
                    $.ajax({
                        type: "GET",
                        url: "backend/ajax_requests.php?action=addPost",
                        data: {
                            message: $("#modal-dialog #message").val(),
                            link: escape($("#modal-dialog #link").val()),
                            picture: escape($("#modal-dialog #picture").val()),
                            publishdate: $("#modal-dialog #publish-date").val()
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
        function editPost(id) {
            $.ajax("backend/ajax_requests.php?action=getPost&id=" + id).done(function(response) {
                response = $.parseJSON(response);
                console.log(response);

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
                    },
                    saveFunction: function() {
                        if ($("#modal-dialog #message").val() === "" && $("#modal-dialog #link").val() === "" && $("#modal-dialog #picture").val() === "") {
                            $("#modal-dialog #alert-not-filled-dialog").show();
                            return;
                        }
                        $.ajax({
                            type: "GET",
                            url: "backend/ajax_requests.php?action=updatePost",
                            data: {
                                id: id,
                                message: escape($("#modal-dialog #message").val()),
                                link: escape($("#modal-dialog #link").val()),
                                picture: escape($("#modal-dialog #picture").val()),
                                publishdate: $("#modal-dialog #publish-date").val()
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