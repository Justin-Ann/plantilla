<?php
require_once 'config.php';
header('Content-Type: application/json');

// Ensure database connection is available
global $conn;

function sendVerificationEmail($userEmail, $token) {
    global $conn;
    if (!$conn) return false;
    
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1, status = 'active' WHERE verification_token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        return $stmt->execute();
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {  // Fixed syntax error
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
                try {
                    if (empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
                        throw new Exception('All fields are required');
                    }

                    $userEmail = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
                    if (!$userEmail) {
                        throw new Exception('Invalid email format');
                    }

                    // Check existing email
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    if (!$stmt) {
                        throw new Exception($conn->error);
                    }
                    
                    $stmt->bind_param("s", $userEmail);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        throw new Exception('Email already registered');
                    }

                    // Create new user
                    $password = password_hash($data['password'], PASSWORD_DEFAULT);
                    $fullName = $data['full_name'];
                    $token = bin2hex(random_bytes(32));
                    $status = 'active';
                    $emailVerified = 1;

                    $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, verification_token, status, email_verified) VALUES (?, ?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception($conn->error);
                    }

                    $stmt->bind_param("sssssi", $userEmail, $password, $fullName, $token, $status, $emailVerified);
                    
                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }

                    sendVerificationEmail($userEmail, $token);

                    echo json_encode([
                        'success' => true,
                        'message' => 'Registration successful! You can now login.'
                    ]);

                } catch (Exception $e) {
                    error_log("Registration error: " . $e->getMessage());
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
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
