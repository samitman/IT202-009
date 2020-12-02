<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//only let's users access their cart if logged in
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

//updating item quantity
if (isset($_POST["update"]) && isset($_POST["quantity"])) {
    $productID = $_POST["update"];
    $newQuantity = $_POST["quantity"];
    $userID = get_user_id();
    $db = getDB();

    //remove if quantity set to 0
    if ($newQuantity == 0) {
        $stmt = $db->prepare("DELETE FROM Cart where user_id=:id AND product_id=:pid");
        $r = $stmt->execute([":pid" => $productID, ":id" => $userID]);
        if ($r) {
            flash("Removed item from cart");
        }
    } else { //updates quantity
        $stmt = $db->prepare("UPDATE Cart SET quantity=:quantity WHERE user_id=:id AND product_id=:pid");
        $r = $stmt->execute([":quantity" => $newQuantity, ":id" => $userID, ":pid" => $productID]);
        if ($r) {
            flash("Updated quantity");
        }
    }
}

//removes product from cart
if (isset($_POST["remove"])) {
    $productID = $_POST["remove"];
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM Cart where user_id=:id AND product_id=:pid");
    $r = $stmt->execute([":pid" => $productID, ":id" => $userID]);
    if ($r) {
        flash("Removed item from cart");
    }
}

//clears entire cart
if (isset($_POST["clear"])) {
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM Cart where user_id=:id");
    $r = $stmt->execute([":id" => $userID]);
    if ($r) {
        flash("Cart emptied");
    }
}


//gets products for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id,name,price,visibility from Products WHERE visibility != 0 LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <h3>Add to Cart</h3>
    <form method="POST">
        <br>
        <label>Select a Product</label>
        <br>
        <select name="product_id">
            <option value="-1">None</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php safer_echo($product["id"]); ?>"> <?php safer_echo($product["name"]." $".$product["price"]);?> </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label>Quantity</label>
        <br>
        <input name="quantity" type="number" value="1"/>
        <br>
        <button type="submit" name="save" value="Submit">Submit</button>
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
$stmt = $db->prepare("SELECT c.id,c.product_id,c.quantity,c.price, Product.name as product FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products Product on Product.id = c.product_id where c.user_id = :id ORDER by product");
$r = $stmt->execute([":id" => $userID]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="results">
        <div class="list-group">
            <div>
                <div><h3>Cart Contents</h3></div>
            </div>
            <div>
                <br>
            </div>
            <?php
            if(empty($results)){safer_echo("Your cart is empty, let's change that.");echo("<br>");}
            $cartTotal = 0;
            foreach ($results as $product):?>
                <div class="list-group-item">
                    <div>
                        <div><?php safer_echo($product["product"]); ?></div>
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
                        <br><br>
                        <form method="POST">
                            <br>
                            <label>Change Quantity</label>
                            <br>
                            <input name="quantity" type="number"/>
                            <br>
                            <button type="submit" value="<?php safer_echo($product["product_id"]);?>" name="update">Update</button>
                        </form>
                    </div>
                    <div>
                        <form method="POST">
                            <button type="submit" value="<?php safer_echo($product["product_id"]);?>" name="remove">Remove</button>
                        </form>
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
        <div>
            <div><br></div>
        </div>
    </div>

    <form method="POST">
        <button type="submit" name="clear">Clear</button>
    </form>

    <br>
    <a type="button" href="checkout.php">Proceed to Checkout</a>
<?php require(__DIR__ . "/partials/flash.php");