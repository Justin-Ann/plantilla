<?php
require_once 'config.php';
header('Content-Type: application/json');

$division = $_GET['division'] ?? null;

if($division) {
    $stmt = $conn->prepare("SELECT ar.* 
                           FROM applicant_records ar
                           JOIN divisions d ON ar.division_id = d.id
                           WHERE d.name = ?");
    $stmt->bind_param("s", $division);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode($records);
}
?>
