<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,description,category,user_id, Users.username FROM Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php
//check to see if the user purchased the product to allow them to rate it
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT Orders.id,OrderItems.product_id FROM Orders JOIN OrderItems where Orders.user_id = :id AND OrderItems.order_id = Orders.id");
    $r = $stmt->execute([":id"=>$userID]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $didOrder = false;
    foreach($orderItems as $item):
        if($item["product_id"]==$_GET["id"]){
            $didOrder = true;
        }
    endforeach;
//TODO output all product ratings on this page
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <br>
            <div><h1><u><?php safer_echo($result["name"]); ?> </u></h1></div>
        </div>
        <div class="card-body">
            <div>
                <p>Product Information:</p>
                <div>Price: $<?php safer_echo($result["price"]); ?></div>
                <div>Units Available: <?php safer_echo($result["quantity"]); ?></div>
                <div>Category: <?php safer_echo($result["category"]); ?></div>
                <div>Seller ID: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
        <br>
        <div>Description: <?php safer_echo($result["description"]); ?></div>
    </div>
<br><br>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<h3>Add to Cart</h3>
    <div>
        <form method="POST">
            <br>
            <label>Quantity:</label>
            <br>
            <input name="quantity" type="number" value="1"/>
            <br>
            <button id="atc" type="submit" name="save" value="Add to Cart">Add to Cart</button>
        </form>
    </div>
<?php
if (isset($_POST["save"])) {
    $id = $_GET["id"];
    $quantity = $_POST["quantity"];
    $price = $result["price"];
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
if(isset($_POST["rate"])){
    $rate = $_POST["rating"];
    $comment = $_POST["comment"];
    $userID = get_user_id();
    $pid = $_GET["id"];
    $created = date('Y-m-d H:i:s');

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Ratings(product_id,user_id,rating,comment,created) VALUES(:pid,:user,:rate,:comment,:created)");
    $r = $stmt->execute([":pid"=>$pid,":user"=>$userID,":rate"=>$rate,":comment"=>$comment,":created"=>$created]);

    if($r) {
        flash("Thank you for your rating!");
    }else{
        flash("Error creating rating.");
    }
}
?>

<?php if($didOrder):?>
<h3>Rate This Item:</h3>
    <div>
        <form method="POST">
            <br>
            <label>Rating (1-5):</label>
            <br>
            <select name="rating" required>
                <option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
            </select>
            <br>
            <label>Comment:</label>
            <br>
            <input type="text" name="comment" required/>
            <br>
            <button type="submit" name="rate" value="Rate Product">Submit</button>
        </form>
    </div>
<?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php");