<?php

ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once("../facebook-sdk/facebook.php");
$config = array();
$config['appId'] = '192351964261671';
$config['secret'] = '2c0ce846356ab46e072b68aae2bcc3db';
$facebook = new Facebook($config);
session_start();
if ($_SESSION['role'] == 1) {
    if ($_GET["action"] == "changeRole") {
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
    }
    if ($_GET["action"] == "delete") {

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
    }
    if ($_GET["action"] == "add") {

        $username = $_GET["username"];
        $role = $_GET["role"];
        $id = $facebook->api('/' . $username . '?fields=id', 'GET');
        $sites = $facebook->api('/' . $id['id'] . '/accounts', 'GET');
        $sites = $sites['data'];
        $insert_pages = "INSERT INTO pages (userID,ID,pageNAME) VALUES ";
        foreach ($sites as $value) {
            $perms = $value['perms'];
            foreach ($perms as $perm) {
                if ($perm == "CREATE_CONTENT") {
                    $insert_pages = $insert_pages . "('" . $id['id'] . "','" . $value['id'] . "','" . $value['name'] . "'),";
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
    }
    if ($_GET["action"] == "showPages") {

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
            $response = $response . '{ "pageName" : "' . $row['pageNAME'] . '", "ID" : "' . $row['ID'] . '", "userID" : "' . $row['userID'] . '"},';
        }
        $response = substr($response, 0, -1);
        $response = $response . "]}";
        echo $response;
    }
    if ($_GET["action"] == "refresh") {

        $id = $_GET["id"];
        $sites = $facebook->api('/' . $id . '/accounts', 'GET');
        $sites = $sites['data'];
        $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
        if (!$link) {
            exit;
        }
        if (!mysql_select_db('usr_p158169_51', $link)) {
            exit;
        }
        foreach ($sites as $value) {
            $perms = $value['perms'];
            foreach ($perms as $perm) {
                if ($perm == "CREATE_CONTENT") {
                    $insert_pages = "INSERT IGNORE INTO pages (userID,ID,pageNAME) VALUES ";
                    $insert_pages = $insert_pages . "('" . $id . "','" . $value['id'] . "','" . $value['name'] . "')";
                    mysql_query($insert_pages, $link);
                    break;
                }
            }
        }
        echo "OK";
    }
} else {
    exit;
}
?>
