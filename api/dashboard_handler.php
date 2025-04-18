<?php
require_once "../config.php";
require_once "../auth/auth.php";

header('Content-Type: application/json');

try {
    switch ($_GET['action'] ?? '') {
        case 'status-counts':
            $sql = "SELECT status, COUNT(*) as count FROM raw_data WHERE is_latest = 1 GROUP BY status";
            $result = $conn->query($sql);
            $counts = [
                'On-process' => 0,
                'On-hold' => 0,
                'Not yet for filling' => 0
            ];

            while ($row = $result->fetch_assoc()) {
                $counts[$row['status']] = (int)$row['count'];
            }

            echo json_encode(['success' => true, 'counts' => $counts]);
            break;

        case 'monthly-files':
            $month = $_GET['month'] ?? date('Y-m');
            $sql = "SELECT f.*, u.username as uploaded_by_name 
                   FROM uploaded_files f 
                   LEFT JOIN users u ON f.uploaded_by = u.id 
                   WHERE DATE_FORMAT(f.upload_date, '%Y-%m') = ?
                   ORDER BY f.upload_date DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $month);
            $stmt->execute();
            $result = $stmt->get_result();

            $files = [];
            while ($row = $result->fetch_assoc()) {
                $files[] = [
                    'id' => $row['id'],
                    'filename' => $row['original_filename'],
                    'upload_date' => $row['upload_date'],
                    'uploaded_by' => $row['uploaded_by_name'],
                    'last_modified' => $row['last_modified']
                ];
            }

            echo json_encode(['success' => true, 'files' => $files]);
            break;

        case 'recent-files':
            $sql = "SELECT f.*, u.username as last_editor
                   FROM uploaded_files f
                   LEFT JOIN users u ON f.modified_by = u.id
                   ORDER BY f.last_modified DESC
                   LIMIT 10";

            $result = $conn->query($sql);
            $files = [];
            while ($row = $result->fetch_assoc()) {
                $files[] = [
                    'id' => $row['id'],
                    'filename' => $row['original_filename'],
                    'last_modified' => $row['last_modified'],
                    'last_editor' => $row['last_editor']
                ];
            }

            echo json_encode(['success' => true, 'files' => $files]);
            break;

        case 'open-file':
            $fileId = $_GET['id'] ?? null;

            if (!$fileId) {
                throw new Exception('File ID is required');
            }

            $sql = "SELECT * FROM uploaded_files WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $fileId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $file = $result->fetch_assoc();
                $filePath = $file['file_path'];

                if (!file_exists($filePath)) {
                    throw new Exception('File does not exist');
                }

                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                switch (strtolower($fileExtension)) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        header('Content-Type: image/' . $fileExtension);
                        readfile($filePath);
                        break;
                    case 'pdf':
                        header('Content-Type: application/pdf');
                        readfile($filePath);
                        break;
                    case 'txt':
                        header('Content-Type: text/plain');
                        readfile($filePath);
                        break;
                    case 'html':
                        header('Content-Type: text/html');
                        readfile($filePath);
                        break;
                    default:
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                        readfile($filePath);
                        break;
                }

                exit();
            } else {
                throw new Exception('File not found');
            }

        case 'upload':
            if (!isset($_FILES['file-upload'])) {
                throw new Exception('No file uploaded');
            }

            $month_year = $_POST['month_year'] ?? date('Y-m');
            $file = $_FILES['file-upload'];
            $filename = uniqid() . '_' . basename($file['name']);
            $target_path = "../uploads/" . $filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $sql = "INSERT INTO uploaded_files (filename, original_filename, file_path, upload_date, month_year, uploaded_by)
                        VALUES (?, ?, ?, NOW(), ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "ssssi",
                    $filename,
                    $file['name'],
                    $target_path,
                    $month_year,
                    $_SESSION['user_id']
                );

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
                } else {
                    throw new Exception('Failed to save file record');
                }
            } else {
                throw new Exception('Failed to move uploaded file');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
