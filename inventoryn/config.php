<?php
// ================================================
// config.php  –  Database Connection
// ================================================
// Change these values to match YOUR server settings.
// ================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'inventry_management_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;background:#fee2e2;color:#b91c1c;padding:20px;border-radius:10px;margin:20px;'>
        <strong>⚠️ Database Connection Failed!</strong><br>
        Error: " . htmlspecialchars($conn->connect_error) . "<br><br>
        Please check your database credentials in <code>config.php</code>
    </div>");
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
