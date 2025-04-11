<?php
require_once "../config.php";

$id = $_GET['id'];
$username = $email = "";
$username_err = $email_err = "";

// Fetch existing user
$sql = "SELECT username, email FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username, $email);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Update user on form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);

    // Validation can be added here...

    $update_sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($update_stmt, "ssi", $username, $email, $id);
        if (mysqli_stmt_execute($update_stmt)) {
            header("Location: users.php"); // Redirect after successful update
            exit;
        } else {
            echo "Error updating user.";
        }
        mysqli_stmt_close($update_stmt);
    }
}
?>

<!-- Edit Form -->
<h2>Edit User</h2>
<form method="post">
    <label>Username</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>

    <label>Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>

    <input type="submit" value="Update">
</form>
