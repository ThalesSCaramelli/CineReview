<?php
// ============================================
// DATABASE CONNECTION - CineReview
// Change these values if your WAMP setup is different
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'cinereview');
define('DB_USER', 'root');
define('DB_PASS', '');       // WAMP default is empty password

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
