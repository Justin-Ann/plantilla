<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch($action) {
    case 'list':
        $query = "SELECT mf.*, u.full_name as uploaded_by_name 
                 FROM monthly_files mf 
                 JOIN users u ON mf.uploaded_by = u.id 
                 ORDER BY upload_date DESC";
        $result = $conn->query($query);
        $files = [];
        while($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        echo json_encode($files);
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
