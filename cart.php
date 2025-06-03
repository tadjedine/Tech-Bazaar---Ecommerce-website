<?php
session_start();

// Allow both logged-in users and guests with items in cart
$hasCart = isset($_SESSION['logged_in']) || (isset($_SESSION['guest_cart']) && count($_SESSION['guest_cart']) > 0);

// Set nav based on login status
if (!isset($_SESSION['logged_in'])) {
    $nav = 'includes/nav.php';
} else {
    $nav = 'includes/navconnected.php';
    $idsess = $_SESSION['id'];
}

require 'includes/header.php';
require $nav;?>
<div class="container-fluid product-page">
    <div class="container current-page">
        <nav>
            <div class="nav-wrapper">
                <div class="col s12">
                    <a href="index" class="breadcrumb">Home</a>
                    <a href="cart" class="breadcrumb">Cart</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container scroll info">
    <?php if (isset($_GET['error'])): ?>
    <div class="card-panel red lighten-4">
        <span class="red-text text-darken-2">Error: <?= htmlspecialchars($_GET['error']) ?></span>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['removed']) && $_GET['removed'] == 'success'): ?>
    <div class="card-panel green lighten-4">
        <span class="green-text text-darken-2">Item removed from cart successfully.</span>
    </div>
    <?php endif; ?>
    
    <?php
    // For logged in users, get cart from database
    if (isset($_SESSION['logged_in'])) {
        include 'db.php';
        
        // Get cart items
        $queryproduct = "SELECT product.name as 'name',
          product.id as 'id', product.price as 'price',
          category.name as 'category', command.id as 'id_command', command.statut,
          command.quantity as 'quantity'
        FROM category, product, command
        WHERE command.id_product = product.id AND product.id_category = category.id AND command.statut = 'ordered' AND command.id_user = ".$_SESSION['id'];
        
        $result1 = $connection->query($queryproduct);
        
        if ($result1->num_rows > 0) {
            // Cart has items - display them in a table
            ?>
            <table class="highlight">
                <thead>
                <tr>
                    <th data-field="name">Item Name</th>
                    <th data-field="category">Category</th>
                    <th data-field="price">Price</th>
                    <th data-field="quantity">Quantity</th>
                    <th data-field="total">Total</th>
                    <th data-field="action">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $total_price = 0;
                
                // output data of each row
                while($rowproduct = $result1->fetch_assoc()) {
                    $id_productdb = $rowproduct['id'];
                    $name_product = $rowproduct['name'];
                    $category_product = $rowproduct['category'];
                    $quantity_product = $rowproduct['quantity'];
                    $price_product = $rowproduct['price'];
                    $id_command = $rowproduct['id_command'];
                    
                    $subtotal = $price_product * $quantity_product;
                    $total_price += $subtotal;
                    ?>
                    <tr>
                        <td><?= $name_product; ?></td>
                        <td><?= $category_product; ?></td>
                        <td>$<?= $price_product; ?></td>
                        <td><?= $quantity_product; ?></td>
                        <td>$<?= $subtotal; ?></td>
                        <td><a href="deletecommand.php?id=<?= $id_command; ?>" class="waves-effect waves-light" title="Remove item"><i class="material-icons red-text">close</i></a></td>
                    </tr>
                <?php } ?>
                <tr class="green lighten-3">
                    <td colspan="4" class="right-align"><strong>Total:</strong></td>
                    <td colspan="2"><strong>$<?= $total_price; ?></strong></td>
                </tr>
                </tbody>
            </table>
            <div class="right-align">
                <a href="checkout"
                   class='btn-large button-rounded waves-effect waves-light'>
                    Check out <i class="material-icons right">payment</i></a>
            </div>
        <?php 
        } else {
            // Cart is empty - display a message
            ?>
            <div class="card-panel center-align">
                <i class="large material-icons blue-text">shopping_cart</i>
                <h4>Your cart is empty</h4>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="index" class="btn-large button-rounded waves-effect waves-light">Continue Shopping</a>
            </div>
        <?php }
    } 
    
    // For guest users, get cart from session
    else if (isset($_SESSION['guest_cart']) && count($_SESSION['guest_cart']) > 0) { 
        ?>
        <table class="highlight">
            <thead>
            <tr>
                <th data-field="name">Item Name</th>
                <th data-field="price">Price</th>
                <th data-field="quantity">Quantity</th>
                <th data-field="total">Total</th>
                <th data-field="action">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $total_price = 0;
            
            // Output data for guest cart
            foreach ($_SESSION['guest_cart'] as $key => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $total_price += $subtotal;
                ?>
                <tr>
                    <td><?= $item['name']; ?></td>
                    <td>$<?= $item['price']; ?></td>
                    <td><?= $item['quantity']; ?></td>
                    <td>$<?= $subtotal; ?></td>
                    <td>
                        <a href="removefromcart.php?key=<?= $key; ?>" class="waves-effect waves-light" title="Remove item">
                            <i class="material-icons red-text">close</i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            <tr class="green lighten-3">
                <td colspan="3" class="right-align"><strong>Total:</strong></td>
                <td colspan="2"><strong>$<?= $total_price; ?></strong></td>
            </tr>
            </tbody>
        </table>
        <div class="right-align">
            <div class="row">
                <div class="col s6 right-align">
                    <a href="sign" class="btn button-rounded waves-effect waves-light">
                        Log in to check out <i class="material-icons right">account_circle</i>
                    </a>
                </div>
                <div class="col s6 left-align">
                    <a href="guest_checkout" class="btn-large button-rounded waves-effect waves-light">
                        Guest checkout <i class="material-icons right">payment</i>
                    </a>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <!-- Empty cart message for guests -->
        <div class="card-panel center-align">
            <i class="large material-icons blue-text">shopping_cart</i>
            <h4>Your cart is empty</h4>
            <p>Looks like you haven't added any products to your cart yet.</p>
            <a href="index" class="btn-large button-rounded waves-effect waves-light">Continue Shopping</a>
        </div>
    <?php } ?>
</div>
<?php
require 'includes/secondfooter.php';
require 'includes/footer.php'; ?>
