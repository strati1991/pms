<?php

ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once("config.php");

function query($query) {
    $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
    if (!$link) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    if (!mysql_select_db('usr_p158169_51', $link)) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    return mysql_query($query);
}

function userRole($id) {
    $result = query("SELECT role FROM users WHERE id = '" . $id . "'", $link);
    if (!$result) {
        return $errors['VALUE']; //not in system
    }
    while ($row = mysql_fetch_assoc($result)) {
        return $row['role'];
    }
}

function listUsers() {
    $result = query("SELECT *, customer.name as cname,users.id as facebookid FROM users left join customer on users.customer = customer.id", $link);
    if (!$result) {
        return $errors['DATABASE_CON']; //not in system
    }
    return $result;
}

function listPosts($user) {
    if (!$user && $_SESSION['role'] > 0) {
        $result = query("SELECT posts.*,users.username FROM posts join users on posts.userID = users.id", $link);
    } else {
        $result = query("SELECT posts.*,users.username FROM posts join users on posts.userID = users.id where userId = '" . $user . "'", $link);
    }
    if (!$result) {
        exit;
    }
    return $result;
}

function getUser($id) {
    $result = query("SELECT * FROM users WHERE id = '" . $id . "'", $link);
    if (!$result) {
        exit;
    }
    return $result;
}

?>
