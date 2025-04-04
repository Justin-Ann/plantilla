<?php
function setCorsHeaders() {
    // Set strict CORS headers
    header("Access-Control-Allow-Origin: http://localhost");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    
    // Set proper content type without charset
    header("Content-Type: application/json");
    
    // Use Cache-Control instead of Expires
    header("Cache-Control: public, max-age=31536000");
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}
?>
