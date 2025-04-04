<?php
require_once '../config.php';
require_once '../auth/auth.php';

// Proper CORS and content headers
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

// Modern cache control
header('Cache-Control: no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    $query = "SELECT f.*, u.full_name as uploaded_by, 
              (SELECT COUNT(*) FROM file_history WHERE file_id = f.id) as edit_count,
              (SELECT users.full_name 
               FROM file_history fh 
               JOIN users ON users.id = fh.user_id 
               WHERE fh.file_id = f.id 
               ORDER BY fh.timestamp DESC LIMIT 1) as last_editor
              FROM uploaded_files f
              LEFT JOIN users u ON f.user_id = u.id
              WHERE DATE_FORMAT(f.upload_date, '%Y-%m') = ?
              ORDER BY f.upload_date DESC";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $month);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $files = [];
    
    while($row = $result->fetch_assoc()) {
        $files[] = [
            'id' => $row['id'],
            'filename' => $row['filename'],
            'upload_date' => $row['upload_date'],
            'uploaded_by' => $row['uploaded_by'],
            'edit_count' => $row['edit_count'],
            'last_editor' => $row['last_editor']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'files' => $files
    ]);
} catch(Exception $e) {
    error_log("Monthly files error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching monthly files: ' . $e->getMessage()
    ]);
}
?>
