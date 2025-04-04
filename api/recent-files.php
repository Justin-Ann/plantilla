<?php
require_once '../config.php';
require_once '../auth/auth.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
    }

    // Simplified query to test functionality
    $query = "SELECT f.id, f.filename, f.upload_date, 
              COALESCE(u.full_name, 'System') as last_editor
              FROM uploaded_files f
              LEFT JOIN users u ON f.user_id = u.id
              ORDER BY f.upload_date DESC
              LIMIT 10";
              
    error_log("Executing query: " . $query);
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $files = [];
    while($row = $result->fetch_assoc()) {
        $files[] = [
            'id' => $row['id'],
            'filename' => $row['filename'],
            'last_edited' => $row['upload_date'],
            'last_editor' => $row['last_editor']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'files' => $files
    ]);
    
} catch(Exception $e) {
    error_log("Recent files API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recent files',
        'debug' => $e->getMessage()
    ]);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>
