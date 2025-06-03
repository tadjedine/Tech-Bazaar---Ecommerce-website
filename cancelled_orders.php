<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: sign');
} else {
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
                    <a href="index" class="breadcrumb">E-Commerce</a>
                    <a href="orders" class="breadcrumb">Orders</a>
                    <a href="cancelled_orders" class="breadcrumb">Cancelled Orders</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container scroll">
    <h4>Cancelled Orders History</h4>
    
    <table class="highlight striped">
        <thead>
        <tr>
            <th data-field="id">Order ID</th>
            <th data-field="product">Product</th>
            <th data-field="quantity">Quantity</th>
            <th data-field="date">Cancellation Date</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include 'db.php';
        
        // Use DISTINCT to prevent duplicate entries
        $query = "SELECT DISTINCT co.id_command, p.name as product_name, co.quantity, co.cancel_date 
                 FROM cancelled_orders co
                 JOIN product p ON co.id_product = p.id
                 WHERE co.id_user = ?
                 ORDER BY co.cancel_date DESC";
                 
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idsess);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Track already shown order IDs to prevent duplicates
        $shown_orders = [];
        
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                $order_id = $row['id_command'];
                
                // Skip if we've already shown this order
                if (in_array($order_id, $shown_orders)) {
                    continue;
                }
                
                // Add to our tracking array
                $shown_orders[] = $order_id;
                ?>
                <tr>
                    <td><?= $row['id_command']; ?></td>
                    <td><?= $row['product_name']; ?></td>
                    <td><?= $row['quantity']; ?></td>
                    <td><?= date('M d, Y H:i', strtotime($row['cancel_date'])); ?></td>
                </tr>
            <?php }
            $stmt->close();
        } else { ?>
            <tr>
                <td colspan="4" class="center-align">You have no cancelled orders.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    
    <div class="center-align">
        <br>
        <a href="orders.php" class="btn-large meh button-rounded waves-effect waves-light">Back to Orders</a>
        <br><br>
    </div>
</div>

<?php
require 'includes/footer.php';
?> 