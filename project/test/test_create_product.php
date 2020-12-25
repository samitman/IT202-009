<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
    <br>
	<label>Product Name</label>
    <br>
	<input name="name" placeholder="Name"/>
    <br>
	<label>Quantity</label>
    <br>
	<input type="number" min="0" name="quantity"/>
    <br>
	<label>Price</label>
    <br>
	<input type="number" min="0" step="0.01" name="price"/>
    <br>
    <label>Description</label>
    <br>
	<input type="text" name="description"/>
    <br>
    <label>Category</label>
    <br>
    <input type="text" name="category"/>
    <br>
    <label>Image Link</label>
    <br>
    <input type="text" name="imgLink"/>
    <br>
    <label for="vis">Visibility:</label>
    <br>
        <select name="vis" id="vis">
            <option value="1">True</option>
            <option value="0">False</option>
        </select>
    <br><br>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$quantity = $_POST["quantity"];
	$price = $_POST["price"];
	$description = $_POST["description"];
	$category = $_POST["category"];
	$imgLink = $_POST["imgLink"];
	$visibility = $_POST["vis"];
	$created = date('Y-m-d H:i:s'); //calc
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, created, user_id, category, visibility, img) VALUES(:name, :quantity, :price, :description, :created, :user, :cat, :vis, :img)");
	$r = $stmt->execute([
		":name"=>$name,
		":quantity"=>$quantity,
		":price"=>$price,
		":description"=>$description,
		":created"=>$created,
		":user"=>$user,
        ":cat"=>$category,
        ":vis"=>$visibility,
        ":img"=>$imgLink
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/../partials/flash.php");?>
