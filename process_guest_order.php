<?php
session_start();

// Check if form was submitted and guest cart exists
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['guest_cart']) && count($_SESSION['guest_cart']) > 0) {
    
    include 'db.php';
    
    // Get form data
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $zip = htmlspecialchars(trim($_POST['zip']));
    $notes = isset($_POST['notes']) ? htmlspecialchars(trim($_POST['notes'])) : '';
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($zip)) {
        header('Location: guest_checkout.php?error=Please fill all required fields');
        exit;
    }
    
    // Start transaction
    $connection->begin_transaction();
    
    try {
        // Create a guest user entry with a temporary account
        $guestPassword = bin2hex(random_bytes(8)); // Generate random password
        
        // Insert into users table
        $insertUser = "INSERT INTO users (username, firstName, lastName, email, phone, address, city, zip, password, role) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'guest')";
        
        $stmt = $connection->prepare($insertUser);
        $username = 'guest_' . time(); // Create a unique username
        $stmt->bind_param("sssssssss", $username, $first_name, $last_name, $email, $phone, $address, $city, $zip, $guestPassword);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create guest user: " . $stmt->error);
        }
        
        $guest_id = $connection->insert_id;
        
        // Debug log
        error_log("Guest user created with ID: " . $guest_id);
        
        // Insert all items from guest cart into commands
        foreach ($_SESSION['guest_cart'] as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            
            error_log("Processing product ID: " . $product_id . " with quantity: " . $quantity);
            
            // Check stock availability
            $checkStock = "SELECT stock FROM product WHERE id = ? AND stock >= ?";
            $checkStmt = $connection->prepare($checkStock);
            $checkStmt->bind_param("ii", $product_id, $quantity);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                error_log("Not enough stock for product ID: " . $product_id);
                throw new Exception("Not enough stock available for " . $item['name']);
            }
            
            // Get current stock for logging
            $stockQuery = "SELECT stock FROM product WHERE id = ?";
            $stockStmt = $connection->prepare($stockQuery);
            $stockStmt->bind_param("i", $product_id);
            $stockStmt->execute();
            $stockResult = $stockStmt->get_result();
            $stockRow = $stockResult->fetch_assoc();
            $currentStock = $stockRow['stock'];
            error_log("Current stock for product ID " . $product_id . ": " . $currentStock);
            $stockStmt->close();
            
            // Insert into command
            $insertCommand = "INSERT INTO command (id_product, quantity, statut, id_user) VALUES (?, ?, 'paid', ?)";
            $cmdStmt = $connection->prepare($insertCommand);
            $cmdStmt->bind_param("iis", $product_id, $quantity, $guest_id);
            
            if (!$cmdStmt->execute()) {
                error_log("Failed to create order: " . $cmdStmt->error);
                throw new Exception("Failed to create order: " . $cmdStmt->error);
            }
            error_log("Order created for product ID: " . $product_id);
            
            // Update product stock
            $updateStock = "UPDATE product SET stock = stock - ? WHERE id = ?";
            $updateStmt = $connection->prepare($updateStock);
            $updateStmt->bind_param("ii", $quantity, $product_id);
            
            if (!$updateStmt->execute()) {
                error_log("Failed to update stock: " . $updateStmt->error);
                throw new Exception("Failed to update stock: " . $updateStmt->error);
            }
            error_log("Stock updated for product ID " . $product_id . ". New stock: " . ($currentStock - $quantity));
            
            $checkStmt->close();
            $cmdStmt->close();
            $updateStmt->close();
        }
        
        // Add order note if provided
        if (!empty($notes)) {
            try {
                // Check if order_notes table exists
                $checkTable = "SHOW TABLES LIKE 'order_notes'";
                $tableResult = $connection->query($checkTable);
                
                if ($tableResult->num_rows > 0) {
                    $insertNote = "INSERT INTO order_notes (id_user, note) VALUES (?, ?)";
                    $noteStmt = $connection->prepare($insertNote);
                    $noteStmt->bind_param("ss", $guest_id, $notes);
                    $noteStmt->execute();
                    $noteStmt->close();
                    error_log("Order note added successfully");
                } else {
                    error_log("order_notes table does not exist, skipping note");
                }
            } catch (Exception $e) {
                // Just log the error but don't fail the transaction
                error_log("Failed to add order note: " . $e->getMessage());
            }
        }
        
        // Commit transaction
        $connection->commit();
        
        // Clear guest cart
        unset($_SESSION['guest_cart']);
        $_SESSION['item'] = 0;
        
        // Show success page
        header('Location: order_success.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $connection->rollback();
        
        header('Location: guest_checkout.php?error=' . urlencode($e->getMessage()));
        exit;
    }
    
} else {
    // Invalid request
    header('Location: cart.php');
    exit;
} 