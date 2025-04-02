<?php
require_once "../config.php";
require_once "../auth/auth.php";

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Validate file upload
    if (!isset($_FILES['upload_file']) || $_FILES['upload_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['upload_file'];
    $monthYear = $_POST['upload_month'] ?? date('Y-m');
    
    // Validate month format
    if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
        throw new Exception('Invalid month format');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/monthly_files/' . $monthYear . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Validate file type
    $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only Excel files are allowed.');
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO monthly_files (
            filename,
            original_filename,
            file_path,
            month_year,
            uploaded_by,
            file_size,
            file_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'ssssiis',
        $filename,
        $file['name'],
        $filepath,
        $monthYear,
        $_SESSION['user_id'],
        $file['size'],
        $file['type']
    );

    if (!$stmt->execute()) {
        throw new Exception('Database insert failed: ' . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file_id' => $conn->insert_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
