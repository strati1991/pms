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

//  --------- Root actions ---------
if ($_SESSION['role'] > "1") {
    if ($_GET["action"] == "changeRole") {
        query("UPDATE users SET role='" . $role . "' WHERE id = '" . $id . "'");
        echo "OK";
    }
    if ($_GET["action"] == "delete") {
        query("DELETE FROM users WHERE id = '" . $id . "'");
        echo "OK";
    }
    if ($_GET["action"] == "addPages") {
        $pages = split(",", urldecode($_GET["pages"]));
        for ($i = 0; $i < sizeof($pages); $i++) {
            echo("Select * FROM pages WHERE pageID = '" . $pages[$i] . "' and userID='" . $id . "'");
            $result = query("Select * FROM pages WHERE pageID = '" . $pages[$i] . "' and userID='" . $id . "'");
            if (mysql_num_rows($result) == 0) {
                $result = query("Select pageName FROM pages WHERE pageID = '" . $pages[$i] . "'");
                $row = mysql_fetch_assoc($result);
                query("INSERT INTO pages (pageName,pageID,userID) VALUES ('" . $row['pageName'] . "','" . $pages[$i] . "','" . $id . "') ON DUPLICATE KEY UPDATE userID='" . $id . "'");
            }
        }
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
        if (sizeof($sites) > 0) {
            $insert_pages = "INSERT IGNORE INTO pages (userID,ID,pageNAME) VALUES ";
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
            query($insert_pages, $link);
        }
        query("INSERT INTO users VALUES ('" . $id['id'] . "','" . $role . "','" . $username . "',0) ON DUPLICATE KEY UPDATE username='" . $username . "'");
        query("DELETE FROM register_notification WHERE userName='" . $username . "'");
        echo "OK";
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
            $result = query("INSERT INTO pages (userID,pageID,pageNAME) VALUES ('" . $_GET["id"] . "','" . $page['id'] . "','" . $_GET["page"] . "') ON DUPLICATE KEY UPDATE pageNAME='" . $_GET["page"] . "'");
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
                    if (mysql_num_rows(query("SELECT * FROM pages where userID='" . $id . "' AND pageID='" . $value['id'] . "'")) == 0) {
                        $insert_pages = "INSERT INTO pages (userID,pageID,pageNAME) VALUES ";
                        $insert_pages = $insert_pages . "('" . $id . "','" . $value['id'] . "','" . $value['name'] . "') ON DUPLICATE KEY UPDATE pageNAME='" . $value['name'] . "'";
                        echo $insert_pages;
                        query($insert_pages);
                        break;
                    }
                }
            }
        }
        echo "OK";
    }
}
//  --------- Root/Community Manager actions ---------
if ($_SESSION['role'] > "0") {
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
}
?>
