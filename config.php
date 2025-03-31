<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'tequila');
define('DB_PASSWORD', '0225');
define('DB_NAME', 'plantilla_management');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');

// System configuration
define('VERIFY_EMAIL_EXPIRY', 24); // hours
define('PASSWORD_RESET_EXPIRY', 1); // hours
define('UPLOAD_PATH', __DIR__ . '/uploads');

// Create database connection
try {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if($conn === false) {
        throw new Exception("ERROR: Could not connect. " . mysqli_connect_error());
    }
} catch(Exception $e) {
    error_log($e->getMessage());
    exit("Database connection failed. Please check the configuration.");
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