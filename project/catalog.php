<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<p>Product Catalog</p>

<?php
    $db = getDB();
    $stmt = $db->prepare("SELECT id,name,price,quantity,user_id, Users.username FROM Products JOIN Users on Products.user_id = Users.id");
    $r = $stmt->execute([]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
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
                        <div><?php safer_echo($product["username"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test_edit_product.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_product.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
</div>
