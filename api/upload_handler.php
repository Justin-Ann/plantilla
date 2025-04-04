<?php
require_once "../config.php";
require_once "../auth/auth.php";

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    if (!isset($_FILES['file']) || !isset($_POST['month_year'])) {
        throw new Exception('Missing required parameters');
    }

    $file = $_FILES['file'];
    $monthYear = $_POST['month_year'];
    $userId = $_SESSION['user_id'];

    // Validate month_year format
    if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
        throw new Exception('Invalid month-year format');
    }

    // Validate file type
    $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only Excel and CSV files are allowed.');
    }

    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $uploadPath = "../uploads/" . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Insert file record
    $stmt = $conn->prepare("INSERT INTO uploaded_files (filename, original_filename, file_path, month_year, uploaded_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", 
        $filename,
        $file['name'],
        $uploadPath,
        $monthYear,
        $userId
    );

    if (!$stmt->execute()) {
        unlink($uploadPath); // Remove file if database insert fails
        throw new Exception('Failed to save file record: ' . $stmt->error);
    }

    $fileId = $stmt->insert_id;

    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file_id' => $fileId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
