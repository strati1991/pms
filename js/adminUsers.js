view.init = function() {
    var script1 = $.getScript("js/vendor/bootstrap-multiselect.js"),
        script2 = $.getScript("js/vendor/jquery.dataTables.min.js");
    $.when(script1, script2).done(function(result2, result1) {
        $('#userlist').dataTable({
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": true,
            "fnInitComplete": function() {
                $('#userlist').fadeIn();
                helper.finished();
            }
        });
        $('.view-content .has-tooltip-bottom').tooltip({
            placement: 'bottom',
            html: true
        });
        $('.view-content .has-tooltip-left').tooltip({
            placement: 'left',
            html: true
        });
    })

};
var users = {
    addPage: function(id) {
        $("#new-page-error").hide();
        helper.showModal({
            content: $("#add-pages").html(),
            saveLabel: "hinzufügen",
            title: "Seite hinzufügen",
            saveFunction: function() {
                if ($("#modal-dialog #new-page").val() == "") {
                    $("#modal-dialog #new-page-error").show();
                    return;
                }
                var pages = "";
                $.each($("#modal-dialog #multiselect option:selected"), function() {
                    pages = pages + $(this).attr("value") + ",";
                });
                pages = pages.substr(0, pages.length - 1);
                helper.loading()
                $.ajax({
                    url: "backend/ajax_users.php?action=addPages",
                    data: {
                        id: id,
                        pages: escape(pages)
                    }
                }).done(function(response) {
                    helper.handleError(response, function() {
                        helper.finished()
                        $('#modal-dialog').modal('hide');
                    });
                });
            }
        });
    },
    deleteUser: function(id) {
        helper.showModal({
            content: $("#delete-dialog").html(),
            saveLabel: "löschen",
            title: "User löschen",
            saveFunction: function() {
                $.ajax({
                    url: "backend/ajax_users.php?action=delete&id=" + id,
                    success: function(data) {
                        if (data == "OK") {
                            $('#modal-dialog').modal('hide');
                            helper.load("adminUsers");
                        } else {
                            helper.handleError(data);
                        }
                    }
                });
            }
        });
    },
    changeRole: function(id) {
        helper.showModal({
            content: $("#change-dialog").html(),
            saveLabel: "Ja",
            title: "Userrolle ändern",
            preShowFunction: function() {
                $("#modal-dialog #select-role-change .active").removeClass();
                $("#modal-dialog .modal-username").html($("#name_" + id).html());
                $("#modal-dialog #select-role-change").find("[value=" + $("#role_" + id).attr("data-role") + "]").addClass("active");
            },
            saveFunction: function() {
                $.ajax({
                    url: "backend/ajax_users.php?action=changeRole&id=" + id + "&role=" + $("#modal-dialog #select-role-change .active").val(),
                    success: function(data) {
                        if (data == "OK") {
                            $('#modal-dialog').modal('hide');
                            helper.load("adminUsers");
                        } else {
                            helper.handleError(data);
                        }
                    }
                });
            }
        });
    },
    addUser: function() {
        helper.showModal({
            content: $("#add-dialog").html(),
            saveLabel: "hinzufügen",
            title: "User hinzufügen",
            preShowFunction: function() {
                $("#modal-dialog #modal-facebook-name-error").hide();
                $("#modal-dialog #select-role-add-error").hide();
            },
            saveFunction: function() {
                if ($("#modal-dialog #modal-facebook-name").val() === "") {
                    $("#modal-dialog #modal-facebook-name-error").show();
                    return;
                }
                if (!$("#modal-dialog #select-role-add .active").length) {
                    $("#modal-dialog #select-role-add-error").show();
                    return;
                }
                $("#loading-screen").fadeIn();
                $.ajax({
                    url: "backend/ajax_users.php?action=add&username=" + $("#modal-dialog #modal-facebook-name").val() + "&role=" + $("#modal-dialog #select-role-add .active").val(),
                    success: function(data) {
                        $("#loading-screen").fadeOut();
                        if (data == "OK") {
                            $('#modal-dialog').modal('hide');
                            helper.load("adminUsers");
                        } else {
                            helper.handleError(data);
                        }
                    }
                })
            }
        });

    },
    showPages: function(id,role) {
        helper.loading();
        $.ajax({
            url: "backend/ajax_users.php?action=showPages&id=" + id,
            dataType: "json",
            success: function(data) {
                helper.showModal({
                    preShowFunction: function() {
                        var pages = "";
                        if (data.pages !== undefined) {
                            $.each(data.pages, function(index, value) {
                                pages = pages + '<li><a target="_blank" href="https://www.facebook.com/' + value.pageID + '">' + value.pageName + '</a></li>';
                            });
                        }
                        $("#modal-dialog #pages-list").html(pages);
                        if(role === 0){
                            $("#modal-dialog #add-page-button").attr("onclick", "users.addPage(" + id + ");");
                            $("#modal-dialog #add-page-button").show();
                            $("#modal-dialog #modal-role").text("publizieren");
                        } else {
                            $("#modal-dialog #add-page-button").hide();
                             $("#modal-dialog #modal-role").text("freigeben/publizieren");
                        }  
                        $("#modal-dialog .modal-username").html($("#name_" + id).html());
                        helper.finished()
                    },
                    content: $("#show-pages").html(),
                    title: "Seiten",
                    saveLabel: "Schließen",
                    hideCloseButton: true
                });
            }
        });
    },
    refreshUsers: function(id) {
        $("#loading-screen").fadeIn();
        $.ajax("backend/ajax_users.php?action=refresh&id=" + id).done(function() {
            helper.finished();
            users.showPages(id);
        });
    },
    refreshPage: function() {
        helper.load("adminUsers");
    }
};