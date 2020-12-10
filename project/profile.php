<?php require_once(__DIR__."/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$profileID = null;
if(isset($_GET["id"])){
    $profileID = $_GET["id"];
}

$db = getDB();
//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    //check if our email changed
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Username already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        $stmt = $db->prepare("UPDATE Users set email = :email, username= :username where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":id" => get_user_id()]);
        if ($r) {
            flash("Updated profile");
        }
        else {
            flash("Error updating profile");
        }
        //password is optional, so check if it's even set
        //if so, then check if it's a valid reset request
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if ($_POST["password"] == $_POST["confirm"]) {
                $password = $_POST["password"];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                //this one we'll do separate
                $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                if ($r) {
                    flash("Reset Password");
                }
                else {
                    flash("Error resetting password");
                }
            }
        }
        //for account type
        $userID = get_user_id();
        $stmt = $db->prepare("SELECT account_type from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id"=>$userID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($_POST["account_type"]!=$result["account_type"]){
            $type = $_POST["account_type"];
            $stmt = $db->prepare("UPDATE Users set account_type= :account_type WHERE id = :id");
            $r = $stmt->execute([":account_type"=>$type,":id"=>$userID]);
            if(!$r){
                flash("Error changing account type");
            }
        }

//fetch/select fresh data in case anything changed
        $stmt = $db->prepare("SELECT email, username from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
}


?>

<?php if($profileID == get_user_id()): ?>
    <form method="POST">
        <br>
        <label for="email">Email</label>
        <br>
        <input type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
        <br>
        <label for="username">Username</label>
        <br>
        <input type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
        <br>
        <!-- DO NOT PRELOAD PASSWORD-->
        <label for="pw">Password</label>
        <br>
        <input type="password" name="password"/>
        <br>
        <label for="cpw">Confirm Password</label>
        <br>
        <input type="password" name="confirm"/>
        <br>
        <label for="type">Change Account Type:</label>
        <br>
        <select name="account_type" id="type">
            <option value="public">Public</option>
            <option value="private">Private</option>
        </select>
        <br><br>
        <button type="submit" name="saved" value="Save Profile">Update</button>
    </form>
<?php endif;?>
<?php
if($profileID != get_user_id()){
    $stmt = $db->prepare("SELECT id,username,email,created,account_type from Users where id=:profileID LIMIT 1");
    $stmt->execute([":profileID" => $profileID]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}
if(!empty($profile)){
    if(!has_role("Admin")&&$profile["account_type"]=="private"){
        flash("Private account, access denied.");
        die(header("Location: home.php"));
    }
}
?>
<?php if(($profileID != get_user_id()) && !empty($profile) && $profile["account_type"] == "public"):?>
<div>
    <div><h3>Welcome to <?php safer_echo($profile["username"]);?>'s profile page!</h3></div>
    <div>Username: <?php safer_echo($profile["username"]);?></div>
    <div>User ID: <?php safer_echo($profile["id"]);?></div>
    <div>Account Type: <?php safer_echo($profile["account_type"]);?></div>
    <div>Created: <?php safer_echo($profile["created"]);?></div>
</div>
<?php endif;?>
<?php require(__DIR__."/partials/flash.php"); ?>
