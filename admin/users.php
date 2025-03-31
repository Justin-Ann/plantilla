<?php
session_start();
require_once "../config.php";
require_once "../auth_middleware.php";

function send_password_reset_email($email, $temp_password) {
    $to = $email;
    $subject = "Password Reset";
    $message = "Your temporary password is: " . $temp_password . "\n\n";
    $message .= "Please login and change your password as soon as possible.";
    $headers = "From: noreply@yourdomain.com";

    mail($to, $subject, $message, $headers);
}

check_admin();

// Handle user detail updates
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["action"])) {
        $user_id = $_POST["user_id"];
        
        switch($_POST["action"]) {
            case "update":
                $username = trim($_POST["username"]);
                $email = trim($_POST["email"]);
                $role = trim($_POST["role"]);
                
                $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $role, $user_id);
                mysqli_stmt_execute($stmt);
                break;
                
            case "reset_password":
                $temp_password = bin2hex(random_bytes(8));
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                
                $sql = "UPDATE users SET password = ?, password_reset_token = NULL WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    // Get user email
                    $email_sql = "SELECT email FROM users WHERE id = ?";
                    $email_stmt = mysqli_prepare($conn, $email_sql);
                    mysqli_stmt_bind_param($email_stmt, "i", $user_id);
                    mysqli_stmt_execute($email_stmt);
                    mysqli_stmt_bind_result($email_stmt, $email);
                    mysqli_stmt_fetch($email_stmt);
                    
                    // Send email with new password
                    send_password_reset_email($email, $temp_password);
                }
                break;
                
            case "deactivate":
                $sql = "UPDATE users SET active = 0 WHERE id = ? AND role != 'admin'";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                break;
        }
    }
}

// Get users list with additional details
$users = [];
$sql = "SELECT id, username, email, role, email_verified, active, last_login, created_at FROM users WHERE role != 'admin'";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <h2>User Management</h2>
            
            <div class="user-filters">
                <input type="text" id="search-users" placeholder="Search users...">
                <select id="role-filter">
                    <option value="">All Roles</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                </select>
                <select id="status-filter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user["username"]); ?></td>
                        <td><?php echo htmlspecialchars($user["email"]); ?></td>
                        <td>
                            <select class="role-select" data-user-id="<?php echo $user["id"]; ?>">
                                <option value="editor" <?php echo $user["role"] === "editor" ? "selected" : ""; ?>>Editor</option>
                                <option value="viewer" <?php echo $user["role"] === "viewer" ? "selected" : ""; ?>>Viewer</option>
                            </select>
                        </td>
                        <td><?php echo $user["active"] ? "Active" : "Inactive"; ?></td>
                        <td><?php echo $user["last_login"] ? date("Y-m-d H:i", strtotime($user["last_login"])) : "Never"; ?></td>
                        <td>
                            <button onclick="editUser(<?php echo $user['id']; ?>)" class="btn btn-primary">Edit</button>
                            <button onclick="resetPassword(<?php echo $user['id']; ?>)" class="btn btn-warning">Reset Password</button>
                            <button onclick="toggleStatus(<?php echo $user['id']; ?>)" class="btn btn-danger">
                                <?php echo $user["active"] ? "Deactivate" : "Activate"; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit User</h3>
            <form id="edit-user-form">
                <input type="hidden" id="edit-user-id">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" id="edit-username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select id="edit-role">
                        <option value="editor">Editor</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
