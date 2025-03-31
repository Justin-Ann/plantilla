<?php
class LoginHandler {
    private $conn;
    private $max_attempts = 3;
    private $lockout_time = 900; // 15 minutes
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function checkLoginAttempts($username) {
        $sql = "SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt 
                FROM login_attempts 
                WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
                
        if($stmt = mysqli_prepare($this->conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $username, $this->lockout_time);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            
            if($row['attempts'] >= $this->max_attempts) {
                $time_left = $this->lockout_time - (time() - strtotime($row['last_attempt']));
                return ['locked' => true, 'time_left' => $time_left];
            }
        }
        return ['locked' => false, 'time_left' => 0];
    }
    
    public function recordLoginAttempt($username, $success) {
        $sql = "INSERT INTO login_attempts (username, success) VALUES (?, ?)";
        if($stmt = mysqli_prepare($this->conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $username, $success);
            mysqli_stmt_execute($stmt);
        }
        
        if($success) {
            $this->clearLoginAttempts($username);
        }
    }
    
    private function clearLoginAttempts($username) {
        $sql = "DELETE FROM login_attempts WHERE username = ?";
        if($stmt = mysqli_prepare($this->conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
        }
    }
    
    public function isAdmin($userId) {
        $sql = "SELECT role FROM users WHERE id = ? AND role = 'admin'";
        if($stmt = mysqli_prepare($this->conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            return mysqli_stmt_num_rows($stmt) > 0;
        }
        return false;
    }
    
    public function getAdminMenu($userId) {
        if($this->isAdmin($userId)) {
            return [
                'user_management' => true,
                'system_settings' => true,
                'audit_logs' => true
            ];
        }
        return [];
    }
}
?>
