/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function adminUsers() {
    $.ajax({
        url: "views/adminUsers.php",
        type: "GET",
        success: function(data) {
            $("#content").html(data);
        }
    });
}


