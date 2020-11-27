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
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <div><u><?php safer_echo($result["name"]); ?> </u></div>
        </div>
        <div class="card-body">
            <div>
                <p>Product Information</p>
                <div>Price: $<?php safer_echo($result["price"]); ?></div>
                <div>Units Available: <?php safer_echo($result["quantity"]); ?></div>
                <div>Description: <?php safer_echo($result["description"]); ?></div>
                <div>Category: <?php safer_echo($result["category"]); ?></div>
                <div>Seller ID: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
    </div>
<br><br>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>

    <div>
        <form method="POST">
            <br>
            <label>Quantity</label>
            <br>
            <input name="quantity" type="number" value="1"/>
            <br>
            <button type="submit" name="save" value="Add to Cart">Add</button>
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
<?php require(__DIR__ . "/partials/flash.php");