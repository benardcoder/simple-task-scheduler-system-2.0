<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        // Handle password change
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
            setMessage('success', 'Password changed successfully.');
        } else {
            setMessage('error', 'Passwords do not match.');
        }
    } elseif (isset($_POST['delete_account'])) {
        // Handle account deletion
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Task Manager</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Add styles for light and dark themes */
        body.light-theme {
            background-color: #f4f4f9;
            color: #333;
        }
        body.dark-theme {
            background-color: #333;
            color: #f4f4f9;
        }
        .settings-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .settings-section {
            margin-bottom: 20px;
        }
        .settings-section h2 {
            margin-bottom: 10px;
            font-size: 1.2em;
            color: #007bff;
        }
        .settings-section button, .settings-section input[type="submit"] {
            margin-top: 10px;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="settings-container">
                <h1><i class="fas fa-cog"></i> Settings</h1>

                <?php displayMessage(); ?>

                <!-- Theme Settings -->
                <div class="settings-section">
                    <h2>Theme</h2>
                    <button onclick="toggleTheme()" class="btn-primary">Toggle Theme</button>
                </div>

                <!-- Notification Settings -->
                <div class="settings-section">
                    <h2>Notifications</h2>
                    <label>
                        <input type="checkbox" id="notificationsToggle" onchange="toggleNotifications()">
                        Enable Notifications
                    </label>
                </div>

                <!-- Task Reminders -->
                <div class="settings-section">
                    <h2>Task Reminders</h2>
                    <label>
                        <input type="checkbox" id="remindersToggle" onchange="toggleReminders()">
                        Enable Task Reminders
                    </label>
                </div>

                <!-- Account Management -->
                <div class="settings-section">
                    <h2>Account</h2>
                    <form method="POST">
                        <div>
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div>
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <input type="submit" name="change_password" value="Change Password" class="btn-primary">
                    </form>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
                        <input type="submit" name="delete_account" value="Delete Account" class="btn-danger">
                    </form>
                </div>

                <!-- Privacy Policy -->
                <div class="settings-section">
                    <h2>Privacy</h2>
                    <p>Your privacy is important to us. We do not share your data with third parties.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-theme');
            document.body.classList.toggle('light-theme');
        }

        function toggleNotifications() {
            const isEnabled = document.getElementById('notificationsToggle').checked;
            alert('Notifications ' + (isEnabled ? 'enabled' : 'disabled'));
            // Implement actual notification logic here
        }

        function toggleReminders() {
            const isEnabled = document.getElementById('remindersToggle').checked;
            alert('Task reminders ' + (isEnabled ? 'enabled' : 'disabled'));
            // Implement actual reminder logic here
        }
    </script>
</body>
</html>