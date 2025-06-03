<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: sign');
    exit;
}

$idsess = $_SESSION['id'];

// Validate request parameters
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: cart?error=invalid_item');
    exit;
}

$command_id = $_GET['id'];

include 'db.php';

// Start transaction
$connection->begin_transaction();

try {
    // Get the command details before deleting
    $query_get_command = "SELECT id_product, quantity FROM command WHERE id = ? AND id_user = ? AND statut = 'ordered'";
    $stmt_get = $connection->prepare($query_get_command);
    $stmt_get->bind_param("is", $command_id, $idsess);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Item not found in your cart");
    }
    
    $row = $result->fetch_assoc();
    $product_id = $row['id_product'];
    $quantity = $row['quantity'];
    
    // Delete the specific item from cart
    $query_delete = "DELETE FROM command WHERE id = ? AND id_user = ? AND statut = 'ordered'";
    $stmt_delete = $connection->prepare($query_delete);
    $stmt_delete->bind_param("is", $command_id, $idsess);
    
    if (!$stmt_delete->execute()) {
        throw new Exception("Failed to delete item from cart");
    }
    
    // Restore the product stock
    $query_restore_stock = "UPDATE product SET stock = stock + ? WHERE id = ?";
    $stmt_restore = $connection->prepare($query_restore_stock);
    $stmt_restore->bind_param("ii", $quantity, $product_id);
    
    if (!$stmt_restore->execute()) {
        throw new Exception("Failed to restore product stock");
    }
    
    // Decrement the cart item count
    if (isset($_SESSION['item']) && $_SESSION['item'] > 0) {
        $_SESSION['item'] -= 1;
    }
    
    // Commit all changes
    $connection->commit();
    
    // Close statements
    $stmt_get->close();
    $stmt_delete->close();
    $stmt_restore->close();
    
    // Redirect back to cart page
    header('Location: cart?removed=success');
    
} catch (Exception $e) {
    // Rollback on error
    $connection->rollback();
    
    // Log error
    error_log("Cart item deletion error: " . $e->getMessage());
    
    // Error redirect
    header('Location: cart?error=' . urlencode($e->getMessage()));
}

// Close connection
$connection->close();
?>
