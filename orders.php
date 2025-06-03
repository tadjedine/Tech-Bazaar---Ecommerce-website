<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
    $nav ='includes/nav.php';
}
else {
    $nav ='includes/navconnected.php';
    $idsess = $_SESSION['id'];
}

require 'includes/header.php';
require $nav; ?>

<div class="container-fluid product-page">
    <div class="container current-page">
        <nav>
            <div class="nav-wrapper">
                <div class="col s12">
                    <a href="index" class="breadcrumb">E-Commerce</a>
                    <a href="orders" class="breadcrumb">Orders</a>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="container scroll">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'cancelled'): ?>
    <div class="card-panel green lighten-4">
        <span class="green-text text-darken-2">Your order has been cancelled successfully and the products have been returned to inventory.</span>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
    <div class="card-panel red lighten-4">
        <span class="red-text text-darken-2">Error: <?= htmlspecialchars($_GET['error']) ?></span>
    </div>
    <?php endif; ?>

    <table class="highlight striped">
        <thead>
        <tr>
            <th data-field="id">Order ID</th>
            <th data-field="name">Name</th>
            <th data-field="quantity">Quantity</th>
            <th data-field="price">Price</th>
            <th data-field="total">Total</th>
            <th data-field="date">Date</th>
            <th data-field="status">Status</th>
            <th data-field="action">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include 'db.php';
        
        // Use a direct query instead of the stored procedure
        $query = "SELECT c.id, p.name, c.quantity, p.price, (c.quantity * p.price) AS total, 
                 c.dat, c.statut
                 FROM command c
                 JOIN product p ON c.id_product = p.id
                 WHERE c.id_user = ? AND c.statut = 'paid'
                 ORDER BY c.dat DESC";
        
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idsess);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // output data of each row
            while($roworder = $result->fetch_assoc()) {
                $id = $roworder['id'];
                $productname = $roworder['name'];
                $quantity = $roworder['quantity'];
                $price = $roworder['price'];
                $total = $roworder['total'];
                $dat = $roworder['dat'];
                $statu = $roworder['statut'];
                ?>
                <tr>
                    <td><?= $id; ?></td>
                    <td><?= $productname; ?></td>
                    <td><?= $quantity; ?></td>
                    <td>$<?= $price; ?></td>
                    <td>$<?= $total; ?></td>
                    <td><?= date('M d, Y H:i', strtotime($dat)); ?></td>
                    <td><?= $statu; ?></td>
                    <td>
                        <a href="cancel_order.php?id=<?= $id; ?>" class="btn-small red waves-effect waves-light" 
                           onclick="return confirm('Are you sure you want to cancel this order? The product will be returned to inventory.');">
                            Cancel
                        </a>
                    </td>
                </tr>
            <?php }
            $stmt->close();
        } else { ?>
            <tr>
                <td colspan="8" class="center-align">You have no active orders.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="center-align">
        <br>
        <a href="downloadorder.php?id=<?= $_SESSION['id']; ?>" class="btn-large meh button-rounded waves-effect waves-light">Download</a>
        <a href="cancelled_orders.php" class="btn-large red button-rounded waves-effect waves-light">View Cancelled Orders</a>
        <br><br>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
