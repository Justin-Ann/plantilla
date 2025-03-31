<?php
// config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
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

// Create database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>