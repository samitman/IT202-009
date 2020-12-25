<?php require_once(__DIR__ . "/../partials/nav.php"); ?>
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
	$category = $_POST["category"];
	$visibility = $_POST["vis"];
	$imgLink = $_POST["imgLink"];
	$modified = date('Y-m-d H:i:s'); //calc
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE Products set name=:name, quantity=:quantity, price=:price, description=:description, category=:category, modified=:modified, visibility=:visibility, img=:img where id=:id");
		//$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, created, user_id) VALUES(:name, :quantity, :price, :description,:created,:user)")
		$r = $stmt->execute([
			":name"=>$name,
           	        ":quantity"=>$quantity,
                        ":price"=>$price,
                        ":description"=>$description,
                        ":category"=>$category,
                        ":modified"=>$modified,
                        "visibility"=>$visibility,
                        "img"=>$imgLink,
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
    <br>
	<label>Product Name</label>
    <br>
	<input name="name" placeholder="Name" value="<?php echo $result["name"];?>"/>
    <br>
	<label>Quantity</label>
    <br>
	<input type="number" min="0" name="quantity" value="<?php echo $result["quantity"];?>" />
    <br>
	<label>Price</label>
    <br>
	<input type="number" min="0" step="0.01" name="price" value="<?php echo $result["price"];?>" />
    <br>
	<label>Description</label>
    <br>
	<input type="text" name="description" value="<?php echo $result["description"];?>" />
    <br>
    <label>Category</label>
    <br>
    <input type="text" name="category" value="<?php echo $result["category"];?>" />
    <br>
    <label>Image Link</label>
    <br>
    <input type="text" name="imgLink" value="<?php echo $result["img"];?>" />
    <br>
    <label for="vis">Visibility:</label>
    <br>
    <select name="vis" id="vis">
        <option value="1">True</option>
        <option value="0">False</option>
    </select>
    <br><br>
    <button type="submit" name="save" value="Update">Update</button>
</form>


<?php require(__DIR__ . "/../partials/flash.php");?>
