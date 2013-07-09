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
            <th>Pages</th>
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
                            <button class="btn" onclick="posts.delPost('<?= $row['postID'] ?>');">Löschen</button>
                            <button class="btn" onclick="posts.editPost('<?= $row['postID'] ?>');">Bearbeiten</button>
                            <?php
                            if ($_SESSION['ID'] > 0) {
                                ?><button class="btn" onclick="posts.view('<?= $row['postID'] ?>');">Anschauen</button><?php
                            }
                            ?>
                            <?php
                            if ($_SESSION['role'] > 0) {
                                ?><button class="btn" onclick="posts.changeStatus('<?= $row['postID'] ?>');">Status &auml;ndern</button><?php
                            }
                            ?>
                        </div>

                    </div>

                </td>
                <td><?= $row['username'] ?></td>
                <td id="message_<?= $row['postID'] ?>"><?= substr($row['message'], 0, 20) . "..." ?></td>
                <td id="date_<?= $row['postID'] ?>"><?= $row['lastChanged'] ?></td>
                <?php
                    $status = "";
                    $style = "";
                    if ($row['status'] == 0) {
                        $style = "label label-important";
                        $status = "not reviewed";
                    } else if ($row['status'] == 1) {
                        $style = "label label-warning";
                        $status = "rejected";
                    } else if ($row['status'] == 2) {
                        $style = "label label-success";
                        $status = "released";
                    }
                   
                    ?>
                <td style="height: 100%;" id="status_<?= $row['postID'] ?>" class="<?=$style?>" data-status="<?= $row['status'] ?>">
                    <?= $status?>
                    </td>
                <td id="start_<?= $row['postID'] ?>"><?= $row['startTime'] ?></td>
                <td>
                    <button class="btn" class="popover-pages" data-original-title="Seiten:" id="popover-pages-<?= $row['postID'] ?>" data-toggle="popover-pages-<?= $row['postID'] ?>" onclick="posts.popoverPagesToggle(<?= $row['postID'] ?>);">Seiten</button>
                </td>
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
    <span style="float: left; margin-right: 20px;" class="btn btn-success fileinput-button">
        <i class="icon-plus icosn-white"></i>
        <span id="upload-label">Video hochladen</span>
        <input id="youtube-link" type="file" name="file">
    </span>
    </br>
    <span style="float: left;" class="btn btn-success fileinput-button">
        <i class="icon-plus icosn-white"></i>
        <span id="upload-label">Bild hochladen</span>
        <input id="fileupload" type="file" name="file">
    </span>
    <button style="margin-left: 10px;" onclick="enableLink()" class="btn btn-warning"><i class="icon-minus icosn-white"></i><span id="upload-label">Bild entfernen</span></button>
    </br>
    </br>
    <img  src="img/no-image.gif" alt="kein Bild" id="picture-preview" style="height: 100px;float: left; margin-right: 315px;margin-bottom: 10px;"/>
    </br>
    </br>
    <label style="margin-top: 10px;">Veröffentlichungsdatum:</label>
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
    <label style="border-top: 1px solid grey;margin-top: 30px;padding-top: 10px;">
        Auf diesen Seiten soll der Post veröffentlich werden:
    </label>
    <div id="pages">

    </div>
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
<script type="text/javascript" src="js/vendor/jquery.ui.widget.js"></script>
<script type="text/javascript" src="js/vendor/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="js/vendor/jquery.fileupload.js"></script>
<script type="text/javascript" src="js/posts.js"></script>