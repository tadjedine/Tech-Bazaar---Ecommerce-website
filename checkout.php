<?php
session_start();

if (!isset($_SESSION['logged_in']) && !isset($_SESSION['item'])) {
    header('Location: sign');
}

elseif($_SESSION['item'] < 1){
    header('Location: index');
}
else {
    $nav ='includes/navconnected.php';
    $idsess = $_SESSION['id'];

    $email_sess = $_SESSION['email'];
    $country_sess = $_SESSION['country'];
    $firstname_sess = $_SESSION['firstname'];
    $lastname_sess = $_SESSION['lastname'];
    $city_sess = $_SESSION['city'];
    $address_sess = $_SESSION['address'];
    
    // Mark items as processing at the start of checkout
    include 'db.php';
    $updateCartItems = "UPDATE command SET statut = 'processing' WHERE id_user = ? AND statut = 'ordered'";
    $stmtUpdate = $connection->prepare($updateCartItems);
    $stmtUpdate->bind_param("i", $idsess);
    $stmtUpdate->execute();
    $stmtUpdate->close();
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
                    <a href="checkout" class="breadcrumb">Checkout</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container checkout">
    <div class="card pay">
            <div class="row">
            <div class="col s12">
                <div class="card-content">
                    <h4 class="center-align">Order Confirmation</h4>
                    <div class="divider"></div>
                    <br>
                    
                    <h5>Your Information</h5>
                    <table class="striped">
                        <tbody>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?= $firstname_sess . ' ' . $lastname_sess ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?= $email_sess ?></td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td><?= $address_sess ?></td>
                            </tr>
                            <tr>
                                <td><strong>City:</strong></td>
                                <td><?= $city_sess ?></td>
                            </tr>
                            <tr>
                                <td><strong>Country:</strong></td>
                                <td><?= $country_sess ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="divider"></div>
                    <br>
                    
                    <h5>Order Summary</h5>
                    <table class="striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th class="right-align">Price</th>
                                <th class="right-align">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_price = 0;
                            
                            $query = "SELECT p.name, p.price, c.quantity, (p.price * c.quantity) as subtotal
                                     FROM command c
                                     JOIN product p ON c.id_product = p.id
                                     WHERE c.id_user = ? AND c.statut = 'processing'";
                            
                            $stmt = $connection->prepare($query);
                            $stmt->bind_param("i", $idsess);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while($row = $result->fetch_assoc()) {
                                $total_price += $row['subtotal'];
                                ?>
                                <tr>
                                    <td><?= $row['name'] ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td class="right-align">$<?= $row['price'] ?></td>
                                    <td class="right-align">$<?= $row['subtotal'] ?></td>
                                </tr>
                            <?php } 
                            $stmt->close();
                            ?>
                            <tr class="green lighten-5">
                                <td colspan="3" class="right-align"><strong>Total:</strong></td>
                                <td class="right-align"><strong>$<?= $total_price ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-action center-align">
                    <form method="post" action="final">
                        <!-- Pass the user information as hidden fields -->
                        <input type="hidden" name="email" value="<?= $email_sess ?>">
                        <input type="hidden" name="country" value="<?= $country_sess ?>">
                        <input type="hidden" name="firstname" value="<?= $firstname_sess ?>">
                        <input type="hidden" name="lastname" value="<?= $lastname_sess ?>">
                        <input type="hidden" name="city" value="<?= $city_sess ?>">
                        <input type="hidden" name="address" value="<?= $address_sess ?>">
                        
                        <button type="submit" id="confirmed" name="pay" class="btn-large meh button-rounded waves-effect waves-light">
                            Confirm Order and Pay
                        </button>
                        
                        <a href="cancel_checkout.php" class="btn-large red button-rounded waves-effect waves-light">
                            Cancel Order
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
