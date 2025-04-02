<?php
// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'tequila');
define('DB_PASS', '0225');
define('DB_NAME', 'hris_db');

// Create connection with error handling
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    // Set charset to utf8mb4
    if (!$mysqli->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $mysqli->error);
    }

    // Test connection
    if (!$mysqli->ping()) {
        throw new Exception("Error: Database server has gone away");
    }
    
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'debug' => $e->getMessage()
    ]));
}

// For backwards compatibility
$conn = $mysqli;
?>
