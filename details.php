<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: sign');
}

else {
    $idsess = $_SESSION['id'];
}
require 'includes/header.php';
?>

<div class="container print">
    <table>
        <thead>
        <tr>
            <th data-field="name">Item Name</th>
            <th data-field="category">quantity</th>
            <th data-field="price">price</th>
            <th data-field="subtotal">subtotal</th>
            <th data-field="quantity">user</th>
            <th data-field="country">country</th>
            <th data-field="city">city</th>
            <th data-field="address">address</th>
        </tr>
        </thead>
        <tbody class="scroll">
        <?php
        include 'db.php';
        
        // Get the latest command ID for this user
        $queryGetOrderId = "SELECT id FROM command WHERE id_user = ? ORDER BY dat DESC LIMIT 1";
        $stmtOrderId = $connection->prepare($queryGetOrderId);
        $stmtOrderId->bind_param("i", $idsess);
        $stmtOrderId->execute();
        $resultOrderId = $stmtOrderId->get_result();
        
        $total = 0;
        $idcmdd = 0;
        
        if ($resultOrderId->num_rows > 0) {
            $rowOrderId = $resultOrderId->fetch_assoc();
            $orderId = $rowOrderId['id'];
            $idcmdd = $orderId; // Store for later use
            
            $stmtOrderId->close();
            
            // Instead of calling stored procedure, use direct queries
            $queryDetails = "SELECT dc.*, p.name, p.price, (dc.quantity * p.price) AS item_total
                            FROM details_command dc
                            JOIN product p ON dc.product = p.name
                            WHERE dc.id_command = ?";
                            
            $stmtDetails = $connection->prepare($queryDetails);
            $stmtDetails->bind_param("i", $orderId);
            $stmtDetails->execute();
            $resultDetails = $stmtDetails->get_result();
            
            // Display order items
            while($rowdetails = $resultDetails->fetch_assoc()) {
                $product_details = $rowdetails['product'];
                $quantity_details = $rowdetails['quantity'];
                $price_details = $rowdetails['price'];
                $item_total = $rowdetails['item_total'];
                $user_details = $rowdetails['user'];
                $country_details = $rowdetails['country'];
                $city_details = $rowdetails['city'];
                $address_details = $rowdetails['address'];
                $total += $item_total;
                ?>
                <tr>
                    <td><?= $product_details; ?></td>
                    <td><?= $quantity_details; ?></td>
                    <td>$ <?= $price_details; ?></td>
                    <td>$ <?= $item_total; ?></td>
                    <td><?= $user_details; ?></td>
                    <td><?= $country_details; ?></td>
                    <td><?= $city_details; ?></td>
                    <td><?= $address_details; ?></td>
                </tr>
            <?php }
            
            $stmtDetails->close();
            
            // Get total separately
            $queryTotal = "SELECT SUM(dc.quantity * p.price) AS total_amount
                          FROM details_command dc
                          JOIN product p ON dc.product = p.name
                          WHERE dc.id_command = ?";
                          
            $stmtTotal = $connection->prepare($queryTotal);
            $stmtTotal->bind_param("i", $orderId);
            $stmtTotal->execute();
            $resultTotal = $stmtTotal->get_result();
            
            if ($resultTotal->num_rows > 0) {
                $totalRow = $resultTotal->fetch_assoc();
                $total = $totalRow['total_amount'];
            }
            
            $stmtTotal->close();
        }
        ?>
        
        <tr class="green lighten-2">
            <td colspan="3" class="right-align"><strong>Total:</strong></td>
            <td><strong>$ <?= $total; ?></strong></td>
            <td colspan="4"></td>
        </tr>
        
        <div class="left-align">
            <?php if ($idcmdd > 0) { ?>
                <h5>Invoice #<?= $idcmdd; ?></h5>
            <?php } ?>
        </div>
        </tbody>
    </table>
    <div class="right-align">
        <p>Thank you for trusting us © E-Commerce Inc <?= date('Y'); ?></p>
    </div>

    <form method="post">
        <button type="submit" name="done" class="button-rounded waves-effect waves-light btn">Home</button>
        <?php
        if (isset($_POST['done'])) {
            // Use prepared statement to update
            $queryupdate = "UPDATE details_command SET statut = 'done' WHERE id_user = ? AND statut = 'ready'";
            $stmtUpdate = $connection->prepare($queryupdate);
            $stmtUpdate->bind_param("i", $idsess);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            echo "<meta http-equiv='refresh' content='0;url=index' />";
        }
        ?>
    </form>
</div>

<?php require 'includes/footer.php'; ?>
