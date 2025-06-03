-- MySQL WorkBench - E-commerce Database Updates



-- Create a table for cancelled orders history
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

-- 1. Stored procedure to display order details and total amount for a client
DELIMITER //
CREATE PROCEDURE GetOrderDetails(IN order_id INT)
BEGIN
    SELECT dc.*, p.name, p.price, (dc.quantity * p.price) AS item_total
    FROM details_command dc
    JOIN product p ON dc.product = p.name
    WHERE dc.id_command = order_id;

    SELECT SUM(dc.quantity * p.price) AS total_amount
    FROM details_command dc
    JOIN product p ON dc.product = p.name
    WHERE dc.id_command = order_id;
END //
DELIMITER ;

-- 2. Stored procedure to finalize an order and empty the cart
DELIMITER //
CREATE PROCEDURE FinalizeOrder(
    IN user_id INT,
    IN address VARCHAR(1000),
    IN city VARCHAR(1000),
    IN country VARCHAR(1000)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE prod_id INT;
    DECLARE prod_name VARCHAR(1000);
    DECLARE prod_qty INT;
    DECLARE prod_price INT;
    DECLARE order_id INT;
    DECLARE user_name VARCHAR(1000);

    DECLARE cart_cursor CURSOR FOR 
        SELECT c.id_product, p.name, c.quantity, p.price
        FROM command c
        JOIN product p ON c.id_product = p.id
        WHERE c.id_user = user_id AND c.statut = 'cart';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Get user's full name
    SELECT CONCAT(firstname, ' ', lastname) INTO user_name
    FROM users
    WHERE id = user_id;

    -- Start transaction
    START TRANSACTION;

    -- Open cursor
    OPEN cart_cursor;

    -- Loop through cart items
    read_loop: LOOP
        FETCH cart_cursor INTO prod_id, prod_name, prod_qty, prod_price;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Insert new command with 'paid' status
        INSERT INTO command (id_product, quantity, statut, id_user)
        VALUES (prod_id, prod_qty, 'paid', user_id);

        SET order_id = LAST_INSERT_ID();

        -- Insert into details_command
        INSERT INTO details_command (product, quantity, price, id_command, id_user, user, address, country, city, statut)
        VALUES (prod_name, prod_qty, prod_price, order_id, user_id, user_name, address, country, city, 'done');

        -- Update stock
        UPDATE product
        SET stock = stock - prod_qty
        WHERE id = prod_id;
    END LOOP;

    -- Close cursor
    CLOSE cart_cursor;

    -- Clear cart
    DELETE FROM command WHERE id_user = user_id AND statut = 'cart';

    -- Commit transaction
    COMMIT;
END //
DELIMITER ;

-- 3. Procedure to display order history for a client
DELIMITER //
CREATE PROCEDURE GetOrderHistory(IN user_id INT)
BEGIN
    SELECT c.id, c.`date`, p.name, c.quantity, p.price, (c.quantity * p.price) AS total, c.statut
    FROM command c
    JOIN product p ON c.id_product = p.id
    WHERE c.id_user = user_id AND c.statut = 'paid'
    ORDER BY c.`date` DESC;
END //
DELIMITER ;

-- 4. Trigger to update stock after order validation
DELIMITER //
CREATE TRIGGER update_stock_after_order
AFTER UPDATE ON command
FOR EACH ROW
BEGIN
    IF NEW.statut = 'paid' AND OLD.statut != 'paid' THEN
        UPDATE product SET stock = stock - NEW.quantity
        WHERE id = NEW.id_product;
    END IF;
END //
DELIMITER ;

-- 5. Trigger to prevent order if quantity exceeds stock
DELIMITER //
CREATE TRIGGER check_stock_before_order
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
END //
DELIMITER ;

-- 6. Trigger to restore stock after order cancellation
DELIMITER //
CREATE TRIGGER restore_stock_after_cancel
AFTER UPDATE ON command
FOR EACH ROW
BEGIN
    IF NEW.statut = 'cancelled' AND OLD.statut = 'paid' THEN
        UPDATE product
        SET stock = stock + OLD.quantity
        WHERE id = OLD.id_product;
    END IF;
END //
DELIMITER ;

-- 7. Trigger to track cancelled orders
DELIMITER //
CREATE TRIGGER track_cancelled_orders
AFTER UPDATE ON command
FOR EACH ROW
BEGIN
    IF NEW.statut = 'cancelled' AND OLD.statut = 'paid' THEN
        INSERT INTO cancelled_orders (id_command, id_product, quantity, id_user)
        VALUES (OLD.id, OLD.id_product, OLD.quantity, OLD.id_user);
    END IF;
END //
DELIMITER ;
