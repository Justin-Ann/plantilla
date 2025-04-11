<?php
require_once "config.php";
require_once "mail_handler.php";

$email = $email_err = "";
$message = "";
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);

        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                $token = bin2hex(random_bytes(16));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $update_sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
                if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                    mysqli_stmt_bind_param($update_stmt, "sss", $token, $expires, $email);
                    if (mysqli_stmt_execute($update_stmt)) {
                        $reset_url = "http://" . $_SERVER['HTTP_HOST'] . "/plantilla/reset-password.php?token=" . $token;
                        if (send_password_reset_email($email, $reset_url)) {
                            $message = "A reset link has been sent to your email.";
                            echo $message;
                        } else {
                            $message = "Failed to send reset email.";
                            echo $message;
                        }
                    }
                }
            } else {
                $email_err = "Email not found.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>