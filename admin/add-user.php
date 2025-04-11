<?php
require_once "../config.php";
require_once "../mail_handler.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Generate verification token
    $verification_token = bin2hex(random_bytes(16));
    $verification_expires = date("Y-m-d H:i:s", strtotime('+1 day'));

    // Prepare SQL to insert user
    $sql = "INSERT INTO users (username, email, password, verification_token, verification_expires) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $password, $verification_token, $verification_expires);

        if (mysqli_stmt_execute($stmt)) {
            // Generate verification URL
            $verification_url = "http://localhost/plantilla/verify.php?token=" . $verification_token;

            // Send email verification
            send_verification_email($email, $verification_url);

            // Redirect to another page after adding the user
            header("Location: users.php"); // Change to your desired page
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>

<!-- HTML Form to Add User -->
<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Add User</button>
</form>
