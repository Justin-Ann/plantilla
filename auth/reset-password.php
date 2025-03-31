<?php
require_once "../config.php";

$password = $confirm_password = "";
$password_err = $confirm_password_err = "";
$token = $_GET['token'] ?? '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if(empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if($password != $confirm_password) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    if(empty($password_err) && empty($confirm_password_err)) {
        $sql = "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $token);
            
            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    $update_sql = "UPDATE users SET password = ?, password_reset_token = NULL WHERE password_reset_token = ?";
                    if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $token);
                        
                        if(mysqli_stmt_execute($update_stmt)) {
                            header("location: ../login.php");
                            exit();
                        }
                    }
                } else {
                    echo "Invalid or expired reset token.";
                    exit();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Plantilla Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
        </div>
        <h2>Reset Password</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?token=" . htmlspecialchars($token); ?>" method="post">
            <div class="form-group">
                <input type="password" name="password" placeholder="New Password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Reset Password">
            </div>
        </form>
    </div>
</body>
</html>
