<?php
require_once "verification.php";

$message = "";
if(isset($_GET['token'])) {
    $result = verifyToken($_GET['token']);
    if($result === "success") {
        $message = "Your email has been verified successfully. You can now <a href='../login.php'>login</a>.";
    } else {
        $message = $result;
    }
} else {
    $message = "Invalid verification link.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Plantilla Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
        </div>
        <h2>Email Verification</h2>
        <div class="message">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
