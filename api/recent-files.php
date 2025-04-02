<?php
require_once 'config.php';
header('Content-Type: application/json');
error_reporting(0);

try {
    $query = "SELECT * FROM monthly_files ORDER BY upload_date DESC LIMIT 5";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $files = [];
    while($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $files]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
