<?php
require_once "../config.php";
require_once "verification.php";

$email = $email_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST["email"]);
        
        // Check if email exists and is unverified
        $sql = "SELECT id, email_verified FROM users WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $email_verified);
                    mysqli_stmt_fetch($stmt);
                    
                    if($email_verified) {
                        $email_err = "This email is already verified.";
                    } else {
                        // Generate new verification token
                        $verification_token = bin2hex(random_bytes(32));
                        $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                        
                        $update_sql = "UPDATE users SET verification_token = ?, verification_expires = ? WHERE id = ?";
                        if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                            mysqli_stmt_bind_param($update_stmt, "ssi", $verification_token, $token_expiry, $id);
                            
                            if(mysqli_stmt_execute($update_stmt) && sendVerificationEmail($email, $verification_token)) {
                                header("location: pending-verification.php");
                                exit();
                            } else {
                                $email_err = "Error sending verification email.";
                            }
                        }
                    }
                } else {
                    $email_err = "No account found with this email address.";
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
    <title>Resend Verification - Plantilla Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
        </div>
        <h2>Resend Verification Email</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Resend Verification">
            </div>
            <p><a href="../login.php">Return to login</a></p>
        </form>
    </div>
</body>
</html>
