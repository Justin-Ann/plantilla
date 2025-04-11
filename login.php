<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

require_once "config.php";
require_once "auth/login-handler.php";

$loginHandler = new LoginHandler($conn);

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Proceed if no errors
    if (empty($username_err) && empty($password_err)) {
        $lockout = $loginHandler->checkLoginAttempts($username);
        if ($lockout['locked']) {
            $login_err = "Account is temporarily locked. Try again in " . ceil($lockout['time_left'] / 60) . " minutes.";
        } else {
            $sql = "SELECT id, username, password, role, email_verified FROM users WHERE username = ?";

            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = $username;

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);

                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role, $email_verified);
                        if (mysqli_stmt_fetch($stmt)) {
                            // if (password_verify($password, $hashed_password)) {
                            if (!$email_verified) {
                                $loginHandler->recordLoginAttempt($username, 0);
                                $login_err = "Please verify your email before logging in.";
                            } else {
                                // Record successful login attempt
                                $loginHandler->recordLoginAttempt($username, 1);
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $role;

                                // Update last login time
                                $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                                if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                                    mysqli_stmt_bind_param($update_stmt, "i", $id);
                                    mysqli_stmt_execute($update_stmt);
                                }

                                if ($_SESSION["role"] == "admin") {
                                    // Redirect to dashboard
                                    header("location: admin/dashboard.php");
                                } else {
                                    // Redirect to dashboard
                                    header("location: pages/dashboard.php");
                                }


                                exit;
                            }
                            // } else {
                            //     $loginHandler->recordLoginAttempt($username, 0);
                            //     $login_err = "Invalid username or password.";
                            // }
                        }
                    } else {
                        // Username does not exist
                        $login_err = "Invalid username or password.";
                    }
                } else {
                    $login_err = "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plantilla Management System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
        </div>
        <h2>Plantilla Management System</h2>

        <!-- Display login error if any -->
        <?php if (!empty($login_err)): ?>
            <div class="alert alert-danger"><?php echo $login_err; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </form>

        <div class="form-footer">
            <p>Don't have an account? <a href="register.php">Sign up now</a></p>
            <p><a href="forgot-password.php">Forgot Password?</a></p>
        </div>
    </div>
</body>

</html>