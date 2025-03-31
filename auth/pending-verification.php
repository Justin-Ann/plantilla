<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Pending - Plantilla Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
        </div>
        <h2>Email Verification Required</h2>
        <div class="message">
            <p>We have sent a verification email to your registered email address.</p>
            <p>Please check your inbox and click the verification link to activate your account.</p>
            <p>If you haven't received the email, check your spam folder or <a href="resend-verification.php">click here to resend</a>.</p>
            <p><a href="../login.php">Return to login</a></p>
        </div>
    </div>
</body>
</html>
