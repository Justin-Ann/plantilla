<?php
require_once '../config.php';
require_once '../auth/auth.php';

header('Content-Type: application/json');

try {
    $query = "SELECT status, COUNT(*) as count 
              FROM status_tracking 
              GROUP BY status";
              
    $result = $conn->query($query);
    
    $counts = [
        'On-Process' => 0,
        'On-Hold' => 0,
        'Not Yet for Filing' => 0
    ];
    
    while($row = $result->fetch_assoc()) {
        $counts[$row['status']] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'counts' => $counts
    ]);
} catch(Exception $e) {
    error_log("Status count error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching status counts'
    ]);
}
?>
