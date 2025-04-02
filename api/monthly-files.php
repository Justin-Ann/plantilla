<?php
require_once 'config.php';
header('Content-Type: application/json');
error_reporting(0);

$action = $_GET['action'] ?? 'list';

switch($action) {
    case 'list':
        try {
            $month = isset($_GET['month']) ? $mysqli->real_escape_string($_GET['month']) : null;
            
            $query = "SELECT * FROM monthly_files";
            if ($month) {
                $query .= " WHERE DATE_FORMAT(upload_date, '%Y-%m') = '$month'";
            }
            $query .= " ORDER BY upload_date DESC";
            
            $result = $mysqli->query($query);
            if (!$result) {
                throw new Exception($mysqli->error);
            }
            
            $files = [];
            while($row = $result->fetch_assoc()) {
                $files[] = [
                    'id' => $row['id'],
                    'filename' => $row['filename'],
                    'upload_date' => $row['upload_date'],
                    'last_edited' => $row['last_edited'],
                    'editor_name' => $row['editor_name']
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $files]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'upload':
        try {
            $month = $_POST['month'] ?? date('Y-m');
            $uploadedFile = $_FILES['file'] ?? null;

            if (!$uploadedFile) {
                throw new Exception('No file uploaded');
            }

            $filename = $mysqli->real_escape_string($uploadedFile['name']);
            $uploadDir = '../uploads/monthly/';
            $filePath = $uploadDir . basename($filename);

            if (!move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                throw new Exception('Failed to upload file');
            }

            $query = "INSERT INTO monthly_files (filename, file_path, upload_date, month_year) 
                     VALUES (?, ?, NOW(), ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('sss', $filename, $filePath, $month);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
}
?>
