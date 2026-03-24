<?php
// ============================================
// db_connect.php — Dedicated DB Connection
// ============================================
// This file establishes the connection between
// the server-side scripts and the MySQL database.
// Include this file in any page that needs DB access.
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your DB username
define('DB_PASS', '');           // Change to your DB password
define('DB_NAME', 'freshmart');

// Create the MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection — show a clear error if it fails
if ($conn->connect_error) {
    die('
        <div style="font-family:sans-serif;padding:40px;background:#fff0f0;
                    color:#c00;border-left:4px solid #c00;margin:40px;">
            <h2>⚠️ Database Connection Failed</h2>
            <p><strong>Error:</strong> ' . $conn->connect_error . '</p>
            <p>
                <strong>How to fix:</strong><br>
                1. Make sure XAMPP MySQL is running.<br>
                2. Confirm your DB credentials in <code>db_connect.php</code>.<br>
                3. Ensure you have imported <code>freshmart.sql</code> via phpMyAdmin.
            </p>
        </div>
    ');
}

// Use UTF-8 encoding for all queries
$conn->set_charset('utf8mb4');
?>
