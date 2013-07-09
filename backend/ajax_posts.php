<?php

ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once("../facebook-sdk/facebook.php");
require_once("../backend/config.php");
require_once("../backend/database_functions.php");
session_start();

$id = $_GET["id"];
$role = $_GET["role"];

$facebook = new Facebook($config);
$user = $facebook->getUser();
$facebook->setFileUploadSupport(true);
if ($role != "0") {
    if ($_GET["action"] == "releasePost") {
        $post = query("SELECT DISTINCT * FROM posts where postID ='" . $id . "'");
        $pages = query("SELECT DISTINCT * FROM posts_on_pages where postID='" . $id . "'");
        $num_results = mysql_num_rows($pages);
        if (!$pages) {
            echo $errors['DATABASE_CON'];
            exit;
        }
        if ($num_results == 0) {
            echo '{ "post": "","pages": []}';
            exit;
        }
        $num_results = mysql_num_rows($post);
        if (!$post) {
            echo $errors['DATABASE_CON'];
            exit;
        }
        if ($num_results == 0) {
            echo '{ "post": "","pages": []}';
            exit;
        }
        $post_data = array();
        $row = mysql_fetch_assoc($post);
        if ($row['message']) {
            $post_data['message'] = $row['message'];
        }
        if ($row['startTime'] && strtotime($row['startTime']) != "") {
            $post_data['published'] = 'false';
            $post_data['scheduled_publish_time'] = strtotime($row['startTime']);
        }
        if ($row['picture']) {
            $post_data['source'] = $row['picture'];
        }
        if ($row['link']) {
            $post_data['link'] = $row['link'];
        }
        $my_pages = $facebook->api("/me/accounts");
        $my_pages = $my_pages["data"];
        $access_tokens = array();
        for ($i = 0; $i < sizeof($my_pages); $i++) {
            $access_tokens[$my_pages[$i]["id"]] = $my_pages[$i]["access_token"];
        }
        while ($row = mysql_fetch_assoc($pages)) {
            $facebook->setAccessToken($access_tokens[$row['pageID']]);
            if ($post_data['source']) {
                $args = array(
                        'url' => 'http://pms.social-media-hosting.com/' . $post_data['source'],
                        'message' => $post_data['message']
                    );
                try {
                    $data = $facebook->api('/' . $row['pageID'] . '/photos', 'post', $args);
                } catch (FacebookApiException $e) {
                    echo $e->getMessage();
                    exit;
                }
                echo "OK";
                exit;
            } else {
                $post_url = '/' . $row['pageID'] . '/feed';
            }
            try {
                $facebook->api($post_url, 'post', $post_data);
            } catch (FacebookApiException $e) {
                echo $e->getMessage();
                exit;
            }
        }
        echo "OK";
    }
}
if ($_GET["action"] == "addPost") {
    query("INSERT IGNORE INTO posts (lastChanged,userID,message,startTime,picture,link) " .
            "VALUES (" .
            "NOW()," .
            "'" . $user . "'," .
            "'" . mysql_escape_string($_GET['message']) . "'," .
            "'" . $_GET['publishdate'] . "'," .
            "'" . mysql_escape_string($_GET['picture']) . "'," .
            "'" . mysql_escape_string($_GET['link']) . "'" .
            ")");
    $result = query("SELECT * FROM posts where userID='" . $user . "' and message='" . mysql_escape_string($_GET['message']) . "' order by lastChanged LIMIT 1");
    $row = mysql_fetch_assoc($result);
    if ($_GET["pages"] != "") {
        $pages = split(",", urldecode($_GET["pages"]));
        for ($i = 0; $i < sizeof($pages); $i++) {
            query("INSERT  IGNORE INTO posts_on_pages (postID,pageID,userID) VALUES ('" . $row['postID'] . "','" . $pages[$i] . "','" . $user . "')");
        }
    }
    notificate($row['postID'], $notifications["post_added"], substr($_GET['message'], 0, 10) . "... vom " . $row['lastChanged']);
    echo "OK";
}
if ($_GET["action"] == "statusPost") {
    query("UPDATE posts SET status='" . mysql_escape_string($_GET["status"]) . "' WHERE postID = '" . $id . "' LIMIT 1");
    echo "OK";
    exit;
}
if ($_GET["action"] == "getPostByDate") {
    $date = str_replace("/", "-", $_GET["date"]);
    $sql = "SELECT *" .
            "FROM posts where " .
            "(startTime >='" . $date . " 00:00:00' " .
            "and startTime < '" . $date . " 23:59:59') " .
            "or " .
            "(startTime = '0000-00-00 00:00:00' " .
            "and lastChanged >='" . $date . " 00:00:00' " .
            "and lastChanged <= '" . $date . " 23:59:59')";
    if ($_SESSION['role'] == 0) {
        $sql = $sql . "and userID='" . $_SESSION["ID"] . "'";
    }
    $sql = $sql . "and userID='" . $_SESSION["ID"] . "' order by status,lastChanged,startTime";
    $post = query($sql);
    if (!$post) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    if ($post == 0) {
        echo '{ "posts":[]}';
        exit;
    }

    $get = '{ "posts":[';
    while ($row = mysql_fetch_assoc($post)) {
        $get = $get . '{' .
                '"ID": "' . $row['postID'] . '",' .
                '"message": "' . $row['message'] . '",' .
                '"startTime": "' . $row['startTime'] . '",' .
                '"lastChanged": "' . $row['lastChanged'] . '",' .
                '"status": "' . $row['status'] . '"' .
                '},';
    }
    $get = substr($get, 0, -1);
    $get = $get . "]}";
    echo $get;
}
if ($_GET["action"] == "getPostOnPages") {
    $result = query("SELECT DISTINCT posts_on_pages.*,pages.pageName " .
            "FROM posts_on_pages " .
            "join pages on " .
            "pages.pageID = posts_on_pages.pageID " .
            "where postID='" . $id . "'");
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
        $get = $get . '{' .
                '"ID": "' . $row['pageID'] . '",' .
                '"pageName": "' . $row['pageName'] . '"' .
                '},';
    }
    $get = substr($get, 0, -1);
    $get = $get . "]}";
    echo $get;
}

