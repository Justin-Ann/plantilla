<?php
require_once "../config.php";
require_once "../auth/auth.php";

header('Content-Type: application/json');

function getStatusCounts() {
    global $conn;
    $sql = "SELECT status, COUNT(*) as count FROM clean_data GROUP BY status";
    $result = mysqli_query($conn, $sql);
    $counts = [];
    while($row = mysqli_fetch_assoc($result)) {
        $counts[$row['status']] = (int)$row['count'];
    }
    return $counts;
}

// Add more API endpoints...
