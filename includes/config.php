<?php
// ============================================
// includes/config.php — DB connection
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // XAMPP default
define('DB_PASS', '');            // XAMPP default (empty)
define('DB_NAME', 'freshmart');
define('SITE_NAME', 'FreshMart');
define('CURRENCY', 'UGX');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;background:#fff0f0;color:#c00;border-left:4px solid #c00;margin:40px;">
        <h2>Database Connection Failed</h2>
        <p>' . $conn->connect_error . '</p>
        <p><strong>Fix:</strong> Make sure XAMPP MySQL is running and you have imported <code>freshmart.sql</code> in phpMyAdmin.</p>
    </div>');
}
$conn->set_charset('utf8mb4');

// ─── Helper functions ───

function sanitize($conn, $val) {
    return $conn->real_escape_string(trim($val));
}

function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireCustomerLogin() {
    if (!isCustomerLoggedIn()) {
        header('Location: /freshmart/customer/login.php');
        exit;
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /freshmart/admin/login.php');
        exit;
    }
}

function formatPrice($amount) {
    return CURRENCY . ' ' . number_format($amount, 0);
}

function generateOrderNumber() {
    return 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 6));
}
?>
