<?php

ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once("../facebook-sdk/facebook.php");
require_once("../backend/config.php");
require_once("../backend/database_functions.php");
session_start();

$id = $_GET["id"];
$role = $_GET["role"];
$username = $_GET["username"];

$facebook = new Facebook($config);
$user = $facebook->getUser();
$name = $facebook->api("/" . $user . "?fields=username");
if ($_GET["action"] == "addPost") {
    query("INSERT IGNORE INTO posts (lastChanged,userID,message,startTime,picture,link)VALUES (NOW(),'" . $user . "','" . mysql_escape_string($_GET['message']) . "','" . $_GET['publishdate'] . "','" . mysql_escape_string($_GET['picture']) . "','" . mysql_escape_string($_GET['link']) . "')");
    $result = query("SELECT * from posts order by postID limit 1");
    if ($_GET["pages"] != "") {
        $pages = split(",", $_GET["pages"]);
        $row = mysql_fetch_assoc($result);
        if ($row) {
            $id = $row['postID'];
            for ($i = 0; $i < sizeof($pages); $i++) {
                query("INSERT INTO pages (postID,pageID,userID) VALUES ('" . $id . "','" . $pages[$i] . "','" . $user . "') ON DUPLICATE KEY UPDATE");
            }
        }
    }
    echo "OK";
}
if ($_GET["action"] == "statusPost") {
    query("UPDATE posts SET lastChanged=NOW(),status='" . mysql_escape_string($_GET["status"]) . "' WHERE postID = '" . $id . "'");
    echo "OK";
}
if ($_GET["action"] == "getPostOnPages") {
    $result = query("SELECT * FROM posts_on_pages where postID='" . $id . "'");
    $num_results = mysql_num_rows($result);
    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    if ($num_results == 0) {
        echo '{ "pages":[]}';
        exit;
    }

    $get = '{ "pages":[';
    while ($row = mysql_fetch_assoc($result)) {
        $get = $get . '{ "ID": "' . $row['pageID'] . '"},';
    }
    $get = substr($get, 0, -1);
    $get = $get . "]}";
    echo $get;
}

if ($_GET["action"] == "getComments") {
    $result = query("SELECT comments.id as ID,postID,userID,username,text FROM comments,users where postID='" . $id . "' and comments.userID = users.id order by comments.ID");
    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    $num_results = mysql_num_rows($result);
    if ($num_results == 0) {
        echo '{ "comments":[]}';
        exit;
    }
    $get = '{ "comments":[';
    while ($row = mysql_fetch_assoc($result)) {
        $get = $get . '{ "ID": "' . $row['ID'] . '","userID": "' . $row['userID'] . '", "postID": "' . $row['postID'] . '", "username": "' . $row['username'] . '", "text": "' . $row['text'] . '"},';
    }
    $get = substr($get, 0, -1);
    $get = $get . "]}";
    echo $get;
}
if ($_GET["action"] == "addComment") {
    query("INSERT IGNORE INTO comments (postID,userID,text) VALUES ('" . $_GET['postID'] . "','" . $user . "','" . mysql_escape_string($_GET['comment']) . "')");
    echo "OK";
}
if ($_GET["action"] == "deleteComment") {
    query("DELETE FROM comments where ID='" . $id . "'");
    echo "OK";
}
if ($_GET["action"] == "deletePost") {
    query("DELETE FROM posts where postID='" . $id . "'");
    echo "OK";
}
if ($_GET["action"] == "getPost") {
    $result = query("SELECT * FROM posts where postID='" . $id . "'");

    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    $row = mysql_fetch_assoc($result);
    echo '{ "message": "' . mysql_escape_string($row['message']) . '","startTime" :"' . $row['startTime'] . '","picture" :"' . mysql_escape_string($row['picture']) . '","link" :"' . mysql_escape_string($row['link']) . '"}';
}

if ($_GET["action"] == "updatePost") {
    query("UPDATE posts SET lastChanged=NOW(),message='" . $_GET["message"] . "',link='" . $_GET["link"] . "',picture='" . $_GET["picture"] . "',startTime='" . $_GET["publishdate"] . "' WHERE postID = '" . $id . "'");
    if ($_GET["pages"] != "") {
        $pages = split(",", $_GET["pages"]);
        for ($i = 0; $i < sizeof($pages); $i++) {
            query("INSERT IGNORE INTO posts_on_pages (postID,pageID,userID) VALUES ('" . $id . "','" . $pages[$i] . "','" . $user . "')");
        }
    }
    echo "OK";
}
?>
