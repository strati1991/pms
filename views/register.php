<?php
ini_set('display_errors', 'On');
error_reporting(E_all || E_STRICT);
?>
<div class="page-header">
    <h1>Registrieren</h1> 
</div>

<p>Du bist noch nicht registriert.Wenn du dich im System registrieren möchtest dann klicke bitte auf folgenden Button:</p><p><button class="btn" onclick="register()">Registrieren</button></p>
<p>Unseren Administratoren wird dann eine Mail mit deinem Facebook-Namen zugeschickt und du wirst eingetragen.</p>
<script>
    function register() {
        $.ajax({
            url: "backend/ajax_requests.php?action=register",
            success: function(data) {
                handleError(data, function() {
                    showModal({
                        content: '<p style="text-success">Die Benachritigung wurde erfolgreich abgeschickt.</p>',
                        closeLabel: "ok",
                        title: "Erfolgreich",
                        saveFunction: function() {
                            $("#modal-dialog").modal("hide");
                        }
                    });
                });
            }
        });
    }
</script>
