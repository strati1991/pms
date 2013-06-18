<?php
$link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
if (!$link) {
    exit;
}
if (!mysql_select_db('usr_p158169_51', $link)) {
    exit;
}

if($_GET["command"] == "get_user_role"){
    $result = mysql_query("SELECT role FROM users WHERE id = '" . $_GET["id"] . "'",$link);
    if (!$result) {
        exit;
    }
    while ($row = mysql_fetch_assoc($result)) {
        echo $row['role'];
    }
    mysql_free_result($result);
}
?>
