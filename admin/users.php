<?php
session_start();
require_once "../config.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Handle user management actions
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["action"])) {
        $user_id = $_POST["user_id"];
        
        switch($_POST["action"]) {
            case "delete":
                $sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
                break;
            case "reset_password":
                $temp_password = bin2hex(random_bytes(8));
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                // Send email with temporary password
                break;
            case "toggle_status":
                $sql = "UPDATE users SET active = NOT active WHERE id = ?";
                break;
        }
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Get users list
$users = [];
$sql = "SELECT id, username, email, role, email_verified, last_login, created_at FROM users WHERE role != 'admin'";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="admin-container">
        <h2>User Management</h2>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Verified</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user["username"]); ?></td>
                    <td><?php echo htmlspecialchars($user["email"]); ?></td>
                    <td><?php echo htmlspecialchars($user["role"]); ?></td>
                    <td><?php echo $user["email_verified"] ? "Yes" : "No"; ?></td>
                    <td><?php echo $user["last_login"] ? date("Y-m-d H:i", strtotime($user["last_login"])) : "Never"; ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                            <button type="submit" name="action" value="reset_password" class="btn btn-warning">Reset Password</button>
                            <button type="submit" name="action" value="toggle_status" class="btn btn-primary">Toggle Status</button>
                            <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
