<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Manager</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
     <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-circle fa-3x"></i>
                <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="task_scheduler.php">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Task Scheduler</span>
                        </a>
                    </li>
                    <li>
                        <a href="daily_rewards.php">
                            <i class="fas fa-gift"></i>
                            <span>Daily Rewards</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_tasks.php">
                            <i class="fas fa-tasks"></i>
                            <span>My Tasks</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="shop.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Shop</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="welcome-section">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p>Select an option from the menu to get started</p>
            </div>
        </div>
    </div>

    <script src="dashboard.js"></script>
</body>
</html>