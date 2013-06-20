<?php
session_start();
require_once '../backend/database.php';
if ($_SESSION['role'] != 1) {
    echo "Not authorized";
} else {
    $result = listUsers();
    ?>
    <link href="../css/jquery.dataTables.css" rel="stylesheet">
    <h1>Administer User</h1>
    <table id="userlist">
        <thead>
            <tr>
                <th>Username</th>
                <th>Facebook-ID</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysql_fetch_array($result)) {
                ?>
                <tr>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <?
                        if ($row['role'] == 0) {
                            echo "User";
                        } else if ($row['role'] == 1) {
                            echo "Admin";
                        } else if ($row['role'] == 2) {
                            echo "Root";
                        }
                        ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#userlist').dataTable();
        });
    </script>
    <?php
}
?>
