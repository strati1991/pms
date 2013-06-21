<?php
require_once("../facebook-sdk/facebook.php");
$config = array();
$config['appId'] = '192351964261671';
$config['secret'] = '2c0ce846356ab46e072b68aae2bcc3db';
$facebook = new Facebook($config);
session_start();
if ($_GET["action"] == "changeRole") {
    if ($_SESSION['role'] == 1) {
        $id = $_GET["id"];
        $role = $_GET["role"];
        $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
        if (!$link) {
            exit;
        }
        if (!mysql_select_db('usr_p158169_51', $link)) {
            exit;
        }
        mysql_query("UPDATE users SET role='" . $role . "' WHERE id = '" . $id . "'", $link);
        echo "OK";
    } else {
        exit;
    }
}
if ($_GET["action"] == "delete") {
    if ($_SESSION['role'] == 1) {
        $id = $_GET["id"];
        $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
        if (!$link) {
            exit;
        }
        if (!mysql_select_db('usr_p158169_51', $link)) {
            exit;
        }
        mysql_query("DELETE FROM users WHERE id = '" . $id . "'", $link);
        echo "OK";
    } else {
        exit;
    }
}
if ($_GET["action"] == "add") {
    if ($_SESSION['role'] == 1) {
        $username = $_GET["username"];
        $role = $_GET["role"];
        $id = $facebook->api('/' . $username . '?fields=id', 'GET');
        $sites = $facebook->api('/' . $id['id'] . '/accounts', 'GET');
        $sites = $sites['data'];
        $insert_pages = "INSERT INTO pages (userID,ID,pageNAME) VALUES ";
        foreach ($sites as $value) {
            $perms = $value['perms'];
            foreach ($perms as $perm) {
                if($perm == "CREATE_CONTENT") {
                    $insert_pages = $insert_pages . "('" . $id['id']  . "','" . $value['id'] . "','" . $value['name'] . "'),";
                    break;
                }
            }
        }
        $insert_pages = substr($insert_pages, 0, -1);
        $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
        if (!$link) {
            exit;
        }
        if (!mysql_select_db('usr_p158169_51', $link)) {
            exit;
        }
        mysql_query("INSERT INTO users VALUES ('" . $id['id'] . "','" . $role . "','" . $username . "')", $link);
        mysql_query($insert_pages, $link);
        echo "OK";
    } else {
        exit;
    }
}
if ($_GET["action"] == "showPages") {
    if ($_SESSION['role'] == 1) {
        $id = $_GET["id"];
        $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
        if (!$link) {
            exit;
        }
        if (!mysql_select_db('usr_p158169_51', $link)) {
            exit;
        }
        $result = mysql_query("SELECT * FROM pages WHERE userID='" . $id . "'", $link);
        $response = '{ "pages" : [';
        while ($row = mysql_fetch_assoc($result)) {
        $response = $response . '{ "pageName" : "' .  $row['pageNAME'] .'", "ID" : "' . $row['ID'] . '", "userID" : "' . $row['userID'] . '"},';
        }
        $response = substr($response, 0, -1);
        $response = $response . "]}";
        echo $response;
    }
}
?>
