<?php
session_start();
require_once "config.php";

// Check if already verified
if(isset($_SESSION["email_verified"]) && $_SESSION["email_verified"] === true) {
    header("location: dashboard.php");
    exit;
}

// Handle resend verification
if(isset($_POST["resend"])) {
    $sql = "SELECT email FROM users WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $email);
                if(mysqli_stmt_fetch($stmt)) {
                    // Generate new verification token
                    $verification_token = bin2hex(random_bytes(32));
                    $update_sql = "UPDATE users SET verification_token = ? WHERE id = ?";
                    if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                        mysqli_stmt_bind_param($update_stmt, "si", $verification_token, $_SESSION["id"]);
                        if(mysqli_stmt_execute($update_stmt)) {
                            $verification_url = "http://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . $verification_token;
                            if(send_verification_email($email, $verification_url)) {
                                $message = "Verification email has been resent.";
                            } else {
                                $error = "Error sending verification email.";
                            }
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification Required</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="verification-required-container">
        <h2>Email Verification Required</h2>
        <p>Please verify your email address to access all features.</p>
        
        <?php if(isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <button type="submit" name="resend" class="btn btn-primary">Resend Verification Email</button>
        </form>
        
        <div class="mt-3">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
