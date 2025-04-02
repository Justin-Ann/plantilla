<?php
require_once "../config.php";
session_start();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $_GET['action'] ?? '';

    switch($action) {
        case 'register':
            // Validate input
            if (empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
                throw new Exception('All fields are required');
            }

            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Email already registered');
            }

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, verification_token) VALUES (?, ?, ?, ?)");
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", 
                $data['full_name'],
                $data['email'],
                $hashedPassword,
                $verificationToken
            );

            if ($stmt->execute()) {
                // Send verification email
                $verifyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/HRIS/verify.php?token=" . $verificationToken;
                $to = $data['email'];
                $subject = "Verify your HRIS account";
                $message = "Welcome to HRIS! Please click this link to verify your account: " . $verifyUrl;
                $headers = "From: noreply@hris.com";

                mail($to, $subject, $message, $headers);

                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! Please check your email for verification.'
                ]);
            } else {
                throw new Exception('Registration failed');
            }
            break;

        case 'login':
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            $stmt = $conn->prepare("SELECT id, password, role, email_verified FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (!$user['email_verified']) {
                    echo json_encode(['success' => false, 'message' => 'Please verify your email first']);
                    exit;
                }
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['loggedin'] = true;
                    
                    $token = bin2hex(random_bytes(32));
                    
                    echo json_encode([
                        'success' => true,
                        'token' => $token,
                        'role' => $user['role']
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid password']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            break;

        // ...existing code for other actions...
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
