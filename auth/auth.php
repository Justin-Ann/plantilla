<?php
require_once '../config.php';
session_start();

class Auth {
    public function register($data) {
        $verificationToken = bin2hex(random_bytes(32));
        $sql = "INSERT INTO users (full_name, email, password, verification_token) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        if ($stmt->execute([$data['full_name'], $data['email'], $hashedPassword, $verificationToken])) {
            $this->sendVerificationEmail($data['email'], $verificationToken);
            return ['success' => true];
        }
        return ['success' => false];
    }

    private function sendVerificationEmail($email, $token) {
        $verifyUrl = "http://localhost/HRIS/auth/verify.php?token=" . $token;
        $subject = "Verify your HRIS account";
        $message = "Please click this link to verify your account: " . $verifyUrl;
        mail($email, $subject, $message);
    }
}

function requireLogin() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if(!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        header("location: dashboard.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}
?>
