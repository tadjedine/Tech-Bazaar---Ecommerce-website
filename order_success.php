<?php
session_start();

$nav = 'includes/nav.php';

// If the user is logged in, use the connected nav
if (isset($_SESSION['logged_in'])) {
    $nav = 'includes/navconnected.php';
    $idsess = $_SESSION['id'];
}

require 'includes/header.php';
require $nav;
?>

<div class="container-fluid product-page">
    <div class="container current-page">
        <nav>
            <div class="nav-wrapper">
                <div class="col s12">
                    <a href="index" class="breadcrumb">Home</a>
                    <a href="#" class="breadcrumb">Order Complete</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container scroll info">
    <div class="card-panel center-align">
        <i class="large material-icons green-text">check_circle</i>
        <h4>Your order has been placed successfully!</h4>
        <p>Thank you for shopping with us. We'll process your order as soon as possible.</p>
        
        <?php if (!isset($_SESSION['logged_in'])): ?>
        <p>Since you checked out as a guest, we'll send order updates to your provided email address.</p>
        <div class="divider" style="margin: 25px 0;"></div>
        <p>Create an account to easily track your orders in the future:</p>
        <a href="sign" class="btn waves-effect waves-light">Create Account</a>
        <?php else: ?>
        <p>You can track the status of your order in the "My Orders" section.</p>
        <a href="orders" class="btn waves-effect waves-light">View My Orders</a>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="index" class="btn-large button-rounded waves-effect waves-light">
                Continue Shopping <i class="material-icons right">shopping_cart</i>
            </a>
        </div>
    </div>
</div>

<?php
require 'includes/secondfooter.php';
require 'includes/footer.php';
?> 