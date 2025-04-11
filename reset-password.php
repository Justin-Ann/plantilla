<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $check_sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()";
    if ($stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id);
            mysqli_stmt_fetch($stmt);

            $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
            if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                mysqli_stmt_bind_param($update_stmt, "si", $new_password, $id);
                mysqli_stmt_execute($update_stmt);
                echo "Password has been reset. You can now <a href='login.php'>login</a>.";
            }
        } else {
            echo "Invalid or expired token.";
        }
    }
}
?>

<?php if (!empty($token)): ?>
    <form method="POST">
        <input type="password" name="password" placeholder="New password" required>
        <button type="submit">Reset Password</button>
    </form>
<?php else: ?>
    <p>Invalid request.</p>
<?php endif; ?>