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
<div class="page-header view-content">
    <button class="btn has-tooltip-bottom" title="Post hinzufügen" style="float:right" onclick="posts.addPost()"><i class="icon-plus icosn-white"></i></button>
    <h1>Posts</h1>
</div>
<table id="postlist" class="table table-hover table-bordered view-content" style="display:none;">
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
                <td><a target="_blank" href="https://facebook.com/<?= $row['username'] ?>"><?= $row['username'] ?></a></td>
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
                <td class="<?= $style ?>"  id="status_<?= $row['postID'] ?>" data-status="<?= $row['status'] ?>">
                    <span><?= $status[$row['status']] ?>
                    <?php
                        if($row['status'] == 2 && $row['facebookPostID'] != 0){
                            ?>
                        <a href="https://facebook.com/<?= $row['facebookPostID'] ?>" target="_blank" ><button title="Zu dem Post auf Facebook springen" class="btn has-tooltip-bottom" ><i class="icon-share-alt icosn-white"></i></button></a>
                        <?php
                        }
                    ?>
                    </span>
                </td>
                <td id="start_<?= $row['postID'] ?>"><?= $row['startTime'] ?></td>
                <td>
                    <button title="Seiten anzeigen auf denen der Post erscheinen wird" class="btn has-tooltip-bottom popover-pages" data-original-title="Seiten:" id="popover-pages-<?= $row['postID'] ?>" data-toggle="popover-pages-<?= $row['postID'] ?>" onclick="posts.popoverPagesToggle(<?= $row['postID'] ?>);"><i class="icon-list icosn-white"></i></button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<div id="add-dialog" style="display:none" >

    <div style="display:none" id="alert-not-filled-dialog" class="alert alert-block alert-error">
        <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
        Es muss mindestens eins der drei Felder mit Inhalt gefüllt sein: Nachricht,Link oder Bild!
    </div>
    <div class="form-horizontal">
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
            <div class="control-group">
                <label class="control-label" for="fileupload">Video hinzufügen:</label>
                <div class="controls">
                    <span style="float: left;" id="youtubeupload-button" class="btn btn-success fileinput-button">
                        <i class="icon-upload icosn-white"></i>
                        <span >hochladen</span>
                    </span>
                    <form id="youtubeupload-form" style="display:none" action="/backend/ajax_requests.php?action=uploadVideo" method="post"> 
                        <input id="youtubeupload-file" type="file" name="file"/>
                    </form>
                    <button style="margin-left: 10px;" onclick="posts.enableLink('video')" class="btn btn-warning delete"><i class="icon-trash icosn-white"></i><span style="margin-left: 5px;">Video entfernen</span></button>
                </div>
            </div>
            <div class="control-group" id="video-url-container" style="display:none">
                <label class="control-label" for="link">Video:</label>
                <div class="controls" style="padding-top: 5px;">
                    <iframe class="youtube-player" id="video-url" type="text/html" width="320" height="240" src="" allowfullscreen frameborder="0">
                    </iframe>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button id="video-edit" onclick="posts.editVideo()" class="btn btn-success hide"><i class="icon-edit icosn-white"></i><span style="margin-left: 5px;">Video bearbeiten</span></button>
                    <div id="video-not-yet-shown" style="margin-top: 10px;margin-bottom: 0px;;display:none;" class="alert alert-warning">
                        Das Video wird erst beim späteren editieren des Posts angezeigt. 
                    </div>
                    <div id="error-video-not-on-youtube" style="margin-top: 10px;margin-bottom: 0px;display:none;" class="alert alert-error" >
                        Das Video scheint es anscheinend nicht auf Youtube zu geben. Klicken Sie bitte uf den Play-Button der Video-Vorschau um zu sehen was nicht geklappt hat.
                    </div>
                </div>
            </div>
            <div style="display:none" id="alert-video-wrong-type-dialog" class="alert alert-block alert-error">
                <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                Dein Video muss entweder ein mpeg4,mov,avi oder ein wmv sein!
            </div>
            <div class="control-group">
                <label class="control-label" for="fileupload">Bild hinzufügen:</label>
                <div class="controls">
                    <span style="float: left;" id="imageupload-button" class="btn btn-success fileinput-button">
                        <i class="icon-upload icosn-white"></i>
                        <span >hochladen</span>
                    </span>
                    <form id="imageupload-form" style="display:none" action="/backend/ajax_requests.php?action=uploadImage" method="post"> 
                        <input id="imageupload-file" type="file" name="file"/>
                    </form>
                    <button style="margin-left: 10px;" onclick="posts.enableLink('image')" class="btn btn-warning delete"><i class="icon-trash icosn-white"></i><span style="margin-left: 5px;">Bild entfernen</span></button>

                </div>
            </div>
            <div style="display:none" id="alert-image-to-large-dialog" class="alert alert-block alert-error">
                <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                Dein Bild ist zu groß es darf höchstens 100kb groß sein!
            </div>
            <div style="display:none" id="alert-image-wrong-type-dialog" class="alert alert-block alert-error">
                <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                Dein Bild muss entweder ein PNG oder ein JPEG sein!
            </div>
            <div class="control-group">
                <label class="control-label" for="picture-preview">Vorschau:</label>
                <div class="controls">
                    <img src="/img/no_image.jpg" class="img-polaroid" alt="kein Bild" id="picture-preview" style="width: 250px;margin-bottom: 10px;"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="publish-date">Veröffentlichungsdatum:</label>
                <div class="controls">
                    <div class="input-append date" id="select-date">
                        <input data-format="yyyy-MM-dd hh:mm:ss" type="text" id="publish-date" readonly/>
                        <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></span>
                    </div>

                </div>
            </div>
            <div style="display:none" id="alert-date-not-correct-dialog" class="alert alert-block alert-error">
                <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                Das Datum muss in der Zukunft liegen!
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
    </div>
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
                    <img style="width: 200px;" class="img-polaroid" alt="kein Bild" id="picture"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="picture">Video:</label>
                <div class="controls" style="margin-top: 5px;">
                    <iframe class="youtube-player" id="video" type="text/html" width="320" height="240" src="" allowfullscreen frameborder="0">
                    </iframe>
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
            <div style="display:none" id="alert-comment-empty-dialog" class="alert alert-block alert-error">
                <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                Du hast keinen Text eingegeben!
            </div>
            <textarea id="comment" style="display:none" rows="5" cols="20"></textarea>
            <button class="btn has-tooltip-left" id="add-comment-button" title="Kommentar hinzufügen" ><i class="icon-comment icosn-white"></i></button>
            <button class="btn has-tooltip-left" id="cancel-comment-button" onclick="posts.cancelComment();" style="display:none" title="Abbrechen" ><i class="icon-remove icosn-white"></i></button>
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
<div id="video-dialog" class="modal fade hide" style="z-index: 100000;">
    <div class="modal-header">
        <h3>Video bearbeiten</h3>
    </div>
    <div class="modal-body">
        <div class="form-horizontal">
            <fieldset>
                <div style="display:none" id="no-video-title-dialog" class="alert alert-block alert-error">
                    <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                    Du musst einen Titel eingeben!
                </div>
                <div class="control-group">
                    <label class="control-label" for="video-title">Video Titel:</label>
                    <div class="controls">
                        <input type="text" id="video-title" />
                    </div>
                </div>
                <div style="display:none" id="no-video-category-dialog" class="alert alert-block alert-error">
                    <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                    Du musst eine Kategory angeben!
                </div>
                <div class="control-group">
                    <label class="control-label" for="video-category">Video Kategorie:</label>
                    <div class="controls">
                        <div class="bfh-selectbox">
                            <input type="hidden" name="video-category" id="video-category" value="">
                            <a class="bfh-selectbox-toggle" role="button" data-toggle="bfh-selectbox" href="#">
                                <span class="bfh-selectbox-option input-medium" data-option></span>
                                <b class="caret"></b>
                            </a>
                            <div class="bfh-selectbox-options">
                                <div role="listbox">
                                    <ul role="option" id="video-categories">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:none" id="no-video-tags-dialog" class="alert alert-block alert-error">
                    <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
                    Du musst mindestens einen Tag angeben!
                </div>
                <div class="control-group">
                    <label class="control-label" for="video-tags">Video Tags:</label>
                    <div class="controls">
                        <input type="text" name="video-tags" id="video-tags"  placeholder="Tags" placeholder='drücke "Escape" um einen neuen Tag zu beginnen' class="tm-input"/>
                    </div>
                </div>
        </div>
        <div class="modal-footer">
            <a href="#" id="video-save-changes" class="btn btn-primary">Speichern</a>
        </div>
    </div>
</div>
<?php
require_once("../js/posts.php");
?>