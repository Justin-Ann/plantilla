<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'plantilla_db');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    error_log("Connection failed: " . $mysqli->connect_error);
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]));
}

$mysqli->set_charset("utf8mb4");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// For backward compatibility
$conn = $mysqli;
?>
