<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch($action) {
    case 'list':
        $filters = [];
        if (isset($_GET['month'])) $filters[] = "MONTH(date_orig_appt) = " . intval($_GET['month']);
        if (isset($_GET['division'])) $filters[] = "division_id = " . intval($_GET['division']);
        
        $where = count($filters) > 0 ? "WHERE " . implode(" AND ", $filters) : "";
        
        $query = "SELECT * FROM applicant_records $where";
        $result = $conn->query($query);
        $records = [];
        while($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        echo json_encode($records);
        break;

    case 'search':
        $search = $conn->real_escape_string($_GET['term']);
        $query = "SELECT * FROM applicant_records WHERE 
                 full_name LIKE '%$search%' OR 
                 last_name LIKE '%$search%' OR 
                 first_name LIKE '%$search%'";
        $result = $conn->query($query);
        $records = [];
        while($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        echo json_encode($records);
        break;

    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        unset($data['id']);
        
        $sets = [];
        foreach($data as $key => $value) {
            $sets[] = "$key = '" . $conn->real_escape_string($value) . "'";
        }
        
        $query = "UPDATE applicant_records SET " . implode(", ", $sets) . " WHERE id = $id";
        $success = $conn->query($query);
        echo json_encode(['success' => $success]);
        break;
}
?>
