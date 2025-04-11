<?php
require '../config.php'; 
require '../mail_handler.php';

if (!isset($_GET['id'])) {
    echo json_encode(['message' => 'User ID is missing']);
    exit;
}

$user_id = intval($_GET['id']);

// Fetch user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['message' => 'User not found']);
    exit;
}

// Generate reset token
$token = bin2hex(random_bytes(16));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Store token in DB
$insert_stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
$insert_stmt->bind_param("iss", $user_id, $token, $expires);
$insert_stmt->execute();

// Create reset link
$reset_url = "http://localhost/plantilla/reset-password.php?token=$token";

// Send reset email
$success = send_password_reset_email($user['email'], $reset_url);

if ($success) {
    echo json_encode(['message' => 'Password reset link has been sent.']);
} else {
    echo json_encode(['message' => 'Failed to send reset email.']);
}
