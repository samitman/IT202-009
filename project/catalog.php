<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<p>Product Catalog</p>

<?php
    $db = getDB();
    $stmt = $db->prepare("SELECT id,name,price,quantity,user_id FROM Products");
    $r = $stmt->execute([]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="results">
        <div class="list-group">
            <?php foreach ($results as $product): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name:</div>
                        <div><?php safer_echo($product["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price:</div>
                        <div><?php safer_echo($product["price"]); ?></div>
                    </div>
                    <div>
                        <div>Units Available:</div>
                        <div><?php safer_echo($product["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Seller ID:</div>
                        <div><?php safer_echo($product["user_id"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test/test_view_product.php?id=<?php safer_echo($product['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
</div>
