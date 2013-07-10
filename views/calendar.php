<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
require_once('../backend/database_functions.php');
require_once("../backend/config.php");
session_start();
if ($_SESSION['role'] > 0) {
    $posts = listPosts();
} else {
    $posts = listPosts($_SESSION['ID']);
}
?>
<script>
    var dates = {
<?php

function formatDate($date) {
    $array = split("-", $date);
    return $array[0] . "/" . intval($array[1]) . "/" . intval($array[2]);
}

$counter = 0;
while ($row = mysql_fetch_array($posts)) {
    if ($counter != 0) {
        echo ",";
    }
    if ($row["startTime"] == "0000-00-00 00:00:00") {
        echo '"' . formatDate($row["lastChanged"]) . '" : {';
    } else {
        echo '"' . formatDate($row["startTime"]) . '" : {';
    }
    echo '"status" : "' . $row['status'] . '" ';
    echo "}";
    $counter++;
}
$counter = 0;
?>};
</script>
<link rel="stylesheet" type="text/css" href="css/pickadate.03.inline.css" />
<div class="page-header">
    <h1>Kalender</h1>
</div>
<input id="calendar" style="display:none" type="text"/>
<ul id="date-pages" style="display:none" class="well well-large" >
</ul>
<script type="text/javascript" src="js/calendar.js"></script>
