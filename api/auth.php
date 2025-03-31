<?php
require_once 'config.php';
header('Content-Type: application/json');

function generateToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'login':
                // Login logic
                $email = $data['email'];
                $password = $data['password'];
                
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND email_verified = 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($user = $result->fetch_assoc()) {
                    if (password_verify($password, $user['password'])) {
                        session_start();
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        echo json_encode(['success' => true, 'role' => $user['role']]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
                    }
                }
                break;
                
            case 'register':
                // Registration logic with email verification
                $email = $data['email'];
                $password = password_hash($data['password'], PASSWORD_DEFAULT);
                $fullName = $data['full_name'];
                $token = generateToken();
                
                $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, verification_token) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $email, $password, $fullName, $token);
                
                if ($stmt->execute()) {
                    // Send verification email
                    sendVerificationEmail($email, $token);
                    echo json_encode(['success' => true, 'message' => 'Registration successful. Please verify your email.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Registration failed']);
                }
                break;

            case 'verify_email':
                $token = $data['token'];
                $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE verification_token = ?");
                $stmt->bind_param("s", $token);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
                }
                break;
        }
    }
}
?>
