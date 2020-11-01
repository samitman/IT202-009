<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$quantity = $_POST["quantity"];
	$price = $_POST["price"];
	$description = $_POST["description"];
	$modified = date('Y-m-d H:i:s'); //calc
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Products set name=:name, quantity=:quantity, price=:price, description=:description, modified=:modified where id=:id");
		//$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, created, user_id) VALUES(:name, :quantity, :price, :description,:created,:user)")
		$r = $stmt->execute([
			":name"=>$name,
           	        ":quantity"=>$quantity,
                        ":price"=>$price,
                        ":description"=>$description,
                        ":modified"=>$modified,
                        ":id"=>$id
		]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
	}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Products where id=:id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Product Name</label>
	<input name="name" placeholder="Name" value="<?php echo $result["name"];?>"/>
	<label>Quantity</label>
	<input type="number" min="0" name="quantity" value="<?php echo $result["quantity"];?>" />
	<label>Price</label>
	<input type="number" min="0" step="0.01" name="price" value="<?php echo $result["price"];?>" />
	<label>Description</label>
	<input type="text" name="description" value="<?php echo $result["description"];?>" />
	<input type="submit" name="save" value="Update"/>
</form>


<?php require(__DIR__ . "/partials/flash.php");?>
