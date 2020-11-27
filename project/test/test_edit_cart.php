<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}

//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Cart where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}

$db = getDB();
$stmt = $db->prepare("SELECT id,name,price from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Edit Cart</h3>
    <form method="POST">
       <label>Select a Product</label>
        <br>
        <select name="product_id">
            <option value="-1">None</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php safer_echo($product["id"]); ?>"> <?php safer_echo($product["name"]." $".$product["price"]);?> </option>
                <?php endforeach; ?>
        </select>
        <label>Quantity</label>
        <br>
        <input name="quantity" type="number"/>
        <input type="submit" name="save" value="Update"/>
    </form>
<?php
//saving
if (isset($_POST["save"])) {
    if(isset($_POST["product_id"])){
                $product_id = $_POST["product_id"];
                $db = getDB();
                $stmt = $db->prepare("SELECT id,price from Products WHERE id=:id");
                $r = $stmt->execute([":id" => $product_id]);
                $productSelection = $stmt->fetch(PDO::FETCH_ASSOC);
         }
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];
    $price = $productSelection["price"];
    $user = get_user_id();
    $db = getDB();
    if (isset($id)) {
        $stmt = $db->prepare("UPDATE Cart set product_id=:product_id, quantity=:quantity, price=:price where id=:id");
        $r = $stmt->execute([
            ":product_id" => $product_id,
            ":quantity" => $quantity,
            ":price" => $price,
            ":id" => $id
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
        }
}
?>

<?php require(__DIR__ . "/../partials/flash.php");
