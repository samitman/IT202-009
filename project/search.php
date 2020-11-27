<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}

if (isset($_POST["search"]) && !empty($query) && isset($_POST["filter"])) {

        $filter = $_POST["filter"];
        $safeFilter = "name";
        switch($filter){
            case "category":
                $safeFilter = "category";
                break;
            case "price":
                $safeFilter = "price";
                break;
            default:
                break;
        }

        if($safeFilter == "category" || $safeFilter == "name") {
            if (!has_role("Admin")) {
                $db = getDB();
                $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility,category, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE $safeFilter LIKE :q AND Products.visibility!=0 ORDER BY name LIMIT 10");
                $r = $stmt->execute([":q" => "%$query%"]);
                if ($r) {
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    flash("There was a problem fetching the results");
                }
            } elseif (has_role("Admin")) {
                $db = getDB();
                $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility,category, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE $safeFilter LIKE :q ORDER BY name LIMIT 10");
                $r = $stmt->execute([":q" => "%$query%"]);
                if ($r) {
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    flash("There was a problem fetching the results");
                }
            }
        }elseif($safeFilter == "price") {
            if (!has_role("Admin")) {
                $db = getDB();
                $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility,category, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE name LIKE :q AND Products.visibility!=0 ORDER BY price LIMIT 10");
                $r = $stmt->execute([":q" => "%$query%"]);
                if ($r) {
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    flash("There was a problem fetching the results");
                }
            } elseif (has_role("Admin")) {
                $db = getDB();
                $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility,category, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE name LIKE :q ORDER BY price LIMIT 10");
                $r = $stmt->execute([":q" => "%$query%"]);
                if ($r) {
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    flash("There was a problem fetching the results");
                }
            }
        }
}
?>

<form method="POST">
    <br>
    <label for="query">Search Products:</label>
    <br>
    <input name="query" id="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <br>
    <label for="filter">Filter:</label>
    <br>
    <select name="filter" id="filter">
        <option value="name">Name</option>
        <option value="category">Category</option>
        <option value="price">Price</option>
    </select>
    <br>
    <button type="submit" value="Search" name="search">Search</button>
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
                        <div>Price: $<?php safer_echo($r["price"]); ?></div>
                    </div>
                    <div>
                        <div>Category: <?php safer_echo($r["category"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="productView.php?id=<?php safer_echo($r['id']); ?>">View</a>
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

