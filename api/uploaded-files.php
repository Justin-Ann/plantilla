<?php
// Assuming you have a database connection established
require_once "config.php";

// Get the month_year parameter from the URL
$month_year = isset($_GET['month_year']) ? $_GET['month_year'] : null;

if ($month_year) {
    // Split the month_year into year and month
    list($year, $month) = explode('-', $month_year);

    // Query to get files uploaded in the given month and year
    $sql = "SELECT * FROM uploaded_files WHERE YEAR(upload_date) = ? AND MONTH(upload_date) = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $year, $month);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Prepare the response
        $files = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $files[] = $row;
        }

        // Return the response as JSON
        echo json_encode([
            'success' => true,
            'files' => $files
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database query failed.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Month and year are required.'
    ]);
}

mysqli_close($conn);
