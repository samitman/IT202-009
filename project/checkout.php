<?php require_once(__DIR__ . "/partials/nav.php");
//this is the checkout page, accessed through a link in the cart
//Will have form for payment method, shipping, and show the products being bought

//only let's users access if logged in
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
$user_id = get_user_id();
?>

<form method="POST">
    <h3>Shipping Address</h3>

    <br>
    <label>Street Address:</label>
    <br>
    <input name="adr" type="text" required/>
    <br>
    <label>City:</label>
    <br>
    <input name="city" type="text" required/>
    <br>
    <label>Zip:</label>
    <br>
    <input name="zip" type="text" pattern="[0-9]{5}"/>
    <br>

    <h3>Payment Method</h3>

    <br>
    <label>Choose Payment Type</label>
    <br>
    <select name="payment">
        <option value="cash">Cash</option>
        <option value="amex">Amex</option>
        <option value="discover">Discover</option>
        <option value="masterCard">MasterCard</option>
        <option value="paypal">PayPal</option>
        <option value="visa">Visa</option>
    </select>
    <br>
    <button type="submit" name="submit" value="Submit">Submit</button>
</form>

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
            <div><h3>Review Items</h3></div>
        </div>
        <div>
            <br>
        </div>
        <?php
        $total = 0;
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
                    <div>Subtotal: $<?php safer_echo($product["price"]*$product["quantity"]); $total+=$product["price"]*$product["quantity"]; ?></div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div>
        <div><b>Order Total: $<?php safer_echo($total); ?></b></div>
    </div>
    <div>
        <div><br></div>
    </div>
</div>
<form method="POST">
    <button type="submit" name="placeOrder">Place Order</button>
</form>

