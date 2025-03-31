<?php
session_start();
require_once "../config.php";
require_once "../auth_middleware.php";

check_admin();

function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Handle API routes
$action = $_GET['action'] ?? '';

switch($action) {
    case 'get-stats':
        $stats = get_dashboard_stats();
        send_json_response(['success' => true, 'stats' => $stats]);
        break;
        
    case 'update-role':
        $user_id = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? null;
        if ($user_id && $role) {
            $success = update_user_role($user_id, $role);
            send_json_response(['success' => $success]);
        }
        break;
        
    case 'reset-password':
        $user_id = $_POST['user_id'] ?? null;
        if ($user_id) {
            $result = reset_user_password($user_id);
            send_json_response($result);
        }
        break;
        
    case 'log-action':
        $action = $_POST['action'] ?? '';
        $details = $_POST['details'] ?? '';
        if ($action) {
            log_audit_action($_SESSION['id'], $action, $details);
            send_json_response(['success' => true]);
        }
        break;
        
    default:
        send_json_response(['success' => false, 'message' => 'Invalid action']);
}

function get_dashboard_stats() {
    global $conn;
    
    $stats = [
        'total_users' => 0,
        'active_users' => 0,
        'recent_activities' => 0
    ];
    
    // Get total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($row = $result->fetch_assoc()) {
        $stats['total_users'] = $row['count'];
    }
    
    return $stats;
}

function update_user_role($user_id, $role) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    return $stmt->execute();
}

function reset_user_password($user_id) {
    global $conn;
    // ... implementation
    return ['success' => true, 'message' => 'Password reset successful'];
}

function log_audit_action($user_id, $action, $details) {
    global $conn;
    // ... implementation
}
?>
