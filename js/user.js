/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 function getUserRole(callback){
     FB.api("/me/picture?width=32&height=32", function(response) {
         $.ajax({
            url: "/backend/?command=get_user_role&id=" + response.id
         }).done(function(data){
             callback(data);
         });
     });
     
 }


