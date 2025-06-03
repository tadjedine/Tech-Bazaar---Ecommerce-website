<?php
session_start();

include_once '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Use prepared statements to prevent SQL injection
    $query_delete = "DELETE FROM users WHERE id = ?";
    $stmt = $connection->prepare($query_delete);
    $stmt->bind_param("i", $id);
    $result_delete = $stmt->execute();
    $stmt->close();
    
    // Also delete user's commands
    $query_delete2 = "DELETE FROM command WHERE id_user = ?";
    $stmt2 = $connection->prepare($query_delete2);
    $stmt2->bind_param("i", $id);
    $result_delete2 = $stmt2->execute();
    $stmt2->close();
    
    // Also delete user's command details
    $query_delete3 = "DELETE FROM details_command WHERE id_user = ?";
    $stmt3 = $connection->prepare($query_delete3);
    $stmt3->bind_param("i", $id);
    $result_delete3 = $stmt3->execute();
    $stmt3->close();
    
    // Set a success message in session
    $_SESSION['delete_success'] = "User successfully deleted";
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: ../sign');
}
?>
