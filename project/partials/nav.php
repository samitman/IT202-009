<link rel="stylesheet" href="static/css/styles.css">
<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<nav>
<h1 id = "title">Sam's Supps</h1>
<ul class="nav">
    <li><a href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li><a href="<?php echo getURL("login.php")?>">Login</a></li>
        <li><a href="<?php echo getURL("register.php")?>">Register</a></li>
    <?php endif; ?>
    <?php if (has_role("Admin")): ?>
            <li><a href= "<?php echo getURL("/test/test_create_product.php")?>">Create Product</a></li>
            <li><a href="<?php echo getURL("test/test_list_product.php")?>">View Products</a></li>
	    <li><a href="<?php echo getURL("test/test_create_cart.php")?>">Create Cart</a></li>
            <li><a href="<?php echo getURL("test/test_list_carts.php")?>">View Carts</a></li>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <li><a href="<?php echo getURL("profile.php")?>">Profile</a></li>
        <li><a href="<?php echo getURL("logout.php")?>">Logout</a></li>
    <?php endif; ?>
</ul>
</nav>
