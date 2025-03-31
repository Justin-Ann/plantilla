<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $query = "SELECT * FROM division_definitions ORDER BY division_order";
    $result = $conn->query($query);
    
    $divisions = [];
    while($row = $result->fetch_assoc()) {
        $divisions[] = $row;
    }
    
    echo json_encode($divisions);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching divisions'
    ]);
}
?>
