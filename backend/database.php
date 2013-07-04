<?php

function userRole($id) {
    $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
    if (!$link) {
        exit;
    }
    if (!mysql_select_db('usr_p158169_51', $link)) {
        exit;
    }
    $result = mysql_query("SELECT role FROM users WHERE id = '" . $id . "'", $link);
    if (!$result) {
        exit;
    }
    while ($row = mysql_fetch_assoc($result)) {
        return $row['role'];
    }
}

function listUsers() {
    $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
    if (!$link) {
        exit;
    }
    if (!mysql_select_db('usr_p158169_51', $link)) {
        exit;
    }
    $result = mysql_query("SELECT * FROM users", $link);
    if (!$result) {
        exit;
    }
    return $result;
}

function getUser($id) {
    $link = mysql_connect('db3473.mydbserver.com', 'p158169d31', 'x2$d76b!x#');
    if (!$link) {
        exit;
    }
    if (!mysql_select_db('usr_p158169_51', $link)) {
        exit;
    }
    $result = mysql_query("SELECT * FROM users WHERE id = '" . $id . "'", $link);
    if (!$result) {
        exit;
    }
    return $result;
}

?>
