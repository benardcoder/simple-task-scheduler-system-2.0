<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        // Validate password
        if (strlen($password) < 4) {
            throw new Exception("Password must be at least 4 characters long");
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        if ($hashed_password === false) {
            throw new Exception("Password hashing failed");
        }

        // Check if username or email already exists
        $check = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        
        if ($check->rowCount() > 0) {
            throw new Exception("Username or email already exists");
        }

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);
        
        $_SESSION['message'] = "Registration successful! Please login.";
        $_SESSION['message_type'] = "success";
        header("Location: index.php");
        exit();
    } catch(Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }
}
?>