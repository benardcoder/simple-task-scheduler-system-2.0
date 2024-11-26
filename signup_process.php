<?php
session_start();
require_once 'config.php';
require_once 'EmailService.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$username, $email, $hashedPassword])) {
            // Send welcome email
            $emailService = new EmailService();
            if ($emailService->sendWelcomeEmail($email, $username)) {
                $_SESSION['success'] = 'Registration successful! Welcome email sent.';
            } else {
                $_SESSION['warning'] = 'Registration successful but welcome email could not be sent.';
            }
            
            header('Location: login.php');
            exit();
        } else {
            throw new Exception('Registration failed');
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: signup.php');
        exit();
    }
}