<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL || E_STRICT);
require_once("../facebook-sdk/facebook.php");
require_once("../backend/config.php");
require_once("../backend/database_functions.php");

session_start();

$id = $_GET["id"];
$role = $_GET["role"];
$username = $_GET["username"];

$developerKey = "AI39si6x4grcCzTFYVWsrgufBWrgxd6TsR_XZEw8sxhl8bUNmbUh-wBzwKUjmX6L8eHmNfxUfDS8Vp_BbEAD6XVH0oIa4IBzLw";
$clientId = "PMS APP";
$applicationId = "PMS APP video upload";

$facebook = new Facebook($config);
$user = $facebook->getUser();
$name = $facebook->api("/" . $user . "?fields=username");

if ($_GET["action"] == "register") {
    if ($user) {
        query("INSERT INTO register_notification (id,userName,time) VALUES ('" . $user . "','" . $name['username'] . "',NOW())");
    } else {
        echo $errors['USER'];
    }
}

if ($_GET["action"] == "uploadVideo") {
    $video = $_FILES['file'];
    $ending = substr($video['name'], strlen($video['name']) - 4);
    if ($ending != ".mov" && $ending != ".mpg" && $ending != "mpeg" && $ending != "peg4" && $ending != ".avi" && $ending != ".wmv" && $ending != ".flv") {
        echo '{' .
        '"error":' . '"' . $errors['WRONG_IMAGE_TYPE'] . '"' .
        '}';
        unlink($video['tmp_name']);
        exit;
    }
    $path = "../img/uploads/" . time() . "_" . basename($video['name']);
    move_uploaded_file($video['tmp_name'], $path);
    echo '{"files": [' .
        '{' .
            '"name": "' . $video['name'] . '",' .
            '"size": ' . $video['size'] . ',' .
            '"url":' . '"' . substr($path, 3) . '"' .
        '}' .
    ']}';
}


if ($_GET["action"] == "uploadImage") {
    $image = $_FILES['file'];
    $ending = substr($image['name'], strlen($image['name']) - 4);
    if (intval($image['size']) > 100000) {
        echo '{' .
        '"error":' . '"' . $errors['IMAGE_TO_LARGE'] . '"' .
        '}';
        unlink($image['tmp_name']);
        exit;
    }
    if ($ending != ".jpg" && $ending != ".png" && $ending != "jpeg") {
        echo '{' .
        '"error":' . '"' . $errors['WRONG_IMAGE_TYPE'] . '"' .
        '}';
        unlink($image['tmp_name']);
        exit;
    }
    $path = "../img/uploads/" . time() . "_" . basename($image['name']);
    move_uploaded_file($image['tmp_name'], $path);
    echo '{"files": [' .
    '{' .
    '"name": "' . $image['name'] . '",' .
    '"size": ' . $image['size'] . ',' .
    '"url":' . '"' . substr($path, 3) . '"' .
    '}' .
    ']}';
}
if ($_GET["action"] == "createSession") {
    $result = query("SELECT * FROM users WHERE id = '" . $user . "'");
    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    $num_results = mysql_num_rows($result);
    if ($num_results == 0) {
        echo $errors['NOT_IN_DATABASE'];
    }
    while ($row = mysql_fetch_assoc($result)) {
        $_SESSION['ID'] = $user;
        $_SESSION['role'] = $row['role'];
        $_SESSION['username'] = $row['username'];
        echo ' { "id": "' . $user . '", "role": "' . $row['role'] . '", "username": "' . $row['username'] . '"}';
    }
}
if ($_GET["action"] == "getNotifications") {
    $result = query("SELECT * FROM notifications WHERE `for` = " . $user . "");
    query("DELETE FROM notifications WHERE `for` = " . $user . "");
    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    $num_results = mysql_num_rows($result);
    if ($num_results == 0) {
        echo '{"notifications": []}';
        exit;
    }
    $get = '{"notifications": [';
    while ($row = mysql_fetch_assoc($result)) {
        $get = $get . ' { "dataID": "' . $row['dataID'] . '", "type": "' . $row['type'] . '", "dataText": "' . $row['dataText'] . '"},';
    }
    $get = substr($get, 0, -1);
    $get = $get . "]}";
    echo $get;
}
if ($_GET["action"] == "destroySession") {
    $facebook->destroySession();
    session_destroy();
    exit;
}
?>
