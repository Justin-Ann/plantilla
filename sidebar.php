<!-- sidebar.php -->
<?php
include 'auth/auth.php';
?>

<div class="sidebar">
    <div class="logo">
        <img src="img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
    </div>
    <ul class="nav-menu">
        <li><a href="dashboard.php" data-page="dashboard">DASHBOARD</a></li>
        <li><a href="data-management.php" data-page="data-management">DATA MANAGEMENT</a></li>
        <li><a href="applicant-records.php" data-page="applicants">APPLICANTS RECORDS</a></li>
        <?php if (isAdmin()): ?>
            <li><a href="user-management.php" data-page="users-management">USER MANAGEMENT</a></li>
        <?php endif; ?>
        <li class="logout"><a href="#" onclick="confirmLogout()">LOGOUT</a></li>
    </ul>
</div>

<script>
    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'http://localhost/plantilla/logout.php';
        }
    }
</script>