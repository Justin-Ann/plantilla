<?php
require_once "../config.php";

$email = $email_err = "";
$success_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST["email"]);
        
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ? AND email_verified = 1";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    // Generate reset token
                    $reset_token = bin2hex(random_bytes(32));
                    $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    $update_sql = "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?";
                    if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                        mysqli_stmt_bind_param($update_stmt, "sss", $reset_token, $token_expiry, $email);
                        
                        if(mysqli_stmt_execute($update_stmt)) {
                            // Send reset email
                            $reset_link = "http://".$_SERVER['HTTP_HOST']."/HRIS/auth/reset-password.php?token=".$reset_token;
                            $to = $email;
                            $subject = "Password Reset Request";
                            $message = "Click the following link to reset your password: " . $reset_link;
                            $headers = "From: noreply@pagasa.gov.ph";
                            
                            if(mail($to, $subject, $message, $headers)) {
                                $success_msg = "Password reset instructions have been sent to your email.";
                            } else {
                                $email_err = "Error sending reset email.";
                            }
                        }
                    }
                } else {
                    $email_err = "No verified account found with this email address.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Plantilla Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
        </div>
        <h2>Reset Password</h2>
        <?php 
        if(!empty($success_msg)) {
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Reset Password">
            </div>
            <p><a href="../login.php">Return to login</a></p>
        </form>
    </div>
</body>
</html>
