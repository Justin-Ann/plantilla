<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    // Verify database connection
    if (!$mysqli->ping()) {
        throw new Exception('Database connection lost');
    }

    // Get status counts
    $query = "SELECT 
        SUM(CASE WHEN status = 'On-Process' THEN 1 ELSE 0 END) as onProcess,
        SUM(CASE WHEN status = 'On-Hold' THEN 1 ELSE 0 END) as onHold,
        SUM(CASE WHEN status = 'Not Yet for Filling' THEN 1 ELSE 0 END) as notYetFilling
        FROM plantilla_records";

    $result = $mysqli->query($query);
    
    if (!$result) {
        throw new Exception($mysqli->error);
    }
    
    $counts = $result->fetch_assoc();
    
    // Convert null values to 0
    $counts = array_map(function($value) {
        return $value ?? 0;
    }, $counts);

    echo json_encode([
        'success' => true,
        'data' => $counts
    ]);

} catch (Exception $e) {
    error_log("Status Counts API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch status counts',
        'debug' => $e->getMessage()
    ]);
}
?>
