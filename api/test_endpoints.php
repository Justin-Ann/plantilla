<?php
require_once "../config.php";

header('Content-Type: application/json');

$endpoints = [
    'upload' => '/api/upload_handler.php',
    'dashboard' => '/api/dashboard_handler.php',
    'applicants' => '/api/applicant_handler.php'
];

$results = [];

foreach ($endpoints as $name => $path) {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/HRIS' . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $results[$name] = [
        'endpoint' => $url,
        'status' => $httpCode,
        'available' => ($httpCode !== 0 && $httpCode !== 404)
    ];
}

echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'endpoints' => $results
]);
?>
