<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: sign');
    exit;
}

$idsess = $_SESSION['id'];

// Validate request parameters
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php?error=invalid_order');
    exit;
}

$order_id = $_GET['id'];

include 'db.php';

// Start transaction
$connection->begin_transaction();

try {
    // 1. Get order details before cancellation
    $query_get_order = "SELECT id_product, quantity FROM command WHERE id = ? AND id_user = ? AND statut = 'paid'";
    $stmt_get = $connection->prepare($query_get_order);
    $stmt_get->bind_param("ii", $order_id, $idsess);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Order not found or already cancelled");
    }
    
    $order = $result->fetch_assoc();
    $product_id = $order['id_product'];
    $quantity = $order['quantity'];
    
    // 2. Update order status to cancelled
    $query_update = "UPDATE command SET statut = 'cancelled' WHERE id = ? AND id_user = ? AND statut = 'paid'";
    $stmt_update = $connection->prepare($query_update);
    $stmt_update->bind_param("ii", $order_id, $idsess);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Failed to update order status");
    }
    
    // 3. Manually restore product stock
    $query_restore = "UPDATE product SET stock = stock + ? WHERE id = ?";
    $stmt_restore = $connection->prepare($query_restore);
    $stmt_restore->bind_param("ii", $quantity, $product_id);
    
    if (!$stmt_restore->execute()) {
        throw new Exception("Failed to restore product stock");
    }
    
    // 4. Check if this order cancellation is already recorded
    $check_existing = "SELECT id FROM cancelled_orders WHERE id_command = ? AND id_user = ?";
    $stmt_check = $connection->prepare($check_existing);
    $stmt_check->bind_param("ii", $order_id, $idsess);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    // Only insert if no existing record found
    if ($check_result->num_rows == 0) {
        // Record cancellation in cancelled_orders table
        $query_record = "INSERT INTO cancelled_orders (id_command, id_product, quantity, id_user) 
                        VALUES (?, ?, ?, ?)";
        $stmt_record = $connection->prepare($query_record);
        $stmt_record->bind_param("iiii", $order_id, $product_id, $quantity, $idsess);
        
        if (!$stmt_record->execute()) {
            throw new Exception("Failed to record cancellation");
        }
    }
    
    // Commit all changes
    $connection->commit();
    
    // Close statements
    $stmt_get->close();
    $stmt_update->close();
    $stmt_restore->close();
    $stmt_check->close();
    if (isset($stmt_record)) $stmt_record->close();
    
    // Success redirect
    header('Location: orders.php?status=cancelled');
    
} catch (Exception $e) {
    // Rollback on error
    $connection->rollback();
    
    // Log error
    error_log("Order cancellation error: " . $e->getMessage());
    
    // Error redirect
    header('Location: orders.php?error=' . urlencode($e->getMessage()));
}

// Close connection
$connection->close();
?> 