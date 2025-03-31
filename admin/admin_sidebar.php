<div class="sidebar">
    <div class="logo">
        <img src="../img/dost-pagasa-logo.png" alt="DOST-PAGASA Logo">
    </div>
    <ul class="nav-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">Users</a></li>
        <li><a href="roles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'roles.php' ? 'active' : ''; ?>">Roles</a></li>
        <li><a href="divisions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'divisions.php' ? 'active' : ''; ?>">Divisions</a></li>
        <li><a href="audit-log.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'audit-log.php' ? 'active' : ''; ?>">Audit Log</a></li>
        <li class="logout"><a href="../logout.php">Logout</a></li>
    </ul>
</div>
