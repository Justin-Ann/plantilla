<?php
require_once 'config.php';

// Add CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    // Verify database connection is active
    if (!$mysqli->ping()) {
        throw new Exception('Database connection lost');
    }

    $action = $_GET['action'] ?? 'list';
    
    switch($action) {
        case 'list':
            $query = "SELECT * FROM divisions ORDER BY name ASC";
            $result = $mysqli->query($query);
            
            if (!$result) {
                throw new Exception($mysqli->error);
            }
            
            $divisions = [];
            while($row = $result->fetch_assoc()) {
                $divisions[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'code' => $row['code']
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $divisions]);
            break;
            
        case 'records':
            $divisionId = $mysqli->real_escape_string($_GET['id']);
            $month = $mysqli->real_escape_string($_GET['month'] ?? date('Y-m'));
            
            $query = "SELECT * FROM raw_data 
                     WHERE division_id = ? 
                     AND DATE_FORMAT(upload_date, '%Y-%m') = ?";
            
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('is', $divisionId, $month);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $records = [];
            
            while($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $records]);
            break;
    }
} catch (Exception $e) {
    error_log("Division API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load divisions',
        'debug' => $e->getMessage()
    ]);
}
?>
