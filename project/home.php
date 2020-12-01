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
<img src="<?php getURL("/static/css/protein1.jpg/")?>" width="500" height="500">
<img src="<?php getURL("/static/css/protein2.jpg/")?>" width="500" height="500">
<img src="<?php getURL("/static/css/protein3.jpg/")?>" width="500" height="500">
<?php require(__DIR__."/partials/flash.php"); ?>
