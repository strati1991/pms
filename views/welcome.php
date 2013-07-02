 <?php
session_start();
if(isset($_SESSION['username'])) {
    ?>
    <h1>Hallo, <?= $_SESSION['username'] ?></h1>
    <?php
} else {
 ?>
    <h1>Hallo, klicke bitte auf Login</h1>
    <?php
}
?>