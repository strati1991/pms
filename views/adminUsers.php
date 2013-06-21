<?php
session_start();
if ($_SESSION['role'] != 1) {
    include("views/notAuthorized.html");
} else {
    $result = listUsers();
    ?>
    <link href="../css/jquery.dataTables.css" rel="stylesheet">
    <a id="add_user" class="button" style="float:right" onclick="addUser()">User hinzufügen</a>
    <h1>Administer Users</h1>
    <table id="userlist" style="display:none;">
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
                    <td><a href="#" onclick="del('<?= $row['id'] ?>');">del</a><a href="#" onclick="changeRole('<?= $row['id'] ?>');">role</a></td>
                    <td id="name_<?= $row['id'] ?>"><a style="color: #3b5998;" href="https://www.facebook.com/<?= $row['id'] ?>" target="_blank"><?= $row['username'] ?></a></td>
                    <td><?= $row['id'] ?></td>
                    <td data-role="<?= $row['role'] ?>" id="role_<?= $row['id'] ?>">
                        <?
                        if ($row['role'] == 0) {
                            echo "User";
                        } else if ($row['role'] == 1) {
                            echo "Admin";
                        } else if ($row['role'] == 2) {
                            echo "Root";
                        }
                        ?></td>
                    <td><a onclick="showPages('<?= $row['id'] ?>');" href="#">Pages</a></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <div id="delete_dialog" style="display:none">
        <p>
            <span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>
            Wollen sie den User wirklich l&ouml;schen?
        </p>
    </div>
    <div id="change_dialog" style="display:none">
        <p>
            Rolle von <span class="username"></span> ändern:
            <select id="select_role">
                <option value="1">Admin</option>
                <option value="0">User</option>
                <option value="2">Root</option>
            </select>

        </p>
    </div>
    <div id="alert_dialog" style="display:none" title="Not authorized">
        <p>
            <span class="ui-icon ui-icon-alertk" style="float: left; margin: 0 7px 50px 0;"></span>
            Sie sind nicht authorisiert!
        </p>
    </div>
    <div id="add_dialog" style="display:none" title="Add user">
        <p>
            Geben Sie den Facebook-Usernamen ein: <input type="text" id="facebook_name"><br>
            Rolle von festlegen:
            <select id="select_role_add">
                <option value="1">Admin</option>
                <option value="0">User</option>
                <option value="2">Root</option>
            </select>
        </p>
    </div>
    <div id="show_pages" style="display:none;overflow: hidden" title="Shoq Pages">
        <p>
            Der User <span class="username"></span><br>
            Hat auf folgenden Seiten "CREATE_CONTENT" Rechte:
        <div id="pages_list" class="border" style="overflow:scroll;">
        </div>
    </p>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
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
        });
        function del(id) {
            $("#delete_dialog").dialog({
                resizable: false,
                height: 250,
                width: 350,
                modal: true,
                title: "Löschen?",
                buttons: {
                    "Löschen": function() {
                        $(this).dialog("close");
                        $.ajax({
                            url: "backend/ajax_requests.php?action=delete&id=" + id,
                            success: function(data) {
                                if (data == "OK") {
                                    window.location.href = "http://pms.social-media-hosting.com/?page=adminUsers";
                                } else {
                                    $("#alert_dialog").dialog({
                                        modal: true,
                                        buttons: {
                                            Ok: function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    },
                    "Abbrechen": function() {
                        $(this).dialog("close");
                    }
                }
            });
        }
        function changeRole(id) {
            $(".username").html($("#name_" + id).html());
            $("#select_role").find("[value=" + $("#role_" + id).attr("data-role") + "]").attr('selected', 'selected');
            $("#change_dialog").dialog({
                resizable: false,
                height: 250,
                width: 350,
                modal: true,
                title: "Userrolle ändern?",
                buttons: {
                    "Ok": function() {
                        $(this).dialog("close");
                        $.ajax({
                            url: "backend/ajax_requests.php?action=changeRole&id=" + id + "&role=" + $("#select_role").val(),
                            success: function(data) {
                                if (data == "OK") {
                                    window.location.href = "http://pms.social-media-hosting.com/?page=adminUsers";
                                } else {
                                    $("#alert_dialog").dialog({
                                        modal: true,
                                        buttons: {
                                            Ok: function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                }
                            }
                        })
                    },
                    "Abbrechen": function() {
                        $(this).dialog("close");
                    }
                }
            });
        }
        function addUser() {
            $("#add_dialog").dialog({
                resizable: false,
                height: 350,
                width: 350,
                modal: true,
                title: "User hinzufügen?",
                buttons: {
                    "Ok": function() {
                        $(this).dialog("close");
                        $.ajax({
                            url: "backend/ajax_requests.php?action=add&username=" + $("#facebook_name").val() + "&role=" + $("#select_role_add").val(),
                            success: function(data) {
                                if (data == "OK") {
                                    window.location.href = "http://pms.social-media-hosting.com/?page=adminUsers";
                                } else {
                                    $("#alert_dialog").dialog({
                                        modal: true,
                                        buttons: {
                                            Ok: function() {
                                                $(this).dialog("close");
                                            }
                                        }
                                    });
                                }
                            }
                        })
                    },
                    "Abbrechen": function() {
                        $(this).dialog("close");
                    }
                }
            });
        }
        function showPages(id) {
            $(".username").html($("#name_" + id).html());
            $.ajax({
                url: "backend/ajax_requests.php?action=showPages&id=" + id,
                dataType: "json",
                success: function(data) {
                    pages = "<ul>";
                    $.each(data.pages, function(index, value) {
                        pages = pages + '<li><a class="underline" style="color: #3b5998;" target="_blank" href="https://www.facebook.com/' + value.ID + '">' + value.pageName + '</a></li>';
                    });
                    pages = pages + "</ul>";
                    $("#pages_list").html(pages);
                    $("#show_pages").dialog({
                        resizable: false,
                        height: 350,
                        width: 400,
                        modal: true,
                        title: "Pages",
                        buttons: {
                            "Ok": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        }
    </script>
    <?php
}
?>
