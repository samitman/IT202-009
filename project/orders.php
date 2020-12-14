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
if(isset($_POST["submit"])){
    if(isset($_POST["cat"])){
        $filter = "category";
        $cat = $_POST["cat"];
    }elseif(isset($_POST["date1"]) && isset($_POST["date2"])){
        $filter = "date";
        $date1 = $_POST["date1"];
        $date2 = $_POST["date2"];
    }
}elseif(isset($_GET["filter"])) {
    $filter = $_GET["filter"];
}elseif(isset($_GET["cat"])) {
    $cat = $_GET["cat"];
}elseif(isset($_GET["date1"]) && isset($_GET["date2"])) {
    $date1 = $_GET["date1"];
    $date2 = $_GET["date2"];
}
?>

<?php
$page = 1;
$per_page = 5;
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$db = getDB();
if(!has_role("Admin")) {
    $stmt = $db->prepare("SELECT count(*) as total from Orders where user_id=:id");
    $stmt->execute([":id"=>get_user_id()]);
    $orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
}elseif(has_role("Admin")){
    if(empty($filter)) {
        $stmt = $db->prepare("SELECT count(*) as total from Orders");
        $stmt->execute();
        $orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
    }
        if($filter=="category" && !empty($cat)){
            $stmt = $db->prepare("SELECT count(*) as total from OrderItems JOIN Products on product_id=Products.id where Products.category=:cat");
            $stmt->execute([":cat"=>$cat]);
            $orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
        }elseif($filter=="date" && !empty($date1) && !empty($date2)){
            $stmt = $db->prepare("SELECT count(*) as total from Orders WHERE created BETWEEN :date1 and :date2");
            $stmt->execute([":date1"=>$date1,":date2"=>$date2]);
            $orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
        }
}



$total = 0;
if($orderResult){
    $total = (int)$orderResult["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;
//below will display orders
if(!has_role("Admin")){
    $userID = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT total_price,created,address FROM Orders where user_id=:id ORDER by created DESC LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":id", $userID);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}elseif(has_role("Admin")){
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Orders ORDER by created DESC LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $adminOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php if(has_role("Admin")):?>
    <form method="POST">
        <h3>Filter Orders</h3>
        <br>
        <label for="cat">Category:</label>
        <br>
        <select name="cat" id="cat">
            <option value="" disabled selected>Select a Category</option>
            <option value="Health">Health</option>
            <option value="Protein">Protein</option>
            <option value="Recovery">Recovery</option>
            <option value="Stimulant">Stimulant</option>
        </select>
        <br>
        <label>Date Range: (Y-M-D H:Min:S)</label>
        <br>
        <label>Date 1: </label>
        <br>
        <input type="text" name="date1"/>
        <br>
        <label>Date 2: </label>
        <br>
        <input type="text" name="date2"/>
        <br>
        <button type="submit" value="submit" name="submit">Submit</button>
        <br>
    </form>
<?php endif;?>

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
        $revenue = 0;
        foreach ($adminOrders as $order):?>
            <div class="list-group-item">
                <div>
                    <div>Order ID: <?php safer_echo($order["id"]); ?></div>
                </div>
                <div>
                    <div>User ID: <?php safer_echo($order["user_id"]); ?><?php echo " ";?><a type="button" href="profile.php?id=<?php safer_echo($order["user_id"]); ?>">View Profile</a></div>
                </div>
                <div>
                    <div>Order Date: <?php safer_echo($order["created"]); ?></div>
                </div>
                <div>
                    <div>Address: <?php safer_echo($order["address"]); ?></div>
                </div>
                <div>
                    <div>Payment Method: <?php safer_echo($order["payment_method"]); ?></div>
                </div>
                <div>
                    <div>Subtotal: $<?php safer_echo($order["total_price"]); $revenue+=$order["total_price"];?></div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
        <div><b>Total Revenue: $<?php safer_echo($revenue);?></b></div>
    <?php endif; ?>
    <div>
        <div><br></div>
    </div>
</div>
    <div>
        <nav aria-label="Pages">
            <ul class="pagination">
                <?php if(!(($page-1)<1)):?>
                    <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page-1;?><?php if(!empty($filter)):?>&filter=<?php echo $filter;?><?php endif;?><?php if(!empty($cat)):?>&cat=<?php echo $cat;?><?php endif;?><?php if(!empty($date1)):?>&date1=<?php echo $date1;?><?php endif;?><?php if(!empty($date2)):?>&date2=<?php echo $date2;?><?php endif;?>" tabindex="-1">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                    <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?><?php if(!empty($filter)):?>&filter=<?php echo $filter;?><?php endif;?><?php if(!empty($cat)):?>&cat=<?php echo $cat;?><?php endif;?><?php if(!empty($date1)):?>&date1=<?php echo $date1;?><?php endif;?><?php if(!empty($date2)):?>&date2=<?php echo $date2;?><?php endif;?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <?php if($page<$total_pages):?>
                    <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page+1;?><?php if(!empty($filter)):?>&filter=<?php echo $filter;?><?php endif;?><?php if(!empty($cat)):?>&cat=<?php echo $cat;?><?php endif;?><?php if(!empty($date1)):?>&date1=<?php echo $date1;?><?php endif;?><?php if(!empty($date2)):?>&date2=<?php echo $date2;?><?php endif;?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");