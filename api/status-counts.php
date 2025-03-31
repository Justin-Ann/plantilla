<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $query = "SELECT status, COUNT(*) as count FROM status_tracking GROUP BY status";
    $result = $conn->query($query);
    
    $counts = [
        'onProcess' => 0,
        'onHold' => 0,
        'notYetFilling' => 0
    ];

    while($row = $result->fetch_assoc()) {
        switch($row['status']) {
            case 'On-Process': $counts['onProcess'] = (int)$row['count']; break;
            case 'On-Hold': $counts['onHold'] = (int)$row['count']; break;
            case 'Not Yet for Filling': $counts['notYetFilling'] = (int)$row['count']; break;
        }
    }
    
    echo json_encode($counts);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
