<?php
session_start();
require_once "../config.php";
require_once "../auth_middleware.php";

// Check if user is admin
check_admin();

// Get user statistics
$sql = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) as verified_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count
FROM users";
$result = mysqli_query($conn, $sql);
$stats = mysqli_fetch_assoc($result);

// Get recent activities
$sql = "SELECT u.username, u.email, u.last_login, u.created_at 
        FROM users u 
        ORDER BY u.last_login DESC 
        LIMIT 10";
$activities = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Admin Dashboard</h2>
            
            <!-- User Statistics -->
            <div class="stats-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p class="count"><?php echo $stats['total_users']; ?></p>
                </div>
                <div class="card">
                    <h3>Verified Users</h3>
                    <p class="count"><?php echo $stats['verified_users']; ?></p>
                </div>
                <div class="card">
                    <h3>Admins</h3>
                    <p class="count"><?php echo $stats['admin_count']; ?></p>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="recent-activities">
                <h3>Recent User Activities</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Last Login</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($activity = mysqli_fetch_assoc($activities)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['username']); ?></td>
                            <td><?php echo htmlspecialchars($activity['email']); ?></td>
                            <td><?php echo $activity['last_login'] ? date('M d, Y H:i', strtotime($activity['last_login'])) : 'Never'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <a href="users.php" class="action-btn">Manage Users</a>
                    <a href="roles.php" class="action-btn">Manage Roles</a>
                    <a href="audit-log.php" class="action-btn">View Audit Log</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>
