<?php
require_once 'config.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'list_users':
        $query = "SELECT id, email, full_name, role, email_verified, created_at FROM users";
        $result = $conn->query($query);
        $users = [];
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
        break;

    case 'update_user':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("UPDATE users SET role = ?, email_verified = ? WHERE id = ?");
        $stmt->bind_param("sii", $data['role'], $data['email_verified'], $data['id']);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        break;

    case 'reset_password':
        $userId = $_POST['user_id'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $newPassword, $userId);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        break;
}
?>
