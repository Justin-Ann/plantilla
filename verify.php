<?php
require_once "config.php";

if(empty($_GET['token'])) {
    header("location: login.php");
    exit;
}

$token = trim($_GET['token']);
$message = "";

try {
    $sql = "SELECT id, email_verified FROM users WHERE verification_token = ? AND verification_expires > NOW()";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $email_verified);
                mysqli_stmt_fetch($stmt);
                
                if(!$email_verified) {
                    // Update user verification status
                    $update_sql = "UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE id = ?";
                    if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                        mysqli_stmt_bind_param($update_stmt, "i", $id);
                        if(mysqli_stmt_execute($update_stmt)) {
                            $message = "Email verified successfully. You can now <a href='login.php'>login</a>.";
                        }
                    }
                } else {
                    $message = "Email already verified. Please <a href='login.php'>login</a>.";
                }
            } else {
                $message = "Invalid or expired verification token.";
            }
        }
    }
} catch(Exception $e) {
    $message = "Error occurred during verification.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="verification-container">
        <h2>Email Verification</h2>
        <div class="message"><?php echo $message; ?></div>
    </div>
</body>
</html>
