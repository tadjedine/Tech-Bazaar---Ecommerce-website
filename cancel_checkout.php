<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: sign');
    exit;
}

$idsess = $_SESSION['id'];

include 'db.php';

// Start transaction
$connection->begin_transaction();

try {
    // 1. Get items in processing status
    $query_get = "SELECT id_product, quantity FROM command WHERE id_user = ? AND statut = 'processing'";
    $stmt_get = $connection->prepare($query_get);
    $stmt_get->bind_param("i", $idsess);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows === 0) {
        // No items in processing status, nothing to do
        header('Location: cart');
        exit;
    }
    
    // 2. Change all processing items back to regular cart status
    $query_update = "UPDATE command SET statut = 'ordered' WHERE id_user = ? AND statut = 'processing'";
    $stmt_update = $connection->prepare($query_update);
    $stmt_update->bind_param("i", $idsess);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Failed to cancel checkout");
    }
    
    // Commit all changes
    $connection->commit();
    
    // Close statements
    $stmt_get->close();
    $stmt_update->close();
    
    // Redirect back to cart
    header('Location: cart');
    
} catch (Exception $e) {
    // Rollback on error
    $connection->rollback();
    
    // Log error
    error_log("Checkout cancellation error: " . $e->getMessage());
    
    // Error redirect
    header('Location: cart?error=' . urlencode($e->getMessage()));
}

// Close connection
$connection->close();
?> 