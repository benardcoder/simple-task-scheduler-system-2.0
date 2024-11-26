<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // In a real application, you would:
            // 1. Generate a reset token
            // 2. Store it in the database with an expiration
            // 3. Send an email with a reset link
            
            $_SESSION['message'] = "If an account exists with this email, password reset instructions will be sent.";
            $_SESSION['message_type'] = "success";
        } else {
            // For security, show the same message even if email doesn't exist
            $_SESSION['message'] = "If an account exists with this email, password reset instructions will be sent.";
            $_SESSION['message_type'] = "success";
        }
        
        header("Location: index.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }
}
?>