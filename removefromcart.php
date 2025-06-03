<?php
session_start();

// Check if the key is set and guest_cart exists
if (isset($_GET['key']) && isset($_SESSION['guest_cart'])) {
    // Get the key from URL
    $key = (int)$_GET['key'];
    
    // Check if the key exists in the guest_cart array
    if (isset($_SESSION['guest_cart'][$key])) {
        // Remove the item from the cart
        unset($_SESSION['guest_cart'][$key]);
        
        // Re-index the array
        $_SESSION['guest_cart'] = array_values($_SESSION['guest_cart']);
        
        // Recalculate the total number of items
        $totalItems = 0;
        foreach ($_SESSION['guest_cart'] as $cartItem) {
            $totalItems += $cartItem['quantity'];
        }
        $_SESSION['item'] = $totalItems;
        
        // Redirect back to cart with success message
        header('Location: cart.php?removed=success');
        exit;
    }
}

// Redirect back to cart
header('Location: cart.php');
exit; 