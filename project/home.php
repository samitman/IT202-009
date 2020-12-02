<?php require_once(__DIR__."/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
    echo "Welcome,".$email;
} elseif (!is_logged_in()) {
            flash("Welcome, please login!");
        }


?>
<br><br><br>
<img src="http://34.121.103.64/~si237/repo/project/static/css/protein1.jpg" width="700" height="500">
<img src="http://34.121.103.64/~si237/repo/project/static/css/protein2.jpg" width="700" height="500">
<img src="http://34.121.103.64/~si237/repo/project/static/css/protein3.jpg" width="700" height="400">
<br>
<?php require(__DIR__."/partials/flash.php"); ?>
