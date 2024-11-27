<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $token = $_POST['token'];
        $password = $_POST['password'];
        
        if (empty($password) || strlen($password) < 8) {
            throw new Exception('Invalid password');
        }
        
        // Get user ID from token
        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM password_reset_tokens 
            WHERE token = ? 
            AND expires_at > NOW() 
            AND used = 0
        ");
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        
        if (!$result) {
            throw new Exception('Invalid or expired reset link');
        }
        
        $userId = $result['user_id'];
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ? 
            WHERE id = ?
        ");
        $stmt->execute([$hashedPassword, $userId]);
        
        // Mark token as used
        $stmt = $pdo->prepare("
            UPDATE password_reset_tokens 
            SET used = 1 
            WHERE token = ?
        ");
        $stmt->execute([$token]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Password has been updated successfully. Please login with your new password.';
        header('Location: login.php');
        exit();
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = $e->getMessage();
        header('Location: forgot_password.php');
        exit();
    }
}