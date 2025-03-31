<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Plantilla Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
            <div id="dashboard">
                <!-- Dashboard content is loaded via JavaScript -->
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/data-management.js"></script>
    <script src="js/applicant-records.js"></script>
</body>
</html>