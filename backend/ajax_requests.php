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
    query("INSERT IGNORE INTO posts (userID,message,startTime,picture,link)VALUES ('" . $_SESSION['ID'] . "','" . $_GET['message'] . "','" . $_GET['publishdate'] . "','" . $_GET['picture'] . "','" . $_GET['link'] . "')");
    echo "OK";
}

if ($_GET["action"] == "getPost") {
    $result = query("SELECT * FROM posts where postID='" . $id . "'");

    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    $row = mysql_fetch_assoc($result);
    echo '{ "message": "' . $row['message'] . '","startTime" :"' . $row['startTime'] . '","picture" :"' . $row['picture'] . '","link" :"' . $row['link'] . '"}';
}

if ($_GET["action"] == "updatePost") {
    query("UPDATE posts SET message='" . $_GET["message"] . "',link='" . $_GET["link"] . "',picture='" . $_GET["picture"] . "',startTime='" . $_GET["publishdate"] . "' WHERE postID = '" . $id . "'");
    echo "OK";
}

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
if ($_SESSION['role'] != "0") {
    if ($_GET["action"] == "changeRole") {
        query("UPDATE users SET role='" . $role . "' WHERE id = '" . $id . "'");
        echo "OK";
    }
    if ($_GET["action"] == "delete") {
        query("DELETE FROM users WHERE id = '" . $id . "'");
        echo "OK";
    }
    if ($_GET["action"] == "add") {
        try {
            $id = $facebook->api('/' . $username . '?fields=id', 'GET');
            $sites = $facebook->api('/' . $id['id'] . '/accounts', 'GET');
        } catch (Exception $e) {
            echo $errors['NOT_A_USER'];
            exit;
        }
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
        query("INSERT INTO users VALUES ('" . $id['id'] . "','" . $role . "','" . $username . "')");
        query($insert_pages, $link);
        query("DELETE FROM register_notification WHERE userName='" . $username . "'");
        echo "OK";
    }
    if ($_GET["action"] == "showPages") {
        $result = query("SELECT * FROM pages WHERE userID='" . $id . "'");
        $response = '{ "pages" : [';
        if (mysql_num_rows($result) == 0) {
            echo "{}";
        } else {
            while ($row = mysql_fetch_assoc($result)) {
                $response = $response . '{ "pageName" : "' . $row['pageName'] . '", "pageID" : "' . $row['pageID'] . '", "userID" : "' . $row['userID'] . '"},';
            }
            $response = substr($response, 0, -1);
            $response = $response . "]}";
            echo $response;
        }
    }
    if ($_GET["action"] == "addPage") {
        try {
            $page = $facebook->api("/" . $_GET["page"]);
        } catch (Exception $e) {
            echo $errors['NOT_A_PAGE'];
            exit;
        }
        $result = query("SELECT * FROM pages WHERE userID='" . $id . "' and pageID='" . $page['id'] . "'");
        if (mysql_num_rows($result) != 0) {
            echo "OK";
            exit;
        }
        if ($page['id'] != null) {
            $result = query("INSERT IGNORE INTO pages (userID,pageID,pageNAME) VALUES ('" . $_GET["id"] . "','" . $page['id'] . "','" . $_GET["page"] . "')");
            echo "OK";
        } else {
            echo $errors['NOT_A_PAGE'];
            exit;
        }
    }
    if ($_GET["action"] == "refresh") {
        $sites = $facebook->api('/' . $id . '/accounts', 'GET');
        $sites = $sites['data'];
        foreach ($sites as $value) {
            $perms = $value['perms'];
            foreach ($perms as $perm) {
                if ($perm == "CREATE_CONTENT") {
                    $insert_pages = "INSERT IGNORE INTO pages (userID,pageID,pageNAME) VALUES ";
                    $insert_pages = $insert_pages . "('" . $id . "','" . $value['id'] . "','" . $value['name'] . "')";
                    query($insert_pages);
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
