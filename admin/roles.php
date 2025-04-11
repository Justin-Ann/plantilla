<?php
session_start();
require_once "../config.php";
require_once "../auth_middleware.php";

check_admin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "add_role":
                $name = $_POST["name"];
                $description = $_POST["description"];
                $permissions = json_encode($_POST["permissions"] ?? []);

                $sql = "INSERT INTO roles (name, description, permissions) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sss", $name, $description, $permissions);
                mysqli_stmt_execute($stmt);
                break;

            case "delete_role":
                $role_id = $_POST["role_id"];
                $sql = "DELETE FROM roles WHERE id = ? AND name != 'admin'";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $role_id);
                mysqli_stmt_execute($stmt);
                break;
        }
    }
}

$roles = [];
$sql = "SELECT * FROM roles ORDER BY name";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $roles[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Role Management</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="container">
        <?php include 'admin_sidebar.php'; ?>

        <div class="main-content">
            <h2>Role Management</h2>

            <!-- Add Role Form -->
            <div class="add-role-form">
                <h3>Add New Role</h3>
                <form method="post">
                    <input type="hidden" name="action" value="add_role">
                    <div class="form-group">
                        <label>Role Name:</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Permissions:</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="permissions[]" value="read"> Read</label>
                            <label><input type="checkbox" name="permissions[]" value="write"> Write</label>
                            <label><input type="checkbox" name="permissions[]" value="delete"> Delete</label>
                        </div>
                    </div>
                    <button type="submit" class="action-btn">Add Role</button>
                </form>
            </div>

            <!-- Roles List -->
            <div class="roles-list">
                <h3>Existing Roles</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($role["name"]); ?></td>
                                <td><?php echo htmlspecialchars($role["description"]); ?></td>
                                <td>
                                    <?php
                                    $perms = json_decode($role["permissions"], true);
                                    $permission_labels = [
                                        'read' => 'Read',
                                        'write' => 'Write',
                                        'delete' => 'Delete'
                                    ];

                                    $display_permissions = array_map(function ($perm) use ($permission_labels) {
                                        return $permission_labels[$perm] ?? '';
                                    }, $perms);

                                    echo implode(", ", array_filter($display_permissions));
                                    ?>
                                </td>
                                <td>
                                    <?php if ($role["name"] !== "admin"): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_role">
                                            <input type="hidden" name="role_id" value="<?php echo $role["id"]; ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</body>

</html>