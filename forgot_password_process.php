<?php
session_start();
require_once 'config.php';
require_once 'includes/EmailService.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = trim($_POST['email']);
        
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate new password
            $newPassword = generateRandomPassword();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password in database
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateStmt->execute([$hashedPassword, $email]);
            
            // Send email with new password
            $emailService = new EmailService();
            if ($emailService->sendPasswordResetEmail($email, $user['username'], $newPassword)) {
                $_SESSION['message'] = "A new password has been sent to your email.";
                $_SESSION['message_type'] = "success";
            } else {
                throw new Exception("Failed to send password reset email");
            }
            
        } else {
            // Don't reveal if email exists or not for security
            $_SESSION['message'] = "If your email exists in our system, you will receive reset instructions.";
            $_SESSION['message_type'] = "info";
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: forgot_password.php");
    exit();
}

function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}