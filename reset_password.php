<?php
session_start();
require_once 'config.php';
require_once 'EmailService.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = trim($_POST['email']);
        
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        
        // Verify email exists
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Email not found');
        }
        
        // Generate new password
        $newPassword = bin2hex(random_bytes(8)); // 16 characters
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($stmt->execute([$hashedPassword, $email])) {
            // Send password reset email
            $emailService = new EmailService();
            if ($emailService->sendPasswordResetEmail($email, $newPassword)) {
                $_SESSION['success'] = 'New password has been sent to your email.';
            } else {
                throw new Exception('Failed to send password reset email');
            }
            
            header('Location: login.php');
            exit();
        } else {
            throw new Exception('Failed to reset password');
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: forgot_password.php');
        exit();
    }
}