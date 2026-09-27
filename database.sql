-- Database Creation
CREATE DATABASE IF NOT EXISTS `restaurant_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `restaurant_db`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Manager', 'Cashier', 'Waiter', 'Chef') NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
INSERT INTO `users` (`username`, `password_hash`, `role`, `full_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'System Administrator')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Menu Categories
CREATE TABLE IF NOT EXISTS `menu_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `color` VARCHAR(20) DEFAULT '#0d6efd',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `image_url` VARCHAR(255) DEFAULT 'assets/img/default_food.png',
  `status` ENUM('Available', 'Not Available') DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `menu_categories`(`id`) ON DELETE CASCADE
);

-- Tables
CREATE TABLE IF NOT EXISTS `restaurant_tables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `table_number` VARCHAR(20) NOT NULL UNIQUE,
  `capacity` INT NOT NULL DEFAULT 4,
  `status` ENUM('Available', 'Occupied', 'Reserved') DEFAULT 'Available'
);

-- Insert some default tables
INSERT INTO `restaurant_tables` (`table_number`, `capacity`) VALUES
('T1', 2), ('T2', 2), ('T3', 4), ('T4', 4), ('T5', 6), ('T6', 8)
ON DUPLICATE KEY UPDATE `table_number`=`table_number`;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `table_id` INT NOT NULL,
  `user_id` INT NOT NULL, -- Waiter or Cashier who took the order
  `status` ENUM('Pending', 'Kitchen', 'Ready', 'Served', 'Paid', 'Cancelled') DEFAULT 'Pending',
  `total_amount` DECIMAL(10,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- Order Items (KOT Details)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `menu_item_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL, -- Price at the time of order
  `notes` VARCHAR(255),
  `status` ENUM('Pending', 'Preparing', 'Ready', 'Served') DEFAULT 'Pending',
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`)
);

-- Settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT
);

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('restaurant_name', 'The Colorful Kitchen'),
('tax_rate', '5.00'),
('currency_symbol', '$')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;
