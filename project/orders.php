<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//only let's users access this page if logged in
//depending on the users role, they will either see their orders or all orders
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>

<?php
//below will display orders for regular users
if(!has_role("Admin")){
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT total_price,created,address FROM Orders where user_id=:id ORDER by created DESC LIMIT 10");
    $r = $stmt->execute([":id" => $userID]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}elseif(has_role("Admin")){
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Orders ORDER by created DESC LIMIT 10");
    $r = $stmt->execute([]);
    $adminOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="results">
    <div class="list-group">
        <div>
            <div><h3>Orders:</h3></div>
        </div>
        <div>
            <br>
        </div>
        <?php
        if(!has_role("Admin")):
        foreach ($orders as $order):?>
            <div class="list-group-item">
                <div>
                    <div>Order placed on: <?php safer_echo($order["created"]); ?></div>
                </div>
                <div>
                    <div>Address: <?php safer_echo($order["address"]); ?></div>
                </div>
                <div>
                    <div>Subtotal: $<?php safer_echo($order["total_price"]); ?></div>
                </div>
                <div>
                    <div>Status: Received</div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    if(has_role("Admin")):
        foreach ($adminOrders as $order):?>
            <div class="list-group-item">
                <div>
                    <div>Order ID: $<?php safer_echo($order["id"]); ?></div>
                </div>
                <div>
                    <div>User ID: $<?php safer_echo($order["user_id"]); ?></div>
                </div>
                <div>
                    <div>Order Date: <?php safer_echo($order["created"]); ?></div>
                </div>
                <div>
                    <div>Address: <?php safer_echo($order["address"]); ?></div>
                </div>
                <div>
                    <div>Payment Method: $<?php safer_echo($order["payment_method"]); ?></div>
                </div>
                <div>
                    <div>Subtotal: $<?php safer_echo($order["total_price"]); ?></div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div>
        <div><br></div>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php");