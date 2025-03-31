<?php
require_once "../config.php";
require_once "../auth/auth.php";

class AdminHandler {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getUsers() {
        $sql = "SELECT id, username, email, role, last_login, active 
                FROM users WHERE role != 'admin'";
        // ... implementation
    }
    
    public function resetUserPassword($userId, $newPassword) {
        // ... implementation
    }
}
?>
