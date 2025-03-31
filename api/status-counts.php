<?php
require_once 'config.php';
header('Content-Type: application/json');

$query = "SELECT status, COUNT(*) as count FROM status_tracking GROUP BY status";
$result = $conn->query($query);

$counts = [
    'onProcess' => 0,
    'onHold' => 0,
    'notYetFilling' => 0
];

while($row = $result->fetch_assoc()) {
    switch($row['status']) {
        case 'On-Process': $counts['onProcess'] = $row['count']; break;
        case 'On-Hold': $counts['onHold'] = $row['count']; break;
        case 'Not Yet for Filling': $counts['notYetFilling'] = $row['count']; break;
    }
}

echo json_encode($counts);
?>
