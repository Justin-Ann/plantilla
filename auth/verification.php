<?php
require_once "../config.php";
require '../vendor/autoload.php'; // Add PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $token) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $verification_link = "http://".$_SERVER['HTTP_HOST']."/HRIS/auth/verify.php?token=".$token;
        
        $mail->isHTML(true);
        $mail->Subject = "Verify your email - PAGASA Plantilla System";
        $mail->Body = "
            <h2>Email Verification - PAGASA Plantilla System</h2>
            <p>Thank you for registering. Please click the link below to verify your email:</p>
            <p><a href='$verification_link'>$verification_link</a></p>
            <p>This link will expire in 24 hours.</p>
            <p>If you did not register for this account, please ignore this email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
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
