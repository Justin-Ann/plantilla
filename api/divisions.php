<?php
require_once '../config.php';
require_once '../auth/auth.php';

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

switch($_GET['action'] ?? '') {
    case 'get_data':
        $division = $_GET['division'] ?? null;
        $month = $_GET['month'] ?? null;
        
        if(!$division) {
            echo json_encode(['success' => false, 'message' => 'Division code required']);
            exit;
        }

        $sql = "SELECT * FROM raw_data WHERE plantilla_division_definition = ?";
        if($month) {
            $sql .= " AND DATE_FORMAT(upload_date, '%Y-%m') = ?";
        }

        $stmt = $conn->prepare($sql);
        
        if($month) {
            $stmt->bind_param('ss', $division, $month);
        } else {
            $stmt->bind_param('s', $division);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'get_divisions':
        try {
            $query = "SELECT id, code, name FROM divisions ORDER BY code";
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception($conn->error);
            }
            
            $divisions = [];
            while ($row = $result->fetch_assoc()) {
                $divisions[] = [
                    'id' => $row['id'],
                    'code' => $row['code'],
                    'name' => $row['name']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'divisions' => $divisions
            ]);
            
        } catch (Exception $e) {
            error_log("Divisions API error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching divisions: ' . $e->getMessage()
            ]);
        }
        break;
        
    // ... other actions
}
?>
