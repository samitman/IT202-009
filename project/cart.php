<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php

if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

//gets products for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id,name,price,visibility from Products WHERE visibility != 0 LIMIT 10");
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
        flash("Successfully added to cart.");
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>

<?php
//below will display the cart contents for the user to see
$userID = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT c.id,c.product_id,c.quantity,c.price, Product.name as product FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products Product on Product.id = c.product_id where c.user_id = :id");
$r = $stmt->execute([":id" => $userID]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="results">
        <div class="list-group">
            <div>
                <div><b>Cart Contents</b></div>
            </div>
            <div>
                <br>
            </div>
            <?php
            if(empty($results)){safer_echo("Your cart is empty, let's change that.");}
            $cartTotal = 0;
            foreach ($results as $product):?>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($product["product"]); ?></div>
                    </div>
                    <div>
                        <div>Quantity: <?php safer_echo($product["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Price: $<?php safer_echo($product["price"]); ?></div>
                    </div>
                    <div>
                        <div>Subtotal: $<?php safer_echo($product["price"]*$product["quantity"]); $cartTotal+=$product["price"]*$product["quantity"]; ?></div>
                    </div>
                    <div>
                        <a type="button" href="productView.php?id=<?php safer_echo($product["product_id"]); ?>">View</a>
                    </div>
                    <div>
                        <br>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div>
            <div><b>Total Cart Value: $<?php safer_echo($cartTotal); ?></b></div>
        </div>
    </div>

<?php require(__DIR__ . "/partials/flash.php");