<?php
ob_start(); // Start output buffering
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
            
            // Turn off debug mode for production
            $emailService->setDebugMode(false);
            
            if ($emailService->sendPasswordResetEmail($email, $user['username'], $newPassword)) {
                $_SESSION['message'] = "A new password has been sent to your email.";
                $_SESSION['message_type'] = "success";
                
                // Log successful password reset
                error_log("Password reset successful for user: " . $user['username']);
            } else {
                throw new Exception("Failed to send password reset email");
            }
            
        } else {
            // Don't reveal if email exists or not for security
            $_SESSION['message'] = "If your email exists in our system, you will receive reset instructions.";
            $_SESSION['message_type'] = "info";
            
            // Log attempted reset for non-existent email
            error_log("Password reset attempted for non-existent email: " . $email);
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        
        // Log the error
        error_log("Password reset error: " . $e->getMessage());
    }
    
    // Clear any output buffers
    ob_end_clean();
    
    // Redirect to index.php
    header("Location: index.php");
    exit();
}

/**
 * Generates a random password with specified length
 * 
 * @param int $length The length of the password to generate
 * @return string The generated password
 */
function generateRandomPassword($length = 12) {
    // Define character sets
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $special = '!@#$%^&*()';
    
    // Ensure at least one character from each set
    $password = [
        $lowercase[random_int(0, strlen($lowercase) - 1)],
        $uppercase[random_int(0, strlen($uppercase) - 1)],
        $numbers[random_int(0, strlen($numbers) - 1)],
        $special[random_int(0, strlen($special) - 1)]
    ];
    
    // Complete the rest of the password
    $chars = $lowercase . $uppercase . $numbers . $special;
    for ($i = count($password); $i < $length; $i++) {
        $password[] = $chars[random_int(0, strlen($chars) - 1)];
    }
    
    // Shuffle the password array and convert to string
    shuffle($password);
    return implode('', $password);
}

// If accessed directly without POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}
?>