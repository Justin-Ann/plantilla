<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $query = "SELECT * FROM monthly_files ORDER BY upload_date DESC LIMIT 5";
    $result = $conn->query($query);
    
    $files = [];
    while($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
    
    echo json_encode($files);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
