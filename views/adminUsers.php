<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once('../backend/database_functions.php');
session_start();
if ($_SESSION['role'] == "0") {
    include("views/notAuthorized.html");
} else {
    $result = listUsers();
    ?>
    <div class="page-header">
        <?php
        if ($_SESSION['role'] > 0) {
            ?>
            <button class="btn" style="float:right" onclick="users.refreshUsers('<?= $_SESSION['ID'] ?>');">Meine Seiten aktualisieren</button>
        <?php }
        ?>
        <button class="btn" style="float:right" onclick="users.addUser()">User hinzufügen</button>
        <h1>Administer Users</h1>
    </div>

    <table id="userlist" class="table table-hover table-bordered" style="display:none;">
        <thead>
            <tr>
                <th>Edit</th>
                <th>Username</th>
                <th>Facebook-ID</th>
                <th>Role</th>
                <th>Pages</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysql_fetch_array($result)) {
                ?>
                <tr>
                    <td style="text-align: center;padding-top:0">
                        <div class="btn-toolbar">
                            <div class="btn-group">
                                <button class="btn" onclick="users.deleteUsers('<?= $row['id'] ?>');">Löschen</button>
                                <button class="btn" onclick="users.changeRole('<?= $row['id'] ?>');">Rolle ändern</button>
                            </div>

                        </div>

                    </td>
                    <td id="name_<?= $row['id'] ?>"><a style="color: #3b5998;" href="https://www.facebook.com/<?= $row['id'] ?>" target="_blank"><?= $row['username'] ?></a></td>
                    <td><?= $row['id'] ?></td>
                    <td data-role="<?= $row['role'] ?>" id="role_<?= $row['id'] ?>">
                        <?
                        if ($row['role'] == 0) {
                            echo "User";
                        } else if ($row['role'] == 1) {
                            echo "Community Manager";
                        } else if ($row['role'] == 2) {
                            echo "Root";
                        }
                        ?></td>
                    <td style="text-align: center;padding-top:0">
                        <div class="btn-toolbar">
                            <div class="btn-group">
                                <button class="btn" onclick="users.showPages('<?= $row['id'] ?>');">Seiten</button>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <div id="delete-dialog" style="display:none">
        <label><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
            Wollen sie den User wirklich l&ouml;schen?
        </label>
    </div>
    <div id="change-dialog" style="display:none">
        <label> Rolle von <span class="modal-username"></span> ändern:</label>
        <div id="select-role-change" class="btn-group" data-toggle="buttons-radio">
            <button type="button" class="btn btn-primary" value="1">Community Manager</button>
            <button type="button" class="btn btn-primary" value="0">User</button>
            <button type="button" class="btn btn-primary" value="2">Root</button>
        </div>      
    </div>
    <div id="add-dialog" style="display:none" >
        <label for="modal-facebook-name" > Geben Sie den Facebook-Usernamen ein:</label>
        <input type="text" id="modal-facebook-name" placeholder="z.B. hans.maier">
        <span id="modal-facebook-name-error" style="margin-left: 10px;display:none" class="text-error">du musst einen Usernamen eingeben!</span>
        <div style="display:none" id="alert-not-a-user-dialog" class="alert alert-block alert-error">
            <button type="button" class="close" data-dismiss="alert">x</button>
            Den User gibt es nicht!
        </div>
        <label>Rolle festlegen:</label>
        <div id="select-role-add" class="btn-group" data-toggle="buttons-radio">
            <button type="button" class="btn btn-primary" value="1">Community Manager</button>
            <button type="button" class="btn btn-primary" value="0">User</button>
            <button type="button" class="btn btn-primary" value="2">Root</button>
        </div>
        <span id="select-role-add-error" style="display:none" class="text-error">du musst eine Userrolle auswählen!</span>
    </div>

    <div id="show-pages" style="display:none;overflow: hidden">
        <label>Der User <span class="modal-username"></span></label>
        <label>darf auf folgenden Seiten publizieren:</label>
        <ul class="nav nav-list" id="pages-list">
        </ul>
        <p style="margin-top: 15px;">
            <button type="button" class="btn btn-primary" id="add-page-button">Seite hinzufügen</button>
        </p>
    </p>
    <div id="add-pages" style="display:none;overflow: hidden">
        <div style="display:none" id="alert-not-a-page-dialog" class="alert alert-block alert-error">
            <button type="button" class="close" data-dismiss="alert">x</button>
            Die Seite gibt es nicht!
        </div>
        <label>Dem User <span class="modal-username"></span></label>
        <label>folgende Seite zuweisen:</label>
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
    <script type="text/javascript" src="js/adminUsers.js"></script>
    <?php
}
?>
