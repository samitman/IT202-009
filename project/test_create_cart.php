<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php

//check if user is an admin
if (!has_role("Admin")) {
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}

//gets products for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id,name,price from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Add to Cart</h3>
    <form method="POST">
       <label>Select a Product</label>
        <select name="product_id">
            <option value="-1">None</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php safer_echo($product["id"]); ?>"> <?php safer_echo($product["name"]." $".$product["price"]);?> </option>
                <?php endforeach; ?>
        </select>
        <label>Quantity</label>
        <input name="quantity" type="number"/>
        <input type="submit" name="save" value="Submit"/>
    </form>
        
<?php
if (isset($_POST["save"])) {
	if(isset($_POST["product_id"])){
    		$id = $_POST["product_id"];
    		$db = getDB();
		$stmt = $db->prepare("SELECT id,price from Products WHERE id=:id");
		$r = $stmt->execute([":id" => $id]);
		$productSelection = $stmt->fetch(PDO::FETCH_ASSOC);
   	 }

    $id = $_POST["product_id"];
    $quantity = $_POST["quantity"];
    $price = $productSelection["price"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Cart (product_id, quantity, price, user_id) VALUES(:id, :quantity, :price, :user)");
    $r = $stmt->execute([
        ":id" => $id,
        ":quantity" => $quantity,
        ":price" => $price,
        ":user" => $user
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
    
<?php require(__DIR__ . "/partials/flash.php");
