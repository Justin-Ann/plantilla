<?php
require_once '../config.php';
require_once '../auth/auth.php';

if (isset($_GET['division_code'])) {
    $division_code = $_GET['division_code'];

    $query = "SELECT filename, original_filename, file_path, upload_date, status 
              FROM uploaded_files 
              WHERE division_code = ? AND status = 'completed'";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $division_code);
        $stmt->execute();
        $result = $stmt->get_result();

        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }

        echo json_encode($files);
    } else {
        echo json_encode(['error' => 'Failed to retrieve files']);
    }
} else {
    echo json_encode(['error' => 'Division code not provided']);
}
?>