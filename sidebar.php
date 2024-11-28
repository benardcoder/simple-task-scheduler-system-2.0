<?php
// Get the current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-tasks"></i>
        <span>Task Manager</span>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="task_scheduler.php" class="nav-item <?php echo $current_page == 'task_scheduler.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-plus"></i>
            <span>Task Scheduler</span>
        </a>
        
        <a href="my_tasks.php" class="nav-item <?php echo $current_page == 'my_tasks.php' ? 'active' : ''; ?>">
            <i class="fas fa-list-check"></i>
            <span>My Tasks</span>
        </a>

        <a href="daily_rewards.php" class="nav-item <?php echo $current_page == 'daily_rewards.php' ? 'active' : ''; ?>">
            <i class="fas fa-gift"></i>
            <span>Daily Rewards</span>
        </a>

        <a href="shop.php" class="nav-item <?php echo $current_page == 'shop.php' ? 'active' : ''; ?>">
            <i class="fas fa-store"></i>
            <span>Shop</span>
        </a>
        <!-- Add this where your other navigation links are -->


        <div class="nav-divider"></div>

        <a href="profile.php" class="nav-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        
        <a href="settings.php" class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>

        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <?php if (isset($_SESSION['username'])): ?>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    background: #2c3e50;
    color: #ecf0f1;
    position: fixed;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    z-index: 1000;
}

.sidebar-header {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #34495e;
    font-size: 1.2em;
    font-weight: bold;
}

.sidebar-header i {
    font-size: 1.5em;
    color: #3498db;
}

.sidebar-nav {
    padding: 20px 0;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    background: #34495e;
    color: #ecf0f1;
    border-left-color: #3498db;
}

.nav-item.active {
    background: #34495e;
    color: #3498db;
    border-left-color: #3498db;
}

.nav-item i {
    width: 20px;
    text-align: center;
}

.nav-divider {
    height: 1px;
    background: #34495e;
    margin: 10px 0;
}

.sidebar-footer {
    padding: 15px 20px;
    background: #34495e;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #bdc3c7;
    font-size: 0.9em;
}

.user-info i {
    font-size: 1.2em;
}

/* Main content adjustment */
.main-content {
    margin-left: 250px;
    padding: 20px;
    min-height: 100vh;
    background: #f8f9fa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 60px;
    }

    .sidebar span {
        display: none;
    }

    .sidebar-header {
        justify-content: center;
        padding: 15px;
    }

    .sidebar-header span {
        display: none;
    }

    .nav-item {
        justify-content: center;
        padding: 15px;
    }

    .user-info span {
        display: none;
    }

    .main-content {
        margin-left: 60px;
    }
}

/* Hover effect for mobile */
@media (max-width: 768px) {
    .sidebar:hover {
        width: 250px;
    }

    .sidebar:hover span {
        display: inline;
    }

    .sidebar:hover .sidebar-header {
        justify-content: flex-start;
    }

    .sidebar:hover .nav-item {
        justify-content: flex-start;
    }
}

/* Dark theme support */
@media (prefers-color-scheme: dark) {
    .sidebar {
        background: #1a1a1a;
    }

    .sidebar-header,
    .sidebar-footer {
        background: #2d2d2d;
    }

    .nav-item:hover,
    .nav-item.active {
        background: #2d2d2d;
    }
}
</style>