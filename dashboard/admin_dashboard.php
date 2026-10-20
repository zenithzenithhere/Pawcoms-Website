<?php

    // Prevent browser caching
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies

session_start();



include '../config/database.php';
require_once 'dashboard_sidebar.php';

    // Blocked Checker
        if (basename($_SERVER['PHP_SELF']) == 'nicetry.php') {
        
            die("Access Denied: You are not permitted to view this page.");
        }
        if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] === true) {
            
            die("Access Denied: You are not permitted to view this page.");
        }

    // Check if the user is logged
    if (!isset($_SESSION['id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/style.css?v=dashboard-sidebar-1">
</head>
<body class="with-sidebar">

    <!-- Left Sidebar Navigation -->
    <?php renderDashboardSidebar('dashboard'); ?>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
                <span class="badge"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
            </div>
        </header>

        <!-- Dynamic Content Area -->
        <main class="content">
            <div class="welcome-card">
                <h1>Dashboard</h1>
                <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong>.</p>
            </div>

            <div class="grid">
                <div class="card">
                    <h3>User ID</h3>
                    <p>#<?php echo htmlspecialchars($_SESSION['id']); ?></p>
                </div>
                <div class="card">
                    <h3>Account Role</h3>
                    <p><?php echo ucfirst(htmlspecialchars($_SESSION['role'])); ?></p>
                </div>
                <div class="card">
                    <h3>Session Status</h3>
                    <p>Active</p>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
