<?php
session_start();

include_once '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Use prepared statements to prevent SQL injection
    $query_delete = "DELETE FROM product WHERE id = ?";
    $stmt = $connection->prepare($query_delete);
    $stmt->bind_param("i", $id);
    $result_delete = $stmt->execute();
    $stmt->close();
    
    // Also delete product's pictures
    $query_delete_pics = "DELETE FROM pictures WHERE id_product = ?";
    $stmt2 = $connection->prepare($query_delete_pics);
    $stmt2->bind_param("i", $id);
    $result_delete_pics = $stmt2->execute();
    $stmt2->close();
    
    // Set a success message in session
    $_SESSION['delete_success'] = "Product successfully deleted";
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: ../sign');
}
?>
