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
<ul id="date-pages" style="display:none" >
</ul>
<script type="text/javascript" src="js/vendor/pickadate.js"></script>
<script type="text/javascript">
    $(function() {
        var picker = $('#calendar').pickadate();
        picker.click();
        refreshCal();

    });
    function refreshCal() {
        $.each(dates, function(index, value) {
            var htmlDate = $(".pickadate__body div[data-date='" + index + "']");
            if (value.status == 0) {
                htmlDate.addClass("not-reviewed");
                htmlDate.removeClass("rejected");
                htmlDate.removeClass("released");
            }
            if (value.status == 1) {
                htmlDate.addClass("rejected");
                htmlDate.removeClass("not-reviewed");
                htmlDate.removeClass("released");
            }
            if (value.status == 2) {
                htmlDate.addClass("released");
                htmlDate.removeClass("rejected");
                htmlDate.removeClass("not-reviewed");
            }

        });
    }
    function dateClicked(date) {
        if (dates[date] !== undefined) {
            $.ajax("backend/ajax_posts.php?action=getPostByDate&date=" + escape(date)).done(function(response) {
                var posts = $.parseJSON(response);
                var dateHTML = "";
                $.each(posts.posts, function(index, value) {
                    var style = "";
                    if(value.status == '0'){
                         style="label label-important";
                    }
                    if(value.status == '1'){
                         style="label label-warning";
                    }
                    if(value.status == '2'){
                         style="label label-success";
                    }
                    if (value.startTime == "0000-00-00 00:00:00") {
                        dateHTML += "<li class='" + style + "' onclick='showPosts(" + value.ID + ")'>Post " + value.message.substr(0, 10) + "... vom " + value.lastChanged + "</li>";
                    } else {
                        dateHTML += "<li class='" + style + "' onclick='showPosts(" + value.ID + ")'>Post " + value.message.substr(0, 10) + "... vom " + value.startTime + "</li>";
                    }

                });
                $("#date-pages").hide();
                $("#date-pages").html(dateHTML);
                $("#date-pages").fadeIn(500);
            });
        }
    }
    function showPosts(id){
        load("posts",function(){
             view(id);
        });
       
    }
</script>
