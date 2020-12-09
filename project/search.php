<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
//getting pagination values
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
$productResult = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($productResult){
    $total = (int)$productResult["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

$safeFilter = "name";
if (isset($_POST["search"]) && !empty($query) && isset($_POST["filter"])) {

    $filter = $_POST["filter"];
    $safeFilter = "name";
    switch ($filter) {
        case "category":
            $safeFilter = "category";
            break;
        case "price":
            $safeFilter = "price";
            break;
        default:
            break;
    }
} elseif(isset($_GET["filter"])){
    $safeFilter = $_GET["filter"];
}

        if($safeFilter == "category" || $safeFilter == "name") {
            if (!has_role("Admin")) {
                $db = getDB();
                $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility,category, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE $safeFilter LIKE :q AND Products.visibility!=0 AND Products.quantity > 0 ORDER BY name LIMIT :offset, :count");
                $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
                $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
                $stmt->bindValue(":q", $query);
                $r = $stmt->execute();
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
                $stmt = $db->prepare("SELECT Products.id,name,quantity,price,user_id,visibility,category, Users.username FROM Products JOIN Users on Products.user_id = Users.id WHERE name LIKE :q AND Products.visibility!=0 AND Products.quantity > 0 ORDER BY price LIMIT 10");
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
                        <div><h4><u><?php safer_echo($r["name"]); ?></u></h4></div>
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
    <div>
        <nav aria-label="Pages">
            <ul class="pagination">
                <?php if(!(($page-1)<1)):?>
                    <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page-1;?>&filter=<?php echo $safeFilter;?>" tabindex="-1">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                    <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>&filter=<?php echo $safeFilter;?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <?php if($page<$total_pages):?>
                    <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page+1;?>&filter=<?php echo $safeFilter;?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

<?php require(__DIR__ . "/partials/flash.php");

