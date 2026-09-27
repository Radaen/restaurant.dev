<?php
require_once 'includes/db.php';

try {
    $pdo->beginTransaction();

    // 1. Create Customers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `customers` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `phone` VARCHAR(20) NOT NULL UNIQUE,
      `email` VARCHAR(100) DEFAULT NULL,
      `loyalty_points` INT DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create Suppliers Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `suppliers` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `contact_info` VARCHAR(255) DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Create Ingredients Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ingredients` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(100) NOT NULL,
      `unit` VARCHAR(20) NOT NULL,
      `stock_level` DECIMAL(10,2) DEFAULT 0.00,
      `reorder_level` DECIMAL(10,2) DEFAULT 10.00,
      `supplier_id` INT DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL
    )");

    // 4. Create Reservations Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `reservations` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `customer_id` INT NOT NULL,
      `table_id` INT NOT NULL,
      `reservation_date` DATE NOT NULL,
      `reservation_time` TIME NOT NULL,
      `guests` INT NOT NULL DEFAULT 2,
      `status` ENUM('Confirmed', 'Completed', 'Cancelled') DEFAULT 'Confirmed',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables`(`id`) ON DELETE CASCADE
    )");

    // 5. Alter Orders Table (ignore errors if columns already exist)
    try {
        $pdo->exec("ALTER TABLE orders MODIFY table_id INT NULL");
    } catch (Exception $e) {}

    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_id INT NULL AFTER table_id");
        $pdo->exec("ALTER TABLE orders ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL");
    } catch (Exception $e) {}

    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_type ENUM('Dine-in', 'Takeaway', 'Delivery') DEFAULT 'Dine-in' AFTER user_id");
    } catch (Exception $e) {}

    $pdo->commit();
    echo "Success: Missing tables and columns have been created.";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
