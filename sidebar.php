<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-th-large"></i>
        <span>Navigation</span>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="tasks.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'my_tasks.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tasks"></i>
                    <span>My Tasks</span>
                </a>
            </li>
            <li>
                <a href="task_scheduler.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'task_scheduler.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-calendar-alt"></i>
                    <span>Task Scheduler</span>
                </a>
            </li>
            <li>
                <a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="settings.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
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