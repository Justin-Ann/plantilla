<?php
require_once "../config.php";

function sendVerificationEmail($email, $token) {
    $subject = "Verify your email - Plantilla Management System";
    $verification_link = "http://".$_SERVER['HTTP_HOST']."/HRIS/auth/verify.php?token=".$token;
    
    $message = "
    <html>
    <head>
        <title>Email Verification</title>
    </head>
    <body>
        <h2>Email Verification</h2>
        <p>Thank you for registering. Please click the link below to verify your email:</p>
        <p><a href='$verification_link'>$verification_link</a></p>
        <p>This link will expire in 24 hours.</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: PAGASA HRIS <noreply@pagasa.gov.ph>' . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

function verifyToken($token) {
    global $conn;
    
    $sql = "SELECT id, email_verified, verification_expires FROM users WHERE verification_token = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $email_verified, $expires);
                mysqli_stmt_fetch($stmt);
                
                if($email_verified) {
                    return "Email already verified.";
                }
                
                if(strtotime($expires) < time()) {
                    return "Verification link has expired.";
                }
                
                // Update user as verified
                $update_sql = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?";
                if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                    mysqli_stmt_bind_param($update_stmt, "i", $id);
                    if(mysqli_stmt_execute($update_stmt)) {
                        return "success";
                    }
                }
                mysqli_stmt_close($update_stmt);
            }
        }
        mysqli_stmt_close($stmt);
    }
    return "Invalid verification token.";
}
?>
