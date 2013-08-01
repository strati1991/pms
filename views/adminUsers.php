<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once('../backend/database_functions.php');
require_once('../backend/config.php');
session_start();
if ($_SESSION['role'] == "0") {
    include("views/notAuthorized.html");
} else {
    $result = listUsers();
    ?>
    <div class="page-header view-content">
        <?php
        if ($_SESSION['role'] > 0) {
            ?>
            <button title="Aktualisiere die Seiten auf denen du Content-erstell-Rechte hast" class="btn has-tooltip-bottom" style="float:right" onclick="users.refreshUsers('<?= $_SESSION['ID'] ?>');"><i class="icon-refresh icosn-white"></i></button>
        <?php }
        ?>
        <button class="btn has-tooltip-bottom" style="float:right"  title="User hinzufügen" onclick="users.addUser()"><i class="icon-plus icosn-white"></i></button>
        <h1>Administer Users</h1>
    </div>
    <table id="userlist" class="table table-hover table-bordered view-content" style="display:none;">
        <thead>
            <tr>
                <?php if ($_SESSION['role'] > 1) {
                    ?>
                    <th>Edit</th>
                <?php } ?>
                <th>Username</th>
                <th>Facebook-ID</th>
                <th>Role</th>
                <th>Pages</th>
                <th>Customer</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysql_fetch_array($result)) {
                ?>
                <tr>
                    <?php if ($_SESSION['role'] > 1) {
                        ?>
                        <td style="text-align: center;padding-top:0">
                            <div class="btn-toolbar">
                                <div class="btn-group">
                                    <button title="Löschen" class="btn has-tooltip-bottom" onclick="users.deleteUser('<?= $row['id'] ?>');"><i class="icon-minus icosn-white"></i></button>
                                    <button title="Rolle ändern" class="btn has-tooltip-bottom" onclick="users.changeRole('<?= $row['id'] ?>');"><i class="icon-user icosn-white"></i></button>
                                </div>
                            </div>
                        </td>
                    <?php }
                    ?>
                    <td id="name_<?= $row['id'] ?>"><a class="has-tooltip-bottom" title="Link zur Facebook-Seite des Users" style="color: #3b5998;" href="https://www.facebook.com/<?= $row['id'] ?>" target="_blank"><?= $row['username'] ?></a></td>
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
                                <button title="Der User darf auf folgenden</br>Seiten posten und/oder freigeben" class="btn has-tooltip-left" onclick="users.showPages('<?= $row['id'] ?>',<?= $row['role'] ?>);"><i class="icon-list icosn-white"></i></button>
                            </div>
                        </div>
                    </td>

                    <td style="text-align: center;padding-top: 0;">
                        <?php if ($row['role'] == 0) {
                            ?>
                            <div class="btn-toolbar" style="width: 60px;float: left;">
                                <div class="btn-group">
                                    <button title="Dem User ein Kunden zuweisen" onclick="users.assingnCustomer('<?= $row['id'] ?>')" class="btn has-tooltip-left"><i class="icon-edit icosn-white"></i></button>
                                </div>                               
                            </div>
                             <span style="float: left;margin-top: 16px;"><?= $row['cname'] == '' ? 'Nicht zugewiesen' : $row['cname'] ?></span>
                            <?php
                        }
                        ?>
                    </td>

                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <div id="delete-dialog" style="display:none">
        <div class="alert alert-warning">
            <strong>Wollen sie den User wirklich l&ouml;schen?</strong>
        </div>
    </div>
    <div id="change-dialog" style="display:none">
        <label> Rolle von <span class="modal-username"></span> ändern:</label>
        <div id="select-role-change" class="btn-group" data-toggle="buttons-radio">
            <button title="<?= $role_description[1] ?>" type="button" class="btn btn-primary has-tooltip-right" value="1">Community Manager</button>
            <button title="<?= $role_description[0] ?>" type="button" class="btn btn-primary has-tooltip-right" value="0">User</button>
            <button title="<?= $role_description[2] ?>" type="button" class="btn btn-primary has-tooltip-right" value="2">Root</button>
        </div>      
    </div>
    <div id="add-dialog" style="display:none" >
        <div style="display:none" id="alert-not-a-user-dialog" class="alert alert-block alert-error">
            <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
            Den User gibt es nicht!
        </div>
        <div style="display:none" id="modal-facebook-name-error" class="alert alert-block alert-error">
            <button type="button" class="close" onclick="$(this).parent().hide()">x</button>
            <span  class="text-error">du musst einen Usernamen eingeben!</span>
        </div>
        <form class="form-horizontal">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="modal-facebook-name">Geben Sie den Facebook-Usernamen ein:</label>
                    <div class="controls" style="margin-top: 5px;">
                        <input title="Der Username ist das was am Ende</br>der URL angezeigt wird,</br>wenn man auf das Facebook-Profil</br>des jeweiligen Users geht" type="text" id="modal-facebook-name" class="has-tooltip-bottom" placeholder="z.B. hans.maier">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="modal-facebook-name">Rolle festlegen:</label>
                    <div class="controls" style="margin-top: 5px;">
                        <div id="select-role-add" class="btn-group" data-toggle="buttons-radio">
                            <button title="<?= $role_description[1] ?>" type="button" class="btn btn-primary active has-tooltip-top" value="1">Community Manager</button>
                            <button title="<?= $role_description[0] ?>" type="button" class="btn btn-primary has-tooltip-top" value="0">User</button>
                            <button title="<?= $role_description[2] ?>" type="button" class="btn btn-primary has-tooltip-top" value="2">Root</button>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>



    </div>

    <div id="show-pages" style="display:none;overflow: hidden">
        <form>
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="pages-list">Der User <span class="modal-username"></span></br>darf auf folgenden Seiten <strong id="modal-role"></strong>:</label>
                    <div class="controls" style="margin-top: 5px;">
                        <ul class="nav nav-list" id="pages-list">
                        </ul>
                        <?php if ($_SESSION['role'] > 1) {
                            ?>
                            <p style="margin-top: 15px;">
                                <button type="button" style="display:none;" class="btn btn-primary" id="add-page-button">Seite hinzufügen</button>
                            </p>
                        <?php }
                        ?>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
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
