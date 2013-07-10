<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
date_default_timezone_set('UTC');
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
    <button class="btn" style="float:right" onclick="posts.addPost()">Post hinzufügen</button>
    <h1>Posts</h1>
</div>
<table id="postlist" class="table table-hover table-bordered" style="display:none;">
    <thead>
        <tr>
            <th>Edit</th>
            <th>User</th>
            <th class="hidable">Message</th>
            <th class="hidable">Changed</th>
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
                            <button title="L&ouml;schen" class="btn has-tooltip-bottom" onclick="posts.delPost('<?= $row['postID'] ?>');"><i class="icon-minus icosn-white"></i></button>
                            <button title="Bearbeiten" class="btn has-tooltip-bottom" onclick="posts.editPost('<?= $row['postID'] ?>');"><i class="icon-pencil icosn-white"></i></button>
                            <?php
                            if ($_SESSION['ID'] > 0) {
                                ?><button title="Ansehen und Kommentieren" class="btn has-tooltip-bottom" onclick="posts.view('<?= $row['postID'] ?>');"><i class="icon-eye-open icosn-white"></i></button><?php
                                }
                                ?>
                                <?php
                                if ($_SESSION['role'] > 0) {
                                    ?><button title="Status ändern oder freigeben" class="btn has-tooltip-bottom" onclick="posts.changeStatus('<?= $row['postID'] ?>');"><i class="icon-repeat icosn-white"></i></button><?php
                                }
                                ?>
                        </div>

                    </div>

                </td>
                <td><?= $row['username'] ?></td>
                <td class="hidable" id="message_<?= $row['postID'] ?>"><?= substr($row['message'], 0, 20) . "..." ?></td>
                <td class="hidable" id="date_<?= $row['postID'] ?>"><?= $row['lastChanged'] ?></td>
                <?php
                $style = "";
                if ($row['status'] == 0) {
                    $style = "label label-important";
                } else if ($row['status'] == 1) {
                    $style = "label label-warning";
                } else if ($row['status'] == 2) {
                    $style = "label label-success";
                }
                ?>
                <td style="height: 100%;padding-top: 15px;width: 100%;" class="<?= $style ?>"  id="status_<?= $row['postID'] ?>" data-status="<?= $row['status'] ?>">
                    <span><?= $status[$row['status']] ?></span>
                </td>
                <td id="start_<?= $row['postID'] ?>"><?= $row['startTime'] ?></td>
                <td>
                    <button title="Seiten anzeigen auf denen der Post erscheinen wird" class="btn has-tooltip-bottom" class="popover-pages" data-original-title="Seiten:" id="popover-pages-<?= $row['postID'] ?>" data-toggle="popover-pages-<?= $row['postID'] ?>" onclick="posts.popoverPagesToggle(<?= $row['postID'] ?>);"><i class="icon-list icosn-white"></i></button>
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
        Es muss mindestens eins der drei Felder mit Inhalt gefüllt sein: Nachricht,Link oder Bild!
    </div>
    <div style="display:none" id="alert-date-not-correct-dialog" class="alert alert-block alert-error">
        <button type="button" class="close" data-dismiss="alert">x</button>
        Das Datum muss in der Zukunft liegen!
    </div>
    <form class="form-horizontal">
        <fieldset>
            <div class="control-group">
                <label class="control-label" for="message">Deine Nachricht:</label>
                <div class="controls">
                    <textarea id="message" rows="5" cols="20" placeholder="dein Post..."></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="link">Link hinzufügen:</label>
                <div class="controls">
                    <input type="text" id="link"  placeholder="z.B. http://beispiel.de"/>
                </div>
            </div>
            <!--<div class="control-group">
                <label class="control-label" for="youtube-link">Video hinzufügen:</label>
                <div class="controls">
                    <span style="float: left; margin-right: 20px;" class="btn btn-success fileinput-button">
                        <i class="icon-upload icosn-white"></i>
                        <span id="upload-label">hochladen</span>
                        <input id="youtube-link" type="file" name="file">
                    </span>
                </div>
            </div>-->
            <div class="control-group">
                <label class="control-label" for="fileupload">Bild hinzufügen:</label>
                <div class="controls">
                    <span style="float: left;" class="btn btn-success fileinput-button">
                        <i class="icon-upload icosn-white"></i>
                        <span id="upload-label">hochladen</span>
                        <input id="fileupload" type="file" name="file">
                    </span>
                    <button style="margin-left: 10px;" onclick="posts.enableLink()" class="btn btn-warning"><i class="icon-trash icosn-white"></i><span style="margin-left: 5px;">Bild entfernen</span></button>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="picture-preview">Vorschau:</label>
                <div class="controls">
                    <img  class="img-polaroid" alt="kein Bild" id="picture-preview" style="width: 250px;margin-bottom: 10px;"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="publish-date">Veröffentlichungsdatum:</label>
                <div class="controls">
                    <div class="input-append date" id="select-date">
                        <input data-format="yyyy-dd-MM hh:mm:ss" type="text" id="publish-date" readonly/>
                        <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></span>
                    </div>

                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="multiselect">Seiten auf denen der Post veröffentlicht werden soll:</label>
                <div class="controls">
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
            </div>

        </fieldset>
    </form>
</div>
<div id="preview-dialog" style="display:none" >
    <form class="form-horizontal">
        <fieldset>
            <div class="control-group">
                <label class="control-label" for="message">Nachricht:</label>
                <div class="controls" style="margin-top: 5px;">
                    <p id="message"></p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="link">Link:</label>
                <div class="controls" style="margin-top: 5px;">
                    <a href="#" id="link"></a>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="picture">Bild:</label>
                <div class="controls" style="margin-top: 5px;">
                    <img class="img-polaroid" alt="kein Bild" id="picture"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="pages">Auf diesen Seiten soll der Post veröffentlich werden:</label>
                <div class="controls" style="margin-top: 5px;">
                    <div id="pages" class="well well-small">

                    </div>
                </div>
            </div>

            <h4>Kommentare:</h4>
            <hr>
            <div id="comments" class="well well-small">
            </div>
            <textarea id="comment" style="display:none" rows="5" cols="20"></textarea>
            <button class="btn" id="add-comment-button" title="Kommentar hinzufügen" class="has-tooltip-left"><i class="icon-comment icosn-white"></i></button>
        </fieldset>
    </form>   
</div>
<div id="choose-page-dialog" style="display:none" >
        <form class="form-horizontal">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="pages-list">Auf welchen Siten soll der Post veröffentlich werden:</label>
                    <div class="controls" style="margin-top: 5px;">
                        <ul class="nav nav-list" id="pages-list">
                       </ul>
                    </div>
                </div>
            </fieldset>
        </form>
</div>
<div id="change-status-dialog" style="display:none" >
    <div id="select-status-change" class="btn-group" data-toggle="buttons-radio">
        <button type="button" class="btn btn-primary" value="0"><?= $status[0] ?></button>
        <button type="button" class="btn btn-primary" value="1"><?= $status[1] ?></button>
        <button type="button" class="btn btn-primary" value="2"><?= $status[2] ?></button>
    </div>  
</div>
</div>

<div id="release-dialog" style="display:none">
    <div class="alert alert-warning">
        <form>
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="release-pages">Wollen sie den Post wirklich auf folgenden Seiten freigeben:</label>
                    <div class="controls" style="margin-top: 5px;">
                        <ul id="release-pages"></ul>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div><div id="delete-dialog" style="display:none">
    <div class="alert alert-warning">
            <strong>Wollen sie den Post wirklich l&ouml;schen?</strong>
    </div>
</div>
<?php
require_once("../js/posts.php");
?>