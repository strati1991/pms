<script type="text/javascript">
    var calendar = {
        loadCalDa: function() {
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
        renderEvent: function(date, element) {
            $.ajax("backend/ajax_posts.php?action=getPostByID&id=" + date.ID).done(function(response) {
                var post = $.parseJSON(response),
                        icon = "",
                        time = post.startTime;
                if (post.startTime == "0000-00-00 00:00:00") {
                    time = post.lastChanged;
                }
                var dateHTML = (post.picture != "" ?
                        "<img src='" + post.picture + "' >" :
                        "")
                        + "<p style='color:black'>" + post.message + "</span>" +
                        (post.link != "" ?
                                "<a target=_blank href='" + post.link + "' >" + post.link + "</a>" : "");
                element.popover({
                    html: true,
                    title: time,
                    placement: 'top',
                    content: dateHTML,
                    trigger: 'hover'
                });

            });

        },
        showPost: function(id) {
            helper.load("posts", function() {
                helper.loading();
                posts.view(id);
                helper.finished();
            });

        }
    };
    view.init = function() {
        var script1 = $.getScript("js/vendor/fullcalendar.min.js");
        $.when(script1).done(function(result2, result1) {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultEventMinutes:60,
                axisFormat:"HH:00",
                slotMinutes:60,
                height: 700,
                weekMode:'liquid',
                editable: false,
                eventRender: calendar.renderEvent,
                events: dates,
                firstDay:1,
                eventClick: function(calEvent, jsEvent, view) {
                    calendar.showPost(calEvent.ID);
                }
            });

            helper.finished();
        });
    };
</script>