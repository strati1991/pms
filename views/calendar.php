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
    var dates = [
<?php
$counter = 0;
while ($row = mysql_fetch_array($posts)) {
    if ($counter != 0) {
        echo ",";
    }
    echo '{';
    if ($row["status"] == 0) {
        $style = "not-viewed";
        $color = "#b94a48";
    }
    if ($row["status"] == 1) {
        $style = "rejected";
        $color = "#c09853";
    }
    if ($row["status"] == 2) {
        $style = "released";
        $color = "#468847";
    }
    if ($row["startTime"] == "0000-00-00 00:00:00") {
        echo '"start" : new Date("' . $row["lastChanged"] . '"), ';
    } else {
        echo '"start" : new Date("' . $row["startTime"]  . '"),';
    }
    echo '"status" : "' . $row['status'] . '",';
    echo '"ID" : "' . $row['postID'] . '",';
    echo '"title" : "' . str_replace("+"," ", urlencode($row['message'])) . '", ';
    echo '"className" : "' . $style . '", ';
    echo '"color" : "' . $color . '", ';
    echo '"allDay" : false ';
    echo "}";
    $counter++;
}
$counter = 0;
?>];
</script>
<link rel="stylesheet" type="text/css" href="css/pickadate.03.inline.css" />
<div class="page-header" style="padding-bottom: 70px;">

    <h1 class="pull-left">Kalender</h1>
    <div class="well well-small pull-right" style="margin-top: 12px;">
            <span class="label label-important">Noch nicht angesehen</span>
            <span class="label label-warning">Es gibt Korrekturen</span>
            <span class="label label-success">Freigegen</span>
    </div>
</div>


<div id="calendar"></div>
<ul id="date-pages" style="display:none" class="well well-large" >
</ul>
<?php
require_once("../js/calendar.php");
?>
