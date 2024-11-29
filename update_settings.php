<?php
// Debug: Log the start of the script
error_log("Update settings script started");

// Start output buffering to prevent any unwanted output
ob_start();

session_start();
require_once 'config.php';
require_once 'functions.php';

// Set proper headers
header('Content-Type: application/json');

// Debug: Log the request method and POST data
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle notifications toggle
        if (isset($_POST['notifications_toggle'])) {
            error_log("Handling notifications toggle: " . $_POST['notifications_toggle']);
            
            $notificationsEnabled = ($_POST['notifications_toggle'] === 'true') ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE users SET notifications_enabled = ? WHERE id = ?");
            $success = $stmt->execute([$notificationsEnabled, $_SESSION['user_id']]);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notifications ' . ($notificationsEnabled ? 'enabled' : 'disabled')
                ]);
            } else {
                throw new Exception('Failed to update notifications setting');
            }
        } 
        // Handle reminders toggle
        elseif (isset($_POST['reminders_toggle'])) {
            error_log("Handling reminders toggle: " . $_POST['reminders_toggle']);
            
            $remindersEnabled = ($_POST['reminders_toggle'] === 'true') ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE users SET reminders_enabled = ? WHERE id = ?");
            $success = $stmt->execute([$remindersEnabled, $_SESSION['user_id']]);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Reminders ' . ($remindersEnabled ? 'enabled' : 'disabled')
                ]);
            } else {
                throw new Exception('Failed to update reminders setting');
            }
        }
        // Handle theme change
        elseif (isset($_POST['theme'])) {
            $theme = $_POST['theme'];
            if ($theme === 'light' || $theme === 'dark') {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
                    $success = $stmt->execute([$theme, $_SESSION['user_id']]);
                    
                    if ($success) {
                        $_SESSION['theme'] = $theme;
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update theme']);
                    }
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Database error']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid theme']);
            }
        }
        // Handle password change
        elseif (isset($_POST['current_password']) && isset($_POST['new_password'])) {
            error_log("Handling password change");
            
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $hashedPassword = $stmt->fetchColumn();
            
            if (!password_verify($currentPassword, $hashedPassword)) {
                throw new Exception('Current password is incorrect');
            }
            
            // Update password
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $success = $stmt->execute([$newHashedPassword, $_SESSION['user_id']]);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update password');
            }
        }
        else {
            error_log("Invalid request parameters received");
            throw new Exception('Invalid request parameters');
        }
    } catch (PDOException $e) {
        error_log("Database error in update_settings.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'A database error occurred. Please try again later.'
        ]);
    } catch (Exception $e) {
        error_log("General error in update_settings.php: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}

// Debug: Log the response
$response = ob_get_contents();
error_log("Response being sent: " . $response);

// Send the response
ob_end_flush();
?>