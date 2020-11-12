<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
	<label>Product Name</label>
	<input name="name" placeholder="Name"/>
	<label>Quantity</label>
	<input type="number" min="0" name="quantity"/>
	<label>Price</label>
	<input type="number" min="0" step="0.01" name="price"/>
	<label>Description</label>
	<input type="text" name="description"/>
    <label for="vis">Visibility:</label>
        <select name="vis" id="vis">
            <option value=1>True</option>
            <option value=0>False</option>
        </select>
    <br>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$quantity = $_POST["quantity"];
	$price = $_POST["price"];
	$description = $_POST["description"];
	$vis = $_POST["vis"];
	$created = date('Y-m-d H:i:s'); //calc
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, vis, created, user_id) VALUES(:name, :quantity, :price, :description, :vis, :created,:user)");
	$r = $stmt->execute([
		":name"=>$name,
		":quantity"=>$quantity,
		":price"=>$price,
		":description"=>$description,
		":vis"=>$vis,
		":created"=>$created,
		":user"=>$user
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
