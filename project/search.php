<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
$query = "";
$results = [];
if (isset($_POST["search"])){
    if (isset($_POST["query"])) {
        $query = $_POST["query"];
    }
}elseif(isset($_GET["query"])){
    $query = $_GET["query"];
}

if (isset($_POST["search"]) && !empty($query) && (isset($_POST["filter"]) || isset($_POST["quantFilter"]))) {

    $safeFilter = "name";
    if(isset($_POST["filter"])) {
        $filter = $_POST["filter"];
        switch ($filter) {
            case "category":
                $safeFilter = "category";
                break;
            case "price":
                $safeFilter = "price";
                break;
            case "rating":
                $safeFilter = "rating";
                break;
            default:
                break;
        }
    }
    if(isset($_POST["quantFilter"])){
        $safeFilter = "quantity";
        $quantFilter = $_POST["quantFilter"];
    }
}elseif(isset($_GET["filter"])) {
    $safeFilter = $_GET["filter"];
}
if(isset($_GET["quantity"])){
    $quantFilter = $_GET["quantity"];
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

if(!empty($safeFilter)) {
    $db = getDB();
    if ($safeFilter == "category" || $safeFilter == "name") {
        $stmt = $db->prepare("SELECT count(*) as total from Products WHERE $safeFilter LIKE :q");
        $stmt->execute([":q" => "%$query%"]);
    } elseif ($safeFilter == "price") {
        $stmt = $db->prepare("SELECT count(*) as total from Products WHERE name LIKE :q");
        $stmt->execute([":q" => "%$query%"]);
    } elseif ($safeFilter == "quantity") {
        $stmt = $db->prepare("SELECT count(*) as total from Products WHERE name LIKE :q AND quantity<=:quant");
        $stmt->execute([":q" => "%$query%",":quant"=>$quantFilter]);
    } elseif ($safeFilter == "rating") {
        $stmt = $db->prepare("SELECT count(DISTINCT product_id) as total from Ratings JOIN Products on Products.id = Ratings.product_id WHERE name LIKE :q AND Products.id=Ratings.product_id");
        $stmt->execute([":q" => "%$query%"]);
    }
    $productResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = 0;
    if ($productResult) {
        $total = (int)$productResult["total"];
    }
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * $per_page;
}

if (!empty($query) && !empty($safeFilter)) {
    if ($safeFilter == "category" || $safeFilter == "name") {
        if (!has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,name,quantity,price,visibility,category FROM Products JOIN Users on Products.user_id = Users.id WHERE $safeFilter LIKE :q AND Products.visibility!=0 AND Products.quantity > 0 ORDER BY name LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                flash("There was a problem fetching the results");
            }
        } elseif (has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,name,quantity,price,visibility,category FROM Products JOIN Users on Products.user_id = Users.id WHERE $safeFilter LIKE :q ORDER BY name LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                flash("There was a problem fetching the results");
            }
        }
    } elseif ($safeFilter == "price") {
        if (!has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,name,quantity,price,visibility,category FROM Products JOIN Users on Products.user_id = Users.id WHERE name LIKE :q AND Products.visibility!=0 AND Products.quantity > 0 ORDER BY price LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                flash("There was a problem fetching the results");
            }
        } elseif (has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,name,quantity,price,visibility,category FROM Products JOIN Users on Products.user_id = Users.id WHERE name LIKE :q ORDER BY price LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                flash("There was a problem fetching the results");
            }
        }
    } elseif ($safeFilter == "rating") {
        if (!has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,Products.name,Products.quantity,Products.price,Products.visibility,Products.category,AVG(rating) as rating FROM Products JOIN Ratings on Products.id = Ratings.product_id WHERE name LIKE :q AND Products.visibility!=0 AND Products.quantity > 0 GROUP BY Products.id ORDER BY rating DESC LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                flash("There was a problem fetching the results");
            }
        } elseif (has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,Products.name,Products.quantity,Products.price,Products.visibility,Products.category,AVG(rating) as rating FROM Products JOIN Ratings on Products.id = Ratings.product_id WHERE name LIKE :q GROUP BY Products.id ORDER BY rating DESC LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                flash("There was a problem fetching the results");
            }
        }
    } elseif ($safeFilter == "quantity") {
        if (has_role("Admin")) {
            $db = getDB();
            $stmt = $db->prepare("SELECT Products.id,name,quantity,price,visibility,category FROM Products JOIN Users on Products.user_id = Users.id WHERE name LIKE :q AND quantity<=:quant ORDER BY quantity LIMIT :offset, :count");
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":quant", $quantFilter, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
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
        <option value="" disabled selected>Choose a Filter</option>
        <option value="name">Name</option>
        <option value="category">Category</option>
        <option value="price">Price</option>
        <option value="rating">Rating</option>
    </select>
    <br>
    <?php if(has_role("Admin")): ?>
    <label for="quantFilter">Filter By Quantity:</label>
    <br>
    <input name="quantFilter" type="number" value="<?php if(!empty($quantFilter)){safer_echo($quantFilter);}?>"/>
    <br>
    <?php endif; ?>
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
                        <div>Units Available: <?php safer_echo($r["quantity"]); ?></div>
                    </div>
                    <?php if($r["rating"]):
                        $rate = $r["rating"];
                        $displayRate = substr($rate,0,4);
                        ?>
                    <div>
                        <div>Rating: <?php safer_echo($displayRate); ?></div>
                    </div>
                    <?php endif;?>
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
                <?php if(!(($page-1)<1)&&!empty($safeFilter)):?>
                    <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page-1;?>&query=<?php echo $query;?>&filter=<?php echo $safeFilter;?><?php if(!empty($quantFilter)):?>&quantity=<?php echo $quantFilter;?><?php endif;?>" tabindex="-1">Previous</a>
                    </li>
                <?php endif; ?>
                <?php if(!empty($safeFilter)):?>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                    <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>&query=<?php echo $query;?>&filter=<?php echo $safeFilter;?><?php if(!empty($quantFilter)):?>&quantity=<?php echo $quantFilter;?><?php endif;?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <?php endif; ?>

                <?php if(!empty($safeFilter)):?>
                <?php if(($page<$total_pages)):?>
                    <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page+1;?>&query=<?php echo $query;?>&filter=<?php echo $safeFilter;?><?php if(!empty($quantFilter)):?>&quantity=<?php echo $quantFilter;?><?php endif;?>">Next</a>
                    </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

<?php require(__DIR__ . "/partials/flash.php"); ?>

