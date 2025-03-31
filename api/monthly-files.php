<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch($action) {
    case 'list':
        try {
            $query = "SELECT * FROM monthly_files ORDER BY upload_date DESC";
            $result = $conn->query($query);
            
            $files = [];
            while($row = $result->fetch_assoc()) {
                $files[] = [
                    'id' => $row['id'],
                    'filename' => $row['filename'],
                    'upload_date' => $row['upload_date']
                ];
            }
            
            echo json_encode($files);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'upload':
        if(isset($_FILES['file'])) {
            $fileName = $_FILES['file']['name'];
            $targetPath = '../uploads/' . basename($fileName);
            
            if(move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO monthly_files (filename, file_path, uploaded_by) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $fileName, $targetPath, $_SESSION['user_id']);
                $stmt->execute();
                echo json_encode(['success' => true]);
            }
        }
        break;
}
?>
