view.init = function() {
    var script1 = $.getScript("js/vendor/pickadate.js");
    $.when(script1).done(function(result2, result1) {
        var picker = $('#calendar').pickadate();
        picker.click();
        calendar.refreshCal();
    });
};
var calendar = {
    refreshCal: function() {
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
    },
    dateClicked: function(date) {
        if (dates[date] !== undefined) {
            $.ajax("backend/ajax_posts.php?action=getPostByDate&date=" + escape(date)).done(function(response) {
                var posts = $.parseJSON(response);
                var dateHTML = "";
                $.each(posts.posts, function(index, value) {
                    var style = "";
                    if (value.status == '0') {
                        style = "text-error";
                    }
                    if (value.status == '1') {
                        style = "text-warning";
                    }
                    if (value.status == '2') {
                        style = "text-success";
                    }
                    if (value.startTime == "0000-00-00 00:00:00") {
                        dateHTML += "<li onclick='calendar.showPost(" + value.ID + ")'><i class='icon-arrow-right icosn-white'></i><span class='" + style + "'>Post '" + value.message.substr(0, 10) + "...' vom " + value.lastChanged + "</span></li>";
                    } else {
                        dateHTML += "<li onclick='calendar.showPost(" + value.ID + ")'><i class='icon-arrow-right icosn-white'></i><span class='" + style + "'>Post '" + value.message.substr(0, 10) + "...' vom " + value.startTime + "</span></li>";
                    }

                });
                $("#date-pages").hide();
                $("#date-pages").html(dateHTML);
                $("#date-pages").fadeIn(500);
            });
        }
    },
    showPost: function(id) {
        helper.load("posts", function() {
            helper.loading();
            posts.view(id);
            helper.finished();
        });

    }
};