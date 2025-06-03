<?php
session_start();

if (!isset($_SESSION['logged_in']) && !isset($_POST['pay'])) {
    header('Location: sign');
}

if (isset($_POST['pay'])) {
    include 'db.php';

    // Get user information from POST and session
    $userId = $_SESSION['id'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    
    // Start transaction
    $connection->begin_transaction();
    
    try {
        // Update processing items to paid status
        $update_status = "UPDATE command SET statut = 'paid' WHERE id_user = ? AND statut = 'processing'";
        $stmt_status = $connection->prepare($update_status);
        $stmt_status->bind_param("i", $userId);
        
        if (!$stmt_status->execute()) {
            throw new Exception("Failed to update order status");
        }
        
        // Get all the orders that were just processed
        $get_orders = "SELECT id, id_product, quantity FROM command WHERE id_user = ? AND statut = 'paid'";
        $stmt_orders = $connection->prepare($get_orders);
        $stmt_orders->bind_param("i", $userId);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        
        // For each order, create a details_command record
        while ($order = $result_orders->fetch_assoc()) {
            $orderId = $order['id'];
            $productId = $order['id_product'];
            $quantity = $order['quantity'];
            
            // Get product details
            $get_product = "SELECT name, price FROM product WHERE id = ?";
            $stmt_product = $connection->prepare($get_product);
            $stmt_product->bind_param("i", $productId);
            $stmt_product->execute();
            $product = $stmt_product->get_result()->fetch_assoc();
            $productName = $product['name'];
            $productPrice = $product['price'];
            
            // Get user name
            $userName = $_POST['firstname'] . ' ' . $_POST['lastname'];
            
            // Create details_command record
            $insert_details = "INSERT INTO details_command (product, quantity, price, id_command, id_user, user, address, country, city, statut) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'done')";
            $stmt_details = $connection->prepare($insert_details);
            $stmt_details->bind_param("siiiissss", $productName, $quantity, $productPrice, $orderId, $userId, $userName, $address, $country, $city);
            
            if (!$stmt_details->execute()) {
                throw new Exception("Failed to create order details");
            }
            
            $stmt_product->close();
            $stmt_details->close();
        }
        
        $connection->commit();
        
        // Close statements
        $stmt_status->close();
        $stmt_orders->close();
        
        // Clear cart completely
        unset($_SESSION["item"]);
        $_SESSION["item"] = 0;
        
    } catch (Exception $e) {
        // Rollback on error
        $connection->rollback();
        
        // Log error
        error_log("Order finalization error: " . $e->getMessage());
        
        // Still let the user continue to the thank you page, but display an error message
        $_SESSION['checkout_error'] = $e->getMessage();
    }
    
    // Process session variables for the thank you page
    $nav ='includes/navconnected.php';
    $idsess = $_SESSION['id'];

    $email_sess = $_SESSION['email'];
    $country_sess = $_SESSION['country'];
    $firstname_sess = $_SESSION['firstname'];
    $lastname_sess = $_SESSION['lastname'];
    $city_sess = $_SESSION['city'];
    $address_sess = $_SESSION['address'];
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
                    <a href="final" class="breadcrumb">Thank you</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container thanks">
    <div class="row">
        <div class="col s12 m3">

        </div>

        <div class="col s12 m6">
            <?php if (isset($_SESSION['checkout_error'])): ?>
            <div class="card-panel red lighten-4">
                <span class="red-text text-darken-2">There was an error processing your order: <?= $_SESSION['checkout_error'] ?></span>
                <p>Please contact customer support for assistance.</p>
                <?php unset($_SESSION['checkout_error']); ?>
            </div>
            <?php endif; ?>
            
            <div class="card center-align">
                <div class="card-image">
                    <img src="src/img/thanks.png" class="responsive-img" alt="">
                </div>
                <div class="card-content center-align">
                    <h5>Thank you for your purchase</h5>
                    <p>Your order is on its way Dear : <h5 class="green-text"><?php echo"$firstname_sess". " " . "$lastname_sess";  ?></h5></p>
                </div>
            </div>

            <div class="center-align">
                <a href="details.php" class="button-rounded blue btn waves-effects waves-light">Details</a>
                <a href="index" class="button-rounded btn waves-effects waves-light">Home</a>
            </div>
        </div>
        <div class="col s12 m3">

        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
