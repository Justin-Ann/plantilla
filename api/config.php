<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'plantilla_db');

// Create connection and make it globally accessible
global $conn;
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]));
}

mysqli_set_charset($conn, "utf8mb4");
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
