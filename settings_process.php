<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_setting':
            $setting = $_POST['setting'];
            $value = $_POST['value'];
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO user_settings (user_id, setting_name, setting_value) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $setting, $value, $value]);
                
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error updating setting']);
            }
            break;

        case 'change_password':
            $currentPassword = $_POST['currentPassword'];
            $newPassword = $_POST['newPassword'];
            
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if (password_verify($currentPassword, $user['password'])) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error changing password']);
            }
            break;

        case 'delete_account':
            $password = $_POST['password'];
            
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if (password_verify($password, $user['password'])) {
                    $pdo->beginTransaction();
                    
                    // Delete user's data
                    $stmt = $pdo->prepare("DELETE FROM user_settings WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    
                    $stmt = $pdo->prepare("DELETE FROM tasks WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    
                    $pdo->commit();
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Error deleting account']);
            }
            break;
    }
}
?>