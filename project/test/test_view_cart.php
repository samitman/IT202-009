<?php require_once(__DIR__ . "../partials/nav.php"); ?>
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
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT c.id,c.product_id,c.quantity,c.price, Users.username, Product.name as product FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products Product on Product.id = c.product_id where c.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
    <h3>View Cart</h3>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
	    <div>Username: <?php safer_echo($result["username"]); ?></div>
        </div>
        <div class="card-body">
	<p>Cart Information</p>
	   <div>
		<div> Cart ID: <?php safer_echo($result["id"]); ?></div>
   	   </div>

	   <div>
		<div>Product: <?php safer_echo($result["product"]); ?></div>
	   </div>

	   <div>
		<div>Quantity: <?php safer_echo($result["quantity"]); ?></div>
	   </div>

	   <div>
		<div>Price: $<?php safer_echo($result["price"]); ?></div>
	   </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "../partials/flash.php");
