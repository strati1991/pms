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
        <button class="btn" style="float:right" onclick="addUser()">User hinzufügen</button>
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
                                <button class="btn" onclick="del('<?= $row['id'] ?>');">Löschen</button>
                                <button class="btn" onclick="changeRole('<?= $row['id'] ?>');">Rolle ändern</button>
                                <?php
                                if ($row['role'] > 0 && $row['id'] == $_SESSION['ID']) {
                                    ?>
                                    <button class="btn" onclick="refresh('<?= $row['id'] ?>');">Seiten aktualisieren</button>
                                <?php }
                                ?>

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
                                <button class="btn" onclick="showPages('<?= $row['id'] ?>');">Seiten</button>
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
    </p>
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
        <input type="text" id="new-page" />
        <label id="new-page-error" style="display:none" class="text-error">Du hast vergessen einen Seitennamen anzugeben</label>
    </div>
    <script type="text/javascript">
            view.init = function() {
                $('#userlist').dataTable({
                    "bPaginate": true,
                    "bLengthChange": true,
                    "bFilter": true,
                    "bSort": true,
                    "bInfo": true,
                    "bAutoWidth": true,
                    "fnInitComplete": function() {
                        $('#userlist').fadeIn();
                    }
                });
            };
            function addPage(id) {
                $("#new-page-error").hide();
                showModal({
                    content: $("#add-pages").html(),
                    saveLabel: "hinzufügen",
                    title: "Seite hinzufügen",
                    saveFunction: function() {
                        if ($("#modal-dialog #new-page").val() == "") {
                            $("#modal-dialog #new-page-error").show();
                            return;
                        }
                        $("#loading-screen").fadeIn();
                        $.ajax("backend/ajax_requests.php?action=addPage&id=" + id + "&page=" + $("#modal-dialog #new-page").val()).done(function(response) {
                            handleError(response, function() {
                                $("#loading-screen").fadeOut();
                                $('#modal-dialog').modal('hide');
                            });
                        });
                    }
                });
            }

            function del(id) {
                showModal({
                    content: $("#delete-dialog").html(),
                    saveLabel: "löschen",
                    title: "User löschen",
                    saveFunction: function() {
                        $.ajax({
                            url: "backend/ajax_requests.php?action=delete&id=" + id,
                            success: function(data) {
                                if (data == "OK") {
                                    $('#modal-dialog').modal('hide');
                                    load("adminUsers");
                                } else {
                                    handleError(data);
                                }
                            }
                        });
                    }
                });
            }
            function changeRole(id) {
                $("#select-role-change .active").removeClass();
                $(".modal-username").html($("#name_" + id).html());
                $("#select-role-change").find("[value=" + $("#role_" + id).attr("data-role") + "]").addClass("active");
                showModal({
                    content: $("#change-dialog").html(),
                    saveLabel: "Ja",
                    title: "Userrolle ändern",
                    saveFunction: function() {
                        $.ajax({
                            url: "backend/ajax_requests.php?action=changeRole&id=" + id + "&role=" + $("#modal-dialog #select-role-change .active").val(),
                            success: function(data) {
                                if (data == "OK") {
                                    $('#modal-dialog').modal('hide');
                                    load("adminUsers");
                                } else {
                                    handleError(data);
                                }
                            }
                        });
                    }
                });
            }
            function addUser() {
                $("#modal-dialog #modal-facebook-name-error").hide();
                $("#modal-dialog #select-role-add-error").hide();
                showModal({
                    content: $("#add-dialog").html(),
                    saveLabel: "hinzufügen",
                    title: "User hinzufügen",
                    saveFunction: function() {
                        if ($("#modal-dialog #modal-facebook-name").val() === "") {
                            $("#modal-dialog #modal-facebook-name-error").show();
                            return;
                        }
                        if (!$("#modal-dialog #select-role-add .active").length) {
                            $("#modal-dialog #select-role-add-error").show();
                            return;
                        }
                        $.ajax({
                            url: "backend/ajax_requests.php?action=add&username=" + $("#modal-dialog #modal-facebook-name").val() + "&role=" + $("#modal-dialog #select-role-add .active").val(),
                            success: function(data) {
                                if (data == "OK") {
                                    $('#modal-dialog').modal('hide');
                                    load("adminUsers");
                                } else {
                                    handleError(data);
                                }
                            }
                        })
                    }
                });

            }
            function showPages(id) {
                $("#add-page-button").attr("onclick", "addPage(" + id + ");");
                $(".modal-username").html($("#name_" + id).html());
                $.ajax({
                    url: "backend/ajax_requests.php?action=showPages&id=" + id,
                    dataType: "json",
                    success: function(data) {
                        var pages = "";
                        if (data.pages !== undefined) {
                            $.each(data.pages, function(index, value) {
                                pages = pages + '<li><a target="_blank" href="https://www.facebook.com/' + value.pageID + '">' + value.pageName + '</a></li>';
                            });
                        }
                        $("#pages-list").html(pages);
                        showModal({
                            content: $("#show-pages").html(),
                            title: "Seiten",
                            saveLabel:"Schließen",
                            hideCloseButton:true
                        });
                    }
                });



            }
            function refresh(id) {
                $("#loading-screen").fadeIn();
                $.ajax("backend/ajax_requests.php?action=refresh&id=" + id).done(function() {
                    $("#loading-screen").hide();
                    showPages(id);
                });
            }
            function refreshPage() {
                load("adminUsers");
            }
    </script>
    <?php
}
?>
