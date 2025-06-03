<?php
// Database connection
include 'db.php';

// Check if the connection is successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define the SQL statements for triggers
$triggerSQL = [
    // 1. Trigger to check stock before order
    "DROP TRIGGER IF EXISTS check_stock_before_order",
    "CREATE TRIGGER check_stock_before_order
    BEFORE INSERT ON command
    FOR EACH ROW
    BEGIN
        DECLARE available_stock INT;

        SELECT stock INTO available_stock
        FROM product
        WHERE id = NEW.id_product;

        IF NEW.quantity > available_stock THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot place order: requested quantity exceeds available stock';
        END IF;
    END",

    // 2. Trigger to update stock after order validation
    "DROP TRIGGER IF EXISTS update_stock_after_order",
    "CREATE TRIGGER update_stock_after_order
    AFTER UPDATE ON command
    FOR EACH ROW
    BEGIN
        IF NEW.statut = 'paid' AND OLD.statut != 'paid' THEN
            UPDATE product SET stock = stock - NEW.quantity
            WHERE id = NEW.id_product;
        END IF;
    END",

    // 3. Trigger to restore stock after order cancellation
    "DROP TRIGGER IF EXISTS restore_stock_after_cancel",
    "CREATE TRIGGER restore_stock_after_cancel
    AFTER UPDATE ON command
    FOR EACH ROW
    BEGIN
        IF NEW.statut = 'cancelled' AND OLD.statut = 'paid' THEN
            UPDATE product
            SET stock = stock + OLD.quantity
            WHERE id = OLD.id_product;
        END IF;
    END",

    // 4. Trigger to track cancelled orders
    "DROP TRIGGER IF EXISTS track_cancelled_orders",
    "CREATE TRIGGER track_cancelled_orders
    AFTER UPDATE ON command
    FOR EACH ROW
    BEGIN
        IF NEW.statut = 'cancelled' AND OLD.statut = 'paid' THEN
            INSERT INTO cancelled_orders (id_command, id_product, quantity, id_user)
            VALUES (OLD.id, OLD.id_product, OLD.quantity, OLD.id_user);
        END IF;
    END",
    
    // 5. Add a trigger to update stock on cart changes
    "DROP TRIGGER IF EXISTS update_stock_on_add_to_cart",
    "CREATE TRIGGER update_stock_on_add_to_cart
    AFTER INSERT ON command
    FOR EACH ROW
    BEGIN
        IF NEW.statut = 'ordered' THEN
            UPDATE product SET stock = stock - NEW.quantity
            WHERE id = NEW.id_product;
        END IF;
    END"
];

// Execute each SQL statement
$success = true;
foreach ($triggerSQL as $sql) {
    if (!$connection->query($sql)) {
        echo "Error: " . $connection->error . "<br>";
        $success = false;
    }
}

// Create the cancelled_orders table if it doesn't exist
$createTableSQL = "
CREATE TABLE IF NOT EXISTS `cancelled_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_command` INT(11) NOT NULL,
  `id_product` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `id_user` INT(11) NOT NULL,
  `cancel_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` VARCHAR(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";

if (!$connection->query($createTableSQL)) {
    echo "Error creating table: " . $connection->error . "<br>";
    $success = false;
}

// Initialize stock values if they're NULL
$updateStockSQL = "
UPDATE product SET stock = 10 WHERE stock IS NULL OR stock = 0;
";

if (!$connection->query($updateStockSQL)) {
    echo "Error updating stock: " . $connection->error . "<br>";
    $success = false;
}

// Show result
if ($success) {
    echo "<h2>Database triggers installed successfully!</h2>";
    echo "<p>The stock management system has been activated.</p>";
    echo "<p><a href='index.php'>Return to Homepage</a></p>";
} else {
    echo "<h2>There were errors during installation.</h2>";
    echo "<p>Please check the error messages above.</p>";
}

// Close connection
$connection->close();
?> 