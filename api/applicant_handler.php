<?php
require_once "../config.php";
require_once "../auth/auth.php";
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO applicants (
            id_no, fullname, last_name, first_name, middle_name, extname, mi,
            sex, position_title, item_number, techcode, level,
            appointment_status, sg, step, monthly_salary,
            date_of_birth, date_orig_appt, date_govt_srvc,
            date_last_promotion, date_last_increment, date_of_longevity
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssissdssssss", 
            $data['id_no'],
            $data['fullname'],
            $data['last_name'],
            $data['first_name'],
            $data['middle_name'],
            $data['extname'],
            $data['mi'],
            $data['sex'],
            $data['position_title'],
            $data['item_number'],
            $data['techcode'],
            $data['level'],
            $data['appointment_status'],
            $data['sg'],
            $data['step'],
            $data['monthly_salary'],
            $data['date_of_birth'],
            $data['date_orig_appt'],
            $data['date_govt_srvc'],
            $data['date_last_promotion'],
            $data['date_last_increment'],
            $data['date_of_longevity']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Applicant added successfully']);
        } else {
            throw new Exception($stmt->error);
        }
    } 
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $month = $_GET['month'] ?? null;
        $division = $_GET['division'] ?? null;
        
        if (isset($_GET['action']) && $_GET['action'] === 'get_divisions') {
            // Return list of divisions
            $sql = "SELECT id, name FROM divisions ORDER BY name";
            $result = $conn->query($sql);
            $divisions = [];
            while ($row = $result->fetch_assoc()) {
                $divisions[] = $row;
            }
            echo json_encode(['success' => true, 'divisions' => $divisions]);
            exit;
        }

        // Build base query
        $sql = "SELECT r.* FROM raw_data r 
                INNER JOIN uploaded_files f ON r.file_id = f.id 
                WHERE 1=1";
        $params = [];
        $types = "";

        // Add month filter
        if ($month) {
            $sql .= " AND DATE_FORMAT(f.upload_date, '%Y-%m') = ?";
            $params[] = $month;
            $types .= "s";
        }

        // Add division filter
        if ($division) {
            $sql .= " AND r.division_id = ?";
            $params[] = $division;
            $types .= "i";
        }

        // Add order by
        $sql .= " ORDER BY r.id_no";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $applicants = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates and currency
            $dateFields = ['date_of_birth', 'date_orig_appt', 'date_govt_srvc', 
                         'date_last_promotion', 'date_last_increment', 'date_of_longevity'];
            
            foreach ($dateFields as $field) {
                if (!empty($row[$field])) {
                    $row[$field] = date('Y-m-d', strtotime($row[$field]));
                }
            }
            
            if (!empty($row['monthly_salary'])) {
                $row['monthly_salary'] = number_format($row['monthly_salary'], 2);
            }
            
            $applicants[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $applicants]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
