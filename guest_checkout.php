<?php
session_start();

// Redirect if no items in guest cart
if (!isset($_SESSION['guest_cart']) || count($_SESSION['guest_cart']) === 0) {
    header('Location: cart.php');
    exit;
}

$nav = 'includes/nav.php';
require 'includes/header.php';
require $nav;
?>

<div class="container-fluid product-page">
    <div class="container current-page">
        <nav>
            <div class="nav-wrapper">
                <div class="col s12">
                    <a href="index" class="breadcrumb">Home</a>
                    <a href="cart" class="breadcrumb">Cart</a>
                    <a href="guest_checkout" class="breadcrumb">Guest Checkout</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container scroll info">
    <h4 class="center-align animated fadeIn">Guest Checkout</h4>
    
    <?php if (isset($_GET['error'])): ?>
    <div class="card-panel red lighten-4">
        <span class="red-text text-darken-2">Error: <?= htmlspecialchars($_GET['error']) ?></span>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col s12 m8">
            <!-- Order Summary -->
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Order Summary</span>
                    <table class="highlight">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_price = 0;
                            foreach ($_SESSION['guest_cart'] as $item) {
                                $subtotal = $item['price'] * $item['quantity'];
                                $total_price += $subtotal;
                            ?>
                            <tr>
                                <td><?= $item['name'] ?></td>
                                <td>$<?= $item['price'] ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= $subtotal ?></td>
                            </tr>
                            <?php } ?>
                            <tr class="green lighten-4">
                                <td colspan="3"><strong>Total:</strong></td>
                                <td><strong>$<?= $total_price ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Guest Form -->
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Your Information</span>
                    <form method="post" action="process_guest_order.php">
                        <div class="row">
                            <div class="input-field col s6">
                                <i class="material-icons prefix">person</i>
                                <input id="first_name" name="first_name" type="text" class="validate" required>
                                <label for="first_name">First Name</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="last_name" name="last_name" type="text" class="validate" required>
                                <label for="last_name">Last Name</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">email</i>
                                <input id="email" name="email" type="email" class="validate" required>
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">phone</i>
                                <input id="phone" name="phone" type="tel" class="validate" required>
                                <label for="phone">Phone Number</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">home</i>
                                <input id="address" name="address" type="text" class="validate" required>
                                <label for="address">Delivery Address</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s6">
                                <i class="material-icons prefix">location_city</i>
                                <input id="city" name="city" type="text" class="validate" required>
                                <label for="city">City</label>
                            </div>
                            <div class="input-field col s6">
                                <input id="zip" name="zip" type="text" class="validate" required>
                                <label for="zip">ZIP Code</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12">
                                <i class="material-icons prefix">comment</i>
                                <textarea id="notes" name="notes" class="materialize-textarea"></textarea>
                                <label for="notes">Order Notes (Optional)</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <p>
                                    <label>
                                        <input type="checkbox" required class="filled-in" />
                                        <span>I agree to the <a href="#">terms and conditions</a></span>
                                    </label>
                                </p>
                            </div>
                        </div>
                        <div class="row center-align">
                            <button type="submit" class="btn-large waves-effect waves-light">
                                Complete Purchase <i class="material-icons right">payment</i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col s12 m4">
            <!-- Benefits Panel -->
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Why Create an Account?</span>
                    <ul class="collection">
                        <li class="collection-item"><i class="material-icons tiny">check</i> Track your orders</li>
                        <li class="collection-item"><i class="material-icons tiny">check</i> Save your shipping details</li>
                        <li class="collection-item"><i class="material-icons tiny">check</i> Access order history</li>
                        <li class="collection-item"><i class="material-icons tiny">check</i> Receive exclusive offers</li>
                    </ul>
                    <div class="center-align" style="margin-top: 20px;">
                        <a href="sign" class="waves-effect waves-light btn">
                            Create Account <i class="material-icons right">person_add</i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Secure Checkout Info -->
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Secure Checkout</span>
                    <p><i class="material-icons tiny">lock</i> Your payment information is secure.</p>
                    <p><i class="material-icons tiny">security</i> We use cookies to remember your preferences and enhance your shopping experience.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require 'includes/secondfooter.php';
require 'includes/footer.php';
?> 