view.init = function() {
    $('#userlist').dataTable({
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": true,
        "fnInitComplete": function() {
            $('#userlist').fadeIn();
        }
    });
};
var users = {
    addPage: function(id) {
        $("#new-page-error").hide();
        showModal({
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
                    handleError(response, function() {
                        helper.finished()
                        $('#modal-dialog').modal('hide');
                    });
                });
            }
        });
    },
    deleteUser: function(id) {
        showModal({
            content: $("#delete-dialog").html(),
            saveLabel: "löschen",
            title: "User löschen",
            saveFunction: function() {
                $.ajax({
                    url: "backend/ajax_users.php?action=delete&id=" + id,
                    success: function(data) {
                        if (data == "OK") {
                            $('#modal-dialog').modal('hide');
                            load("adminUsers");
                        } else {
                            handleError(data);
                        }
                    }
                });
            }
        });
    },
    changeRole: function(id) {
        showModal({
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
                            load("adminUsers");
                        } else {
                            handleError(data);
                        }
                    }
                });
            }
        });
    },
    addUser: function() {
        showModal({
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
                            load("adminUsers");
                        } else {
                            handleError(data);
                        }
                    }
                })
            }
        });

    },
    showPages: function(id) {
        $.ajax({
            url: "backend/ajax_users.php?action=showPages&id=" + id,
            dataType: "json",
            preShowfunction :function(){
                helper.loading()
                $("#add-page-button").attr("onclick", "addPage(" + id + ");");
                $(".modal-username").html($("#name_" + id).html());
            },
            success: function(data) {
                var pages = "";
                if (data.pages !== undefined) {
                    $.each(data.pages, function(index, value) {
                        pages = pages + '<li><a target="_blank" href="https://www.facebook.com/' + value.pageID + '">' + value.pageName + '</a></li>';
                    });
                }
                $("#pages-list").html(pages);
                helper.finished()
                showModal({
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
            this.showPages(id);
        });
    },
    refreshPage: function() {
        load("adminUsers");
    }
}