<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<h2>Product Catalog</h2>

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
$stmt = $db->prepare("SELECT count(*) as total from Products");
$stmt->execute([]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

if (!has_role("Admin")) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE Products.visibility != 0 AND Products.quantity > 0 ORDER BY name LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $r = $stmt->execute([]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (has_role("Admin")) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility, Users.username FROM Products JOIN Users on Products.user_id = Users.id ORDER BY name");
    $r = $stmt->execute([]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="results">
        <div class="list-group">
            <?php foreach ($results as $product): ?>
                <div class="list-group-item">
                    <div>
                        <div><h3><u><?php safer_echo($product["name"]); ?></u></h3></div>
                    </div>
                    <div>
                        <div>Price: $<?php safer_echo($product["price"]); ?></div>
                    </div>
                    <div>
                        <div>Units Available: <?php safer_echo($product["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Seller: <?php safer_echo($product["username"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="productView.php?id=<?php safer_echo($product['id']); ?>">View</a>
                    </div>
                    <div>
                        <br>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
</div>
    <div>
    <nav aria-label="Pages">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
            </li>
            <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
            </li>
        </ul>
    </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");