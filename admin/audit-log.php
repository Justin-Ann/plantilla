<?php
session_start();
require_once "../config.php";
require_once "../auth_middleware.php";

check_admin();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

$sql = "SELECT al.*, u.username 
        FROM audit_log al 
        LEFT JOIN users u ON al.user_id = u.id 
        ORDER BY al.timestamp DESC 
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
mysqli_stmt_execute($stmt);
$logs = mysqli_stmt_get_result($stmt);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM audit_log";
$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Log</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="main-content">
            <h2>Audit Log</h2>
            
            <div class="audit-log-filters">
                <input type="text" id="search-log" placeholder="Search audit log...">
                <select id="action-filter">
                    <option value="">All Actions</option>
                    <option value="login">Login</option>
                    <option value="data_edit">Data Edit</option>
                    <option value="file_upload">File Upload</option>
                    <option value="user_management">User Management</option>
                </select>
            </div>
            
            <div class="audit-log-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($log = mysqli_fetch_assoc($logs)): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['timestamp'])); ?></td>
                            <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['details']); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="../js/audit-log.js"></script>
</body>
</html>
