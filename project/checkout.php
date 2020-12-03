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

<?php
//below will display the items being ordered, placed at the top so we can access cart variables
$userID = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT c.id,c.product_id,c.quantity,c.price, Product.name as product FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products Product on Product.id = c.product_id where c.user_id = :id ORDER by product");
$r = $stmt->execute([":id" => $userID]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="results">
        <div class="list-group">
            <div>
                <div><h3>1. Review Items</h3></div>
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
<?php
//before submitting any orders, we must validate that the shipping information is correct
//zip is taken care of by html, need to make sure street address has a house number and at least two words
if(isset($_POST["submit"])) {
    $adr = null;
    $payment = null;
    $price = $total;
    $created = date('Y-m-d H:i:s');
    $id = $user_id;

    if(isset($_POST["payment"])){
        $payment = $_POST["payment"];
        if($payment==-1){
            flash("Please select a valid payment method.");
        }
    }
    //validating street address
    $streetAdr = $_POST["adr"];
    $words = explode(" ", $streetAdr);
    if (gettype($words[0] == "integer") && (sizeof($words) >= 3) && (is_string($_POST["city"])) && (is_string($_POST["state"]))) {
        $adr = $_POST["adr"] . ", " . $_POST["city"] . ", " .$_POST["state"]."  ".$_POST["zip"];
    } else {
        flash("Please enter a valid address.");
    }
    //before letting any order go through, we must validate that the desired product and quantity are available
    $db = getDB();
    $stmt = $db->prepare("SELECT Cart.product_id,Cart.quantity,Products.name,Products.quantity as inventory FROM Cart Join Products on Cart.product_id = Products.id JOIN Users on Cart.user_id = Users.id where Cart.user_id=:id");
    $r = $stmt->execute([":id" => $id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $validOrder = true;
    foreach($items as $item):
        if($item["quantity"]>$item["inventory"]){
            flash("Sorry, there are only ".$item["inventory"]." ".$item["name"]." left in stock, please update your cart.");
            $validOrder = false;
        }elseif($item["inventory"]==0){
            flash("Sorry, ".$item["name"]." is out of stock.");
            $validOrder = false;
        }
    endforeach;

    //only if the all validation is complete we will let the order go through to the orders table
    if ($adr && ($payment!="-1") && $validOrder) {
        //this line is for testing purposes
        echo "Address: ".$adr." Payment: ".$payment." Total: $".$price." User: ".$id." Created: ".$created;

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Orders (user_id,total_price,created,address,payment_method) VALUES(:user,:price,:cr,:adr,:pay)");
        $r = $stmt->execute([
            ":user"=>$id,
            ":price"=>$price,
            ":cr"=>$created,
            ":adr"=>$adr,
            ":pay"=>$payment
        ]);
        if($r){
            flash("Thank you for your order!");
        }
        else{
            $e = $stmt->errorInfo();
            flash("Error placing order: " . var_export($e, true));
        }
        //TODO get order id and copy cart items to order items table, redirect to confirmation page
        //gets the most recent order id
        $db = getDB();
        $stmt = $db->prepare("SELECT id from Orders where user_id = :id ORDER by created DESC LIMIT 1");
        $r = $stmt->execute([":id"=>$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        var_export($order);
        //$order_id = $order["id"];

    }
}

?>

<!--- Form to receive shipping and payment info--->
<form method="POST">
    <h3>2. Shipping Address</h3>

    <br>
    <label>Street Address:</label>
    <br>
    <input name="adr" type="text" required/>
    <br>
    <label>City:</label>
    <br>
    <input name="city" type="text" required/>
    <br>
    <label>State:</label>
    <br>
    <input name="state" type="text" required/>
    <br>
    <label>Zip: (5 Digits)</label>
    <br>
    <input name="zip" type="text" pattern="[0-9]{5}" required/>
    <br>

    <h3>3. Payment Method</h3>

    <br>
    <label>Choose Payment Type:</label>
    <br>
    <select name="payment" required>
        <option value="-1">None</option>
        <option value="cash">Cash</option>
        <option value="amex">Amex</option>
        <option value="discover">Discover</option>
        <option value="masterCard">MasterCard</option>
        <option value="paypal">PayPal</option>
        <option value="visa">Visa</option>
    </select>
    <br><br>
    <button type="submit" name="submit" value="Submit">Place Order</button>
</form>
<?php require(__DIR__ . "/partials/flash.php");



