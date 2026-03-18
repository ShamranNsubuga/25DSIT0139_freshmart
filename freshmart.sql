-- ============================================
-- FRESHMART SUPERMARKET DATABASE
-- Import this in phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS freshmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freshmart;

-- ─── CUSTOMERS ───
CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  phone VARCHAR(30),
  password VARCHAR(255) NOT NULL,
  address TEXT,
  city VARCHAR(100),
  gender VARCHAR(20),
  loyalty_points INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── ADMINS ───
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150),
  role ENUM('superadmin','manager','inventory','cashier') DEFAULT 'manager',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── CATEGORIES ───
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  icon VARCHAR(10),
  slug VARCHAR(100) UNIQUE NOT NULL,
  is_active TINYINT(1) DEFAULT 1
);

-- ─── PRODUCTS ───
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  category_id INT,
  price DECIMAL(12,2) NOT NULL,
  old_price DECIMAL(12,2),
  weight VARCHAR(50),
  stock INT DEFAULT 0,
  min_stock INT DEFAULT 10,
  emoji VARCHAR(10) DEFAULT '🛍️',
  badge ENUM('none','new','sale') DEFAULT 'none',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- ─── ORDERS ───
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(20) UNIQUE NOT NULL,
  customer_id INT,
  total_amount DECIMAL(12,2) NOT NULL,
  payment_method ENUM('mobile_money','card','cash') DEFAULT 'cash',
  payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
  status ENUM('pending','confirmed','in_transit','delivered','cancelled') DEFAULT 'pending',
  delivery_address TEXT,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- ─── ORDER ITEMS ───
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ─── STAFF ───
CREATE TABLE staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) UNIQUE,
  phone VARCHAR(30),
  role VARCHAR(100),
  shift ENUM('morning','afternoon','night','all_day') DEFAULT 'morning',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── PROMOTIONS ───
CREATE TABLE promotions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  description VARCHAR(255),
  discount_type ENUM('percent','fixed') DEFAULT 'percent',
  discount_value DECIMAL(10,2) NOT NULL,
  min_order DECIMAL(12,2) DEFAULT 0,
  max_uses INT DEFAULT 0,
  used_count INT DEFAULT 0,
  valid_from DATE,
  valid_until DATE,
  is_active TINYINT(1) DEFAULT 1
);

-- ============================================
-- SEED DATA
-- ============================================

-- Admin (password: admin123)
INSERT INTO admins (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'superadmin');

-- Categories
INSERT INTO categories (name, icon, slug) VALUES
('Vegetables','🥦','vegetables'),
('Fruits','🍎','fruits'),
('Dairy','🥛','dairy'),
('Meat & Fish','🍖','meat-fish'),
('Bakery','🍞','bakery'),
('Beverages','🥤','beverages'),
('Household','🧴','household'),
('Grains & Cereals','🌾','grains'),
('Condiments','🍯','condiments'),
('Snacks','🍭','snacks');

-- Products
INSERT INTO products (name, category_id, price, old_price, weight, stock, min_stock, emoji, badge) VALUES
('Fresh Whole Milk 1L', 3, 4500, NULL, '1 Litre', 240, 50, '🥛', 'new'),
('Sweet Bananas (5-6 pcs)', 2, 3000, NULL, '~700g', 180, 40, '🍌', 'none'),
('Free Range Eggs x30', 3, 18000, 22000, '30 pcs', 95, 20, '🥚', 'sale'),
('Red Apples 1kg', 2, 12000, 15000, '1 kg', 120, 30, '🍎', 'sale'),
('Red Onions 1kg', 1, 2500, NULL, '1 kg', 300, 50, '🧅', 'none'),
('Cheddar Cheese 250g', 3, 22000, NULL, '250g', 60, 15, '🧀', 'new'),
('Whole Wheat Bread', 5, 6000, NULL, '700g', 3, 20, '🍞', 'none'),
('Orange Juice 1L', 6, 8500, 10000, '1 Litre', 80, 20, '🥤', 'sale'),
('Chicken Breast 1kg', 4, 18000, 24000, '1 kg', 55, 20, '🍗', 'sale'),
('Fresh Broccoli', 1, 4000, 6000, '~400g', 40, 15, '🥦', 'sale'),
('Liquid Yoghurt 500ml', 3, 5500, 7000, '500ml', 3, 20, '🧴', 'sale'),
('Sweet Corn (4 cobs)', 1, 3500, 5000, '4 pcs', 70, 20, '🌽', 'sale');

-- Staff
INSERT INTO staff (full_name, email, phone, role, shift) VALUES
('Alice Namutebi', 'alice@freshmart.ug', '+256 700 001 001', 'Store Manager', 'morning'),
('Brian Ssempa', 'brian@freshmart.ug', '+256 700 002 002', 'Cashier', 'afternoon'),
('Carol Nalwanga', 'carol@freshmart.ug', '+256 700 003 003', 'Inventory Staff', 'morning'),
('Denis Ochieng', 'denis@freshmart.ug', '+256 700 004 004', 'Delivery Rider', 'all_day');

-- Promotions
INSERT INTO promotions (code, description, discount_type, discount_value, max_uses, valid_from, valid_until) VALUES
('FRESH10', 'New customer first order', 'percent', 10, 500, '2025-01-01', '2025-12-31'),
('WEEKEND40', 'Weekend flash sale', 'percent', 40, 200, '2025-03-15', '2025-03-21'),
('BULK15', 'Buy 5+ items bulk discount', 'percent', 15, 0, '2025-01-01', '2025-04-30'),
('SAVE5K', 'Orders over UGX 100k', 'fixed', 5000, 0, '2025-01-01', '2025-04-15');

-- Sample customers (password: password123)
INSERT INTO customers (first_name, last_name, email, phone, password, address, city, loyalty_points) VALUES
('Sarah', 'Kasozi', 'sarah@email.com', '+256 782 111 222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kololo Hill Road', 'Kampala', 2450),
('John', 'Mugisha', 'john@email.com', '+256 754 333 444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ntinda, Plot 12', 'Kampala', 1680),
('Grace', 'Atim', 'grace@email.com', '+256 703 555 666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gulu Road', 'Gulu', 980);

-- Sample orders
INSERT INTO orders (order_number, customer_id, total_amount, payment_method, payment_status, status, delivery_address) VALUES
('ORD-0001', 1, 62000, 'mobile_money', 'paid', 'delivered', 'Kololo Hill Road, Kampala'),
('ORD-0002', 2, 38500, 'card', 'paid', 'delivered', 'Ntinda, Plot 12, Kampala'),
('ORD-0003', 3, 91000, 'cash', 'pending', 'in_transit', 'Gulu Road, Gulu'),
('ORD-0004', 1, 24000, 'mobile_money', 'paid', 'delivered', 'Kololo Hill Road, Kampala'),
('ORD-0005', 2, 55000, 'card', 'failed', 'cancelled', 'Ntinda, Plot 12, Kampala');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 3, 4500),(1, 3, 1, 18000),(1, 8, 2, 8500),
(2, 2, 2, 3000),(2, 4, 1, 12000),(2, 7, 1, 6000),
(3, 9, 2, 18000),(3, 6, 1, 22000),(3, 5, 3, 2500),
(4, 1, 2, 4500),(4, 11, 1, 5500),
(5, 3, 2, 18000),(5, 10, 1, 4000);
