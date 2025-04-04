<?php
require_once '../config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
error_log("Registration request received");

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set proper headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Content-Security-Policy: frame-ancestors \'none\'');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        error_log("Received data: " . $input);
        
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }

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
                            throw new Exception('Invalid credentials');
                        }
                    } else {
                        throw new Exception('Invalid credentials');
                    }
                    break;

                case 'register':
                    // Validate input data
                    if (empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
                        throw new Exception('All fields are required');
                    }

                    // Validate email format
                    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email format');
                    }

                    try {
                        // Check for duplicate email - strengthened check
                        $check_stmt = $conn->prepare("SELECT id, email_verified FROM users WHERE email = ?");
                        if (!$check_stmt) {
                            throw new Exception('Database error: ' . $conn->error);
                        }
                        
                        $check_stmt->bind_param("s", $data['email']);
                        if (!$check_stmt->execute()) {
                            throw new Exception('Error checking email: ' . $check_stmt->error);
                        }
                        
                        $result = $check_stmt->get_result();
                        if ($result->num_rows > 0) {
                            $user = $result->fetch_assoc();
                            if ($user['email_verified']) {
                                throw new Exception('This email is already registered. Please use a different email address.');
                            } else {
                                throw new Exception('This email is already registered but not verified. Please check your email for verification link or contact support.');
                            }
                        }

                        // Create new user
                        $token = bin2hex(random_bytes(32));
                        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

                        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, verification_token, verification_expires) VALUES (?, ?, ?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception('Prepare insert statement failed: ' . $conn->error);
                        }

                        $stmt->bind_param("sssss", 
                            $data['full_name'],
                            $data['email'],
                            $hashed_password,
                            $token,
                            $expiry
                        );

                        if (!$stmt->execute()) {
                            throw new Exception('Insert failed: ' . $stmt->error);
                        }

                        // Send verification email
                        require_once '../auth/verification.php';
                        if (sendVerificationEmail($data['email'], $token)) {
                            echo json_encode([
                                'success' => true, 
                                'message' => 'Registration successful! Please check your email to verify your account.'
                            ]);
                        } else {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Account created but verification email could not be sent. Please contact support.'
                            ]);
                        }

                    } catch (Exception $e) {
                        error_log("Registration error: " . $e->getMessage());
                        echo json_encode([
                            'success' => false,
                            'message' => $e->getMessage(),
                            'details' => 'An error occurred during registration'
                        ]);
                    }
                    break;

                case 'verify_email':
                    $token = $data['token'];
                    $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE verification_token = ?");
                    $stmt->bind_param("s", $token);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
                    } else {
                        throw new Exception('Email verification failed');
                    }
                    break;

                default:
                    throw new Exception('Invalid action');
            }
        } else {
            throw new Exception('Action not specified');
        }
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'details' => 'An error occurred during processing'
    ]);
}
?>
