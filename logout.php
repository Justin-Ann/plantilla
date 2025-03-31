<?php
session_start();

if(isset($_POST['confirm_logout'])) {
    $_SESSION = array();
    session_destroy();
    header("location: login.php");
    exit;
}

// Show confirmation dialog
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout Confirmation</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="logout-confirm">
        <h2>Confirm Logout</h2>
        <p>Are you sure you want to logout?</p>
        <form method="post">
            <button type="submit" name="confirm_logout" class="btn btn-danger">Yes, Logout</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>