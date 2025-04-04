<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');  // Changed to default XAMPP username
define('DB_PASSWORD', '');      // Changed to empty password for default XAMPP setup
define('DB_NAME', 'plantilla_management');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'francistengteng10@gmail.com'); // Replace with your Gmail
define('SMTP_PASS', 'biic qznn xrah kxer');    // Replace with Gmail App Password
define('SMTP_FROM', 'francistengteng10@gmail.com'); // Replace with your Gmail
define('SMTP_FROM_NAME', 'PAGASA HRIS');

// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-error.log');

// System configuration
define('VERIFY_EMAIL_EXPIRY', 24); // hours
define('PASSWORD_RESET_EXPIRY', 1); // hours
define('UPLOAD_PATH', __DIR__ . '/uploads');

// Create database connection
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if($conn->connect_error) {
        throw new Exception("ERROR: Could not connect. " . $conn->connect_error);
    }
} catch(Exception $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Division status options
define('STATUS_OPTIONS', [
    'On-process',
    'On-hold',
    'Not yet for filling'
]);

// Appointment status options
define('APPOINTMENT_STATUS_OPTIONS', [
    'PERMANENT',
    'TEMPORARY'
]);

// Division codes (add all 43 divisions)
define('DIVISIONS', [
    1 => 'Office of the Administrator',
    2 => 'Administrative Division',
    // ... Add all divisions ...
    43 => 'Field Stations'
]);
?>