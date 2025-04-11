<?php
session_start(); // Required to access $_SESSION

require_once "../config.php";  // Database connection
require_once "../PHPMailer/PHPMailerAutoload.php";  // For email verification, if necessary

function uploadFileWithMonth($file, $monthYear, $userId)
{
    global $conn; // ✅ Access $conn from config.php

    // Directory where the files will be stored
    $targetDir = "../uploads/";

    // Generate a unique filename for the uploaded file
    $filename = uniqid() . '_' . basename($file['name']);
    $targetFile = $targetDir . $filename;

    // Allowed file types (adjust as needed)
    $allowedFileTypes = ['xlsx', 'xls', 'csv'];

    // Get the file extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check if the file type is allowed
    if (!in_array($fileExtension, $allowedFileTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only XLSX, XLS, and CSV files are allowed.'];
    }

    // Check file size (max 5MB in this case)
    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'File size is too large.'];
    }

    // Attempt to move the uploaded file to the server
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Insert file metadata into the database
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

        // Insert into file_history to track the action
        $action = 'Created';  // Action when file is uploaded
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for necessary fields
    if (isset($_FILES['file-upload']) && isset($_POST['month_year']) && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];  // Assuming the user is logged in
        $monthYear = $_POST['month_year'];
        $file = $_FILES['file-upload'];

        // Call the upload function
        $response = uploadFileWithMonth($file, $monthYear, $userId);
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request. Missing file, month, or user.']);
    }
}
