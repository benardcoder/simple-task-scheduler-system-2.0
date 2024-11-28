<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Debug: Check if update_settings.php exists
$updateSettingsPath = __DIR__ . '/update_settings.php';
if (!file_exists($updateSettingsPath)) {
    die("Error: update_settings.php not found in " . __DIR__);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user preferences with error handling
try {
    $stmt = $pdo->prepare("SELECT notifications_enabled, reminders_enabled, email, theme FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userPreferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userPreferences) {
        $userPreferences = [
            'notifications_enabled' => false,
            'reminders_enabled' => false,
            'theme' => 'light',
            'email' => ''
        ];
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['message'] = "Error loading settings";
    $_SESSION['message_type'] = "error";
    $userPreferences = [
        'notifications_enabled' => false,
        'reminders_enabled' => false,
        'theme' => 'light',
        'email' => ''
    ];
}

// Get the base URL for AJAX requests
$baseUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
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
<body class="theme-<?php echo htmlspecialchars($userPreferences['theme']); ?>">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="settings-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
            </div>

            <div id="message-container"></div>
            <?php displayMessage(); ?>

            <div class="settings-container">
                <!-- Notification Settings -->
                <div class="settings-section">
                    <h2>Notifications</h2>
                    <label class="toggle-switch">
                        <input type="checkbox" id="notificationsToggle" 
                               <?php echo $userPreferences['notifications_enabled'] ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                        Enable Email Notifications
                    </label>
                </div>

                <!-- Task Reminders -->
                <div class="settings-section">
                    <h2>Task Reminders</h2>
                    <label class="toggle-switch">
                        <input type="checkbox" id="remindersToggle" 
                               <?php echo $userPreferences['reminders_enabled'] ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                        Enable Task Reminders
                    </label>
                </div>

                <!-- Theme Settings -->
                <div class="settings-section">
                    <h2>Theme Settings</h2>
                    <div class="theme-options">
                        <label class="theme-option">
                            <input type="radio" name="theme" value="light" 
                                   <?php echo ($userPreferences['theme'] === 'light') ? 'checked' : ''; ?>>
                            <span class="theme-preview light">
                                <i class="fas fa-sun"></i>
                                Light
                            </span>
                        </label>
                        <label class="theme-option">
                            <input type="radio" name="theme" value="dark" 
                                   <?php echo ($userPreferences['theme'] === 'dark') ? 'checked' : ''; ?>>
                            <span class="theme-preview dark">
                                <i class="fas fa-moon"></i>
                                Dark
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="settings-section">
                    <h2>Change Password</h2>
                    <form method="POST" class="settings-form" id="passwordForm">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Update Password
                        </button>
                    </form>
                </div>

                <!-- Delete Account -->
                <div class="settings-section danger-zone">
                    <h2>Delete Account</h2>
                    <p class="warning-text">
                        <i class="fas fa-exclamation-triangle"></i>
                        This action cannot be undone. All your data will be permanently deleted.
                    </p>
                    <form method="POST" class="settings-form" id="deleteForm">
                        <div class="form-group">
                            <label>Enter Password to Confirm</label>
                            <input type="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-danger">
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store the base URL for AJAX requests
        const BASE_URL = '<?php echo $baseUrl; ?>';
        console.log('Base URL:', BASE_URL); // Debug log

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = '<?php echo $userPreferences['theme']; ?>';
            applyTheme(savedTheme);

            // Add event listeners
            document.querySelectorAll('input[name="theme"]').forEach(input => {
                input.addEventListener('change', function() {
                    changeTheme(this.value);
                });
            });

            document.getElementById('notificationsToggle').addEventListener('change', toggleNotifications);
            document.getElementById('remindersToggle').addEventListener('change', toggleReminders);
        });

        function showMessage(message, type = 'error') {
            const container = document.getElementById('message-container');
            container.innerHTML = `<div class="message ${type}">${message}</div>`;
            setTimeout(() => container.innerHTML = '', 5000);
        }

        function applyTheme(theme) {
            document.body.className = `theme-${theme}`;
        }

        async function changeTheme(theme) {
            try {
                console.log('Changing theme to:', theme); // Debug log
                const formData = new FormData();
                formData.append('theme', theme);

                const response = await fetch(`${BASE_URL}/update_settings.php`, {
                    method: 'POST',
                    body: formData
                });

                console.log('Response status:', response.status); // Debug log

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Response data:', data); // Debug log

                if (data.success) {
                    applyTheme(theme);
                    showMessage('Theme updated successfully', 'success');
                } else {
                    throw new Error(data.message || 'Error updating theme');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage(error.message);
            }
        }

        async function toggleNotifications() {
            try {
                const isEnabled = document.getElementById('notificationsToggle').checked;
                console.log('Toggling notifications:', isEnabled); // Debug log

                const formData = new FormData();
                formData.append('notifications_toggle', isEnabled ? 'true' : 'false');

                const response = await fetch(`${BASE_URL}/update_settings.php`, {
                    method: 'POST',
                    body: formData
                });

                console.log('Response status:', response.status); // Debug log

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Response data:', data); // Debug log

                if (data.success) {
                    showMessage('Notification settings updated', 'success');
                } else {
                    throw new Error(data.message || 'Error updating notifications');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('notificationsToggle').checked = !document.getElementById('notificationsToggle').checked;
                showMessage(error.message);
            }
        }

        async function toggleReminders() {
            try {
                const isEnabled = document.getElementById('remindersToggle').checked;
                console.log('Toggling reminders:', isEnabled); // Debug log

                const formData = new FormData();
                formData.append('reminders_toggle', isEnabled ? 'true' : 'false');

                const response = await fetch(`${BASE_URL}/update_settings.php`, {
                    method: 'POST',
                    body: formData
                });

                console.log('Response status:', response.status); // Debug log

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Response data:', data); // Debug log

                if (data.success) {
                    showMessage('Reminder settings updated', 'success');
                } else {
                    throw new Error(data.message || 'Error updating reminders');
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('remindersToggle').checked = !document.getElementById('remindersToggle').checked;
                showMessage(error.message);
            }
        }
    </script>

    <style>
        /* Your existing CSS styles here */
    </style>
</body>
</html>