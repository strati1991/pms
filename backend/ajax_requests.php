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

if ($_GET["action"] == "register") {
    if ($user) {
        query("INSERT INTO register_notification (id,userName,time) VALUES ('" . $user . "','" . $name['username'] . "',NOW())");
    } else {
        echo $errors['USER'];
    }
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
if ($_GET["action"] == "destroySession") {
    $facebook->destroySession();
    session_destroy();
    exit;
}
?>
