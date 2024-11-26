<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user settings
$settings = getUserSettings($pdo, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Task Manager</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        
    <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <div class="settings-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
            </div>

            <?php displayMessage(); ?>

            <div class="settings-container">
                <!-- Appearance Settings -->
                <div class="settings-section">
                    <h2><i class="fas fa-paint-brush"></i> Appearance</h2>
                    <div class="settings-group">
                        <label>Theme</label>
                        <div class="theme-options">
                            <button class="theme-btn <?php echo ($settings['theme'] ?? 'light') == 'light' ? 'active' : ''; ?>" 
                                    data-theme="light">
                                <i class="fas fa-sun"></i> Light
                            </button>
                            <button class="theme-btn <?php echo ($settings['theme'] ?? 'light') == 'dark' ? 'active' : ''; ?>" 
                                    data-theme="dark">
                                <i class="fas fa-moon"></i> Dark
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="settings-section">
                    <h2><i class="fas fa-bell"></i> Notifications</h2>
                    <div class="settings-group">
                        <label class="switch-label">
                            <span>Email Notifications</span>
                            <label class="switch">
                                <input type="checkbox" id="emailNotifications" 
                                       <?php echo ($settings['email_notifications'] ?? false) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </label>
                    </div>
                    <div class="settings-group">
                        <label class="switch-label">
                            <span>Task Reminders</span>
                            <label class="switch">
                                <input type="checkbox" id="taskReminders" 
                                       <?php echo ($settings['task_reminders'] ?? false) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </label>
                    </div>
                </div>

                <!-- Privacy Settings -->
                <div class="settings-section">
                    <h2><i class="fas fa-shield-alt"></i> Privacy</h2>
                    <div class="settings-group">
                        <label class="switch-label">
                            <span>Show Profile to Others</span>
                            <label class="switch">
                                <input type="checkbox" id="profileVisibility" 
                                       <?php echo ($settings['profile_visible'] ?? false) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </label>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="settings-section">
                    <h2><i class="fas fa-user-cog"></i> Account</h2>
                    <div class="settings-group">
                        <button id="changePasswordBtn" class="btn-secondary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                        <button id="deleteAccountBtn" class="btn-danger">
                            <i class="fas fa-trash-alt"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Change Password</h2>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Delete Account</h2>
            <p class="warning-text">
                Are you sure you want to delete your account? This action cannot be undone.
            </p>
            <form id="deleteAccountForm">
                <div class="form-group">
                    <label for="deletePassword">Enter your password to confirm</label>
                    <input type="password" id="deletePassword" name="password" required>
                </div>
                <button type="submit" class="btn-danger">Delete Account</button>
            </form>
        </div>
    </div>

    <script src="settings.js"></script>
</body>
</html>