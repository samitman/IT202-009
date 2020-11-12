<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<p>Search Products</p>

<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    if (!has_role("Admin")) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE name like :q AND Products.visibility!=0 LIMIT 10");
        $r = $stmt->execute([":q" => "%$query%"]);
        if ($r) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            flash("There was a problem fetching the results");
        }
    } elseif (!has_role("Admin")) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE name like :q LIMIT 10");
        $r = $stmt->execute([":q" => "%$query%"]);
        if ($r) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            flash("There was a problem fetching the results");
        }
    }
}
?>
<form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Name: <?php safer_echo($r["name"]); ?></div>
                    </div>
                    <div>
                        <div>Price: <?php safer_echo($r["price"]); ?></div>
                    </div>
                    <div>
                        <div>Units Available: <?php safer_echo($r["quantity"]); ?></div>
                    </div>
                    <div>
                        <div>Seller: <?php safer_echo($r["username"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test/test_view_product.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                    <div>
                        <br>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