if ($_GET["action"] == "getComments") {
    $result = query("SELECT comments.id as ID,postID,userID,username,text " .
            "FROM comments,users " .
            "where postID='" . $id . "' " .
            "and comments.userID = users.id " .
            "order by comments.ID");
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
        $get = $get . '{' .
                '"ID": "' . $row['ID'] . '",' .
                '"userID": "' . $row['userID'] . '",' .
                '"postID": "' . $row['postID'] . '",' .
                '"username": "' . $row['username'] . '",' .
                '"text": "' . $row['text'] . '"' .
                '   },';
    }
    $get = substr($get, 0, -1);
    $get = $get . "]}";
    echo $get;
}

function notificate($postID, $type, $dataText) {
    $result = query("SELECT userID
                FROM (
                     SELECT DISTINCT userID
                     FROM pages
                     JOIN (
                         SELECT posts_on_pages.pageID AS pageID
                         FROM  `posts` 
                         JOIN posts_on_pages ON posts_on_pages.postID = posts.postID
                         WHERE posts.postID =  '" . $postID . "'
                     ) AS post_pages ON pages.pageID = post_pages.pageID
                 ) AS users_with_page
            JOIN users ON users.id = users_with_page.userID
            WHERE role >0 and userID <> '" . $user . "'");
    while ($row = mysql_fetch_assoc($result)) {
        query("INSERT IGNORE INTO `notifications`(`for`, `type`, `dataID`,`dataText`) " +
                "VALUES (" .
                $row['userID'] . "," .
                $type . "," .
                $postID . "," .
                "'" . mysql_real_escape_string($dataText) . "," .
                ")");
    }
}

if ($_GET["action"] == "addComment") {
    query("INSERT IGNORE INTO comments (postID,userID,text) " .
            "VALUES ('" .
            $_GET['postID'] . "'," .
            "'" . $user . "'," .
            "'" . mysql_escape_string($_GET['comment']) . "'" .
            ")");
    $result = query("SELECT username FROM users WHERE id=" . $_GET['postID'] . "");
    $row = mysql_fetch_assoc($result);
    notificate($_GET['postID'], $notifications["comment"], $row['username']);
    echo "OK";
}
if ($_GET["action"] == "deleteComment") {
    query("DELETE FROM comments where ID='" . $id . "'");
    echo "OK";
}
if ($_GET["action"] == "deletePost") {
    $result = query("SELECT *  FROM posts WHERE postID=" . $id);
    $row = mysql_fetch_assoc($result);
    notificate($id, $notifications["post_deletet"], substr($row['message'], 0, 10) . "... vom " . $row['lastChanged']);
    query("DELETE FROM posts where postID='" . $id . "'");
    query("DELETE FROM posts_on_pages WHERE postID='" . $id . "'");
    query("DELETE FROM comments WHERE postID='" . $id . "'");
    echo "OK";
}
if ($_GET["action"] == "getPost") {
    $result = query("SELECT * FROM posts where postID='" . $id . "'");

    if (!$result) {
        echo $errors['DATABASE_CON'];
        exit;
    }
    $row = mysql_fetch_assoc($result);
    echo '{ "message": "' . mysql_escape_string($row['message']) . '",' .
    '"startTime" :"' . $row['startTime'] . '",' .
    '"picture" :"' . mysql_escape_string($row['picture']) . '",' .
    '"link" :"' . mysql_escape_string($row['link']) . '"' .
    '}';
}

if ($_GET["action"] == "updatePost") {
    if ($_GET["message"] != "undefined" && $_GET["link"] != "undefined" && $_GET["picture"] != "undefined") {
        query("UPDATE posts SET " .
                "status=0," .
                "lastChanged=NOW()," .
                "message='" . $_GET["message"] . "'," .
                "link='" . $_GET["link"] . "'," .
                "picture='" . $_GET["picture"] . "'," .
                "startTime='" . $_GET["publishdate"] . "'" .
                "WHERE postID = '" . $id . "'");
        if ($_GET["pages"] != "") {
            query("DELETE FROM posts_on_pages WHERE postID='" . $id . "'");
            $pages = split(",", urldecode($_GET["pages"]));
            for ($i = 0; $i < sizeof($pages); $i++) {
                query("INSERT IGNORE INTO posts_on_pages (postID,pageID,userID) " .
                        "VALUES (" .
                        "'" . $id . "'," .
                        "'" . $pages[$i] . "'," .
                        "'" . $user . "'" .
                        ")");
            }
        }
        notificate($id, $notifications["post_updated"], substr($row['message'], 0, 10) . "... vom " . $row['lastChanged']);
        echo "OK";
        exit;
    }
    echo "OK";
}
?>
