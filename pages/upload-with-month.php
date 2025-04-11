<?php
session_start();

require_once "../config.php";

function uploadFileWithMonth($file, $monthYear, $userId)
{
    global $conn;
    $targetDir = "../uploads/";
    $filename = uniqid() . '_' . basename($file['name']);
    $targetFile = $targetDir . $filename;
    $allowedFileTypes = ['xlsx', 'xls', 'csv'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedFileTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only XLSX, XLS, and CSV files are allowed.'];
    }

    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File size is too large.'];
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $sql = "INSERT INTO uploaded_files (filename, original_filename, file_path, month_year, uploaded_by) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Error preparing SQL statement: ' . mysqli_error($conn)];
        }
        mysqli_stmt_bind_param($stmt, "ssssi", $filename, $file['name'], $targetFile, $monthYear, $userId);
        if (!mysqli_stmt_execute($stmt)) {
            return ['success' => false, 'message' => 'Error inserting file data into the database: ' . mysqli_error($conn)];
        }

        $fileId = mysqli_insert_id($conn);
        $action = 'Created';
        $historySql = "INSERT INTO file_history (file_id, action, user_id) VALUES (?, ?, ?)";
        $historyStmt = mysqli_prepare($conn, $historySql);
        if ($historyStmt === false) {
            return ['success' => false, 'message' => 'Error preparing history SQL statement: ' . mysqli_error($conn)];
        }
        mysqli_stmt_bind_param($historyStmt, "isi", $fileId, $action, $userId);
        if (!mysqli_stmt_execute($historyStmt)) {
            return ['success' => false, 'message' => 'Error inserting file history: ' . mysqli_error($conn)];
        }

        return ['success' => true, 'message' => 'File uploaded successfully!'];
    } else {
        return ['success' => false, 'message' => 'File upload failed.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file-upload']) && isset($_POST['month_year']) && isset($_SESSION['id'])) {
        $userId = $_SESSION['id'];  // Assuming the user is logged in
        $monthYear = $_POST['month_year'];
        $file = $_FILES['file-upload'];
        $response = uploadFileWithMonth($file, $monthYear, $userId);
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request. Missing file, month, or user.']);
    }
}
