<?php
require_once "../config.php";
require_once "../auth/auth.php";

header('Content-Type: application/json');

function getStatusCounts() {
    global $conn;
    
    $sql = "SELECT 
        SUM(CASE WHEN status = 'On-Process' THEN 1 ELSE 0 END) as on_process,
        SUM(CASE WHEN status = 'On-Hold' THEN 1 ELSE 0 END) as on_hold,
        SUM(CASE WHEN status = 'Not Yet for Filling' THEN 1 ELSE 0 END) as not_yet_filling
        FROM plantilla_records";
        
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'status-counts':
        $query = "SELECT 
            SUM(CASE WHEN status = 'On-Process' THEN 1 ELSE 0 END) as onProcess,
            SUM(CASE WHEN status = 'On-Hold' THEN 1 ELSE 0 END) as onHold,
            SUM(CASE WHEN status = 'Not Yet for Filling' THEN 1 ELSE 0 END) as notYetFilling
            FROM plantilla_records";
        
        $result = $conn->query($query);
        $counts = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $counts]);
        break;

    case 'monthly-files':
        $month = $_GET['month'] ?? date('Y-m');
        $query = "SELECT * FROM monthly_files 
                 WHERE DATE_FORMAT(upload_date, '%Y-%m') = ? 
                 ORDER BY upload_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $files = [];
        while($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $files]);
        break;

    case 'recent-files':
        $query = "SELECT * FROM monthly_files 
                 ORDER BY last_modified DESC 
                 LIMIT 5";
        $result = $conn->query($query);
        $files = [];
        while($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $files]);
        break;
}
